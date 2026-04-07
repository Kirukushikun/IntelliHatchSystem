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

class WipeFormSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public readonly string $wipeMode,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly ?string $selectedYear,
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        try {
            $query = $this->buildQuery();
            $totalDeleted = 0;
            $photosDeleted = 0;

            $query->chunkById(100, function ($forms) use (&$totalDeleted, &$photosDeleted) {
                foreach ($forms as $form) {
                    $photosDeleted += $this->deleteFormPhotos($form);
                    $form->delete();
                    $totalDeleted++;
                }
            });

            $description = $this->buildLogDescription($totalDeleted, $photosDeleted);

            ActivityLogger::logForUser($this->userId, 'bulk_deleted_forms', $description, 'Form', null, [
                'mode' => $this->wipeMode,
                'forms_deleted' => $totalDeleted,
                'photos_deleted' => $photosDeleted,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'year' => $this->selectedYear,
            ]);

            Log::info("WipeFormSubmissions completed: {$totalDeleted} forms, {$photosDeleted} photos deleted.");
        } catch (\Throwable $e) {
            Log::error("WipeFormSubmissions failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function buildQuery()
    {
        $query = Form::query();

        return match ($this->wipeMode) {
            'date_range' => $query
                ->when($this->dateFrom, fn ($q) => $q->whereDate('date_submitted', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('date_submitted', '<=', $this->dateTo)),
            'year' => $query->whereYear('date_submitted', $this->selectedYear),
            default => $query,
        };
    }

    private function deleteFormPhotos(Form $form): int
    {
        $photoUrls = $this->extractPhotoUrls($form->form_inputs ?? []);
        $count = 0;

        foreach ($photoUrls as $url) {
            $relativePath = $this->stripStoragePrefix($url);

            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            DB::table('photos')->where('public_path', $url)->delete();
            $count++;
        }

        return $count;
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

    private function buildLogDescription(int $totalDeleted, int $photosDeleted): string
    {
        $modeDesc = match ($this->wipeMode) {
            'date_range' => "by date range ({$this->dateFrom} to {$this->dateTo})",
            'year' => "for year {$this->selectedYear}",
            default => '(all submissions)',
        };

        return "Bulk deleted {$totalDeleted} form submissions {$modeDesc}. {$photosDeleted} associated photos removed.";
    }
}
