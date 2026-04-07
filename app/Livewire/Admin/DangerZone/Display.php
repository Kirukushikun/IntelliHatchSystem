<?php

namespace App\Livewire\Admin\DangerZone;

use App\Models\Form;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Display extends Component
{
    public string $wipeMode = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $selectedYear = '';
    public string $confirmationText = '';
    public bool $showConfirmModal = false;
    public int $affectedCount = 0;
    public bool $isProcessing = false;

    public function updatedWipeMode(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'selectedYear', 'confirmationText']);
        $this->affectedCount = 0;
    }

    public function getAvailableYears(): array
    {
        return Form::query()
            ->selectRaw('YEAR(date_submitted) as year')
            ->whereNotNull('date_submitted')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();
    }

    public function previewCount(): void
    {
        $this->affectedCount = $this->buildQuery()->count();
    }

    public function openConfirmModal(): void
    {
        $this->affectedCount = $this->buildQuery()->count();

        if ($this->affectedCount === 0) {
            $this->dispatch('showToast', message: 'No form submissions match the selected criteria.', type: 'error');
            return;
        }

        $this->confirmationText = '';
        $this->showConfirmModal = true;
    }

    public function confirmWipe(): void
    {
        if ($this->confirmationText !== (string) $this->affectedCount) {
            return;
        }

        $this->isProcessing = true;

        try {
            $query = $this->buildQuery();
            $totalDeleted = 0;
            $photosDeleted = 0;

            $query->chunk(100, function ($forms) use (&$totalDeleted, &$photosDeleted) {
                foreach ($forms as $form) {
                    $photosDeleted += $this->deleteFormPhotos($form);
                    $form->delete();
                    $totalDeleted++;
                }
            });

            $description = $this->buildLogDescription($totalDeleted, $photosDeleted);
            ActivityLogger::log('bulk_deleted_forms', $description, 'Form', null, [
                'mode' => $this->wipeMode,
                'forms_deleted' => $totalDeleted,
                'photos_deleted' => $photosDeleted,
                'date_from' => $this->dateFrom ?: null,
                'date_to' => $this->dateTo ?: null,
                'year' => $this->selectedYear ?: null,
            ]);

            $this->showConfirmModal = false;
            $this->reset(['confirmationText', 'dateFrom', 'dateTo', 'selectedYear']);
            $this->affectedCount = 0;

            $this->dispatch('showToast', message: "Successfully deleted {$totalDeleted} form submissions and {$photosDeleted} associated photos.", type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('showToast', message: 'An error occurred while deleting forms: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function closeModal(): void
    {
        $this->showConfirmModal = false;
        $this->confirmationText = '';
    }

    public function render()
    {
        return view('livewire.admin.danger-zone.display', [
            'availableYears' => $this->getAvailableYears(),
        ]);
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
