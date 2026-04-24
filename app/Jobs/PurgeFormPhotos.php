<?php

namespace App\Jobs;

use App\Models\Form;
use App\Services\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeFormPhotos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public readonly string $purgeMode,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly ?string $selectedYear,
        public readonly ?string $selectedQuarter,
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        try {
            $query = $this->buildQuery();
            $formsProcessed = 0;
            $photosDeleted = 0;

            $query->chunkById(100, function ($forms) use (&$formsProcessed, &$photosDeleted) {
                foreach ($forms as $form) {
                    $deleted = $this->purgePhotosFromForm($form);

                    if ($deleted > 0) {
                        $photosDeleted += $deleted;
                        $formsProcessed++;
                    }
                }
            });

            $description = $this->buildLogDescription($formsProcessed, $photosDeleted);

            ActivityLogger::logForUser($this->userId, 'bulk_purge_photos', $description, 'Form', null, [
                'mode' => $this->purgeMode,
                'forms_processed' => $formsProcessed,
                'photos_deleted' => $photosDeleted,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'year' => $this->selectedYear,
                'quarter' => $this->selectedQuarter,
            ]);

            Log::info("PurgeFormPhotos completed: {$formsProcessed} forms processed, {$photosDeleted} photos deleted.");
        } catch (\Throwable $e) {
            Log::error("PurgeFormPhotos failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function buildQuery()
    {
        $query = Form::query()->whereNull('photos_purged_at');

        return match ($this->purgeMode) {
            'quarter' => $this->applyQuarterFilter($query),
            'custom' => $query
                ->when($this->dateFrom, fn ($q) => $q->whereDate('date_submitted', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('date_submitted', '<=', $this->dateTo)),
            default => $query,
        };
    }

    private function applyQuarterFilter($query)
    {
        if (! $this->selectedYear || ! $this->selectedQuarter) {
            return $query;
        }

        $quarterRanges = [
            'Q1' => ['01-01', '03-31'],
            'Q2' => ['04-01', '06-30'],
            'Q3' => ['07-01', '09-30'],
            'Q4' => ['10-01', '12-31'],
        ];

        $range = $quarterRanges[$this->selectedQuarter] ?? null;

        if (! $range) {
            return $query;
        }

        return $query->whereDate('date_submitted', '>=', "{$this->selectedYear}-{$range[0]}")
            ->whereDate('date_submitted', '<=', "{$this->selectedYear}-{$range[1]}");
    }

    private function purgePhotosFromForm(Form $form): int
    {
        $formInputs = $form->form_inputs ?? [];
        $photoUrls = $this->extractPhotoUrls($formInputs);

        if (empty($photoUrls)) {
            return 0;
        }

        $count = 0;

        foreach ($photoUrls as $url) {
            $relativePath = $this->stripStoragePrefix($url);

            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            DB::table('photos')->where('public_path', $url)->delete();
            $count++;
        }

        // Remove photo URLs from form_inputs JSON
        $cleanedInputs = $this->removePhotoKeysFromInputs($formInputs);

        $form->update([
            'form_inputs' => $cleanedInputs,
            'photos_purged_at' => now(),
        ]);

        return $count;
    }

    private function removePhotoKeysFromInputs(array $inputs): array
    {
        $cleaned = [];

        foreach ($inputs as $key => $value) {
            // Skip keys that end with _photos and contain photo URLs
            if (str_ends_with($key, '_photos') && is_array($value)) {
                $hasPhotos = false;
                foreach ($value as $item) {
                    if (is_string($item) && $this->looksLikePhotoUrl($item)) {
                        $hasPhotos = true;
                        break;
                    }
                }
                if ($hasPhotos) {
                    $cleaned[$key] = [];
                    continue;
                }
            }

            // For nested arrays, check for photo URLs
            if (is_array($value)) {
                $cleaned[$key] = $this->removePhotoUrlsFromNested($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    private function removePhotoUrlsFromNested(array $arr): array
    {
        $result = [];
        foreach ($arr as $key => $value) {
            if (is_string($value) && $this->looksLikePhotoUrl($value)) {
                continue;
            }
            if (is_array($value)) {
                $result[$key] = $this->removePhotoUrlsFromNested($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function extractPhotoUrls(array $inputs): array
    {
        $urls = [];

        foreach ($inputs as $value) {
            if (is_array($value)) {
                foreach ($value as $nested) {
                    if (is_array($nested)) {
                        foreach ($nested as $item) {
                            if (is_string($item) && $this->looksLikePhotoUrl($item)) {
                                $urls[] = $item;
                            }
                        }
                    } elseif (is_string($nested) && $this->looksLikePhotoUrl($nested)) {
                        $urls[] = $nested;
                    }
                }
            } elseif (is_string($value) && $this->looksLikePhotoUrl($value)) {
                $urls[] = $value;
            }
        }

        return array_unique($urls);
    }

    private function looksLikePhotoUrl(string $value): bool
    {
        return str_contains($value, '/storage/forms/');
    }

    private function stripStoragePrefix(string $publicPath): string
    {
        if (str_starts_with($publicPath, asset('storage/'))) {
            return substr($publicPath, strlen(asset('storage/')));
        }

        $patterns = [
            'https://intellihatch.bfcgroup.ph/storage/',
            'http://intellihatch.bfcgroup.ph/storage/',
            'storage/',
        ];

        foreach ($patterns as $pattern) {
            if (str_starts_with($publicPath, $pattern)) {
                return substr($publicPath, strlen($pattern));
            }
        }

        return $publicPath;
    }

    private function buildLogDescription(int $formsProcessed, int $photosDeleted): string
    {
        $modeDesc = match ($this->purgeMode) {
            'quarter' => "for {$this->selectedQuarter} {$this->selectedYear}",
            'custom' => "from {$this->dateFrom} to {$this->dateTo}",
            default => '(all forms)',
        };

        return "Purged photos from {$formsProcessed} form submissions {$modeDesc}. {$photosDeleted} photos removed from storage.";
    }
}
