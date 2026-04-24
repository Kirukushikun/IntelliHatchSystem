<?php

namespace App\Livewire\Admin\DangerZone;

use App\Jobs\PurgeActivityLogs;
use App\Jobs\PurgeFormPhotos;
use App\Jobs\WipeFormSubmissions;
use App\Models\ActivityLog;
use App\Models\Form;
use Livewire\Component;

class Display extends Component
{
    // Form wipe properties
    public string $wipeMode = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $selectedYear = '';
    public string $confirmationText = '';
    public bool $showConfirmModal = false;
    public int $affectedCount = 0;

    // Photo purge properties
    public string $photoPurgeMode = 'quarter';
    public string $photoDateFrom = '';
    public string $photoDateTo = '';
    public string $photoSelectedYear = '';
    public string $photoSelectedQuarter = '';
    public string $photoConfirmationText = '';
    public bool $showPhotoConfirmModal = false;
    public int $photoAffectedCount = 0;

    // Activity log purge properties
    public string $logWipeMode = 'all';
    public string $logDateFrom = '';
    public string $logDateTo = '';
    public string $logSelectedYear = '';
    public string $logConfirmationText = '';
    public bool $showLogConfirmModal = false;
    public int $logAffectedCount = 0;

    public function updatedWipeMode(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'selectedYear', 'confirmationText']);
        $this->affectedCount = 0;
    }

    public function updatedPhotoPurgeMode(): void
    {
        $this->reset(['photoDateFrom', 'photoDateTo', 'photoSelectedYear', 'photoSelectedQuarter', 'photoConfirmationText']);
        $this->photoAffectedCount = 0;
    }

    public function updatedLogWipeMode(): void
    {
        $this->reset(['logDateFrom', 'logDateTo', 'logSelectedYear', 'logConfirmationText']);
        $this->logAffectedCount = 0;
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

    public function getLogAvailableYears(): array
    {
        return ActivityLog::query()
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();
    }

    public function getPhotoAvailableYears(): array
    {
        return Form::query()
            ->whereNull('photos_purged_at')
            ->selectRaw('YEAR(date_submitted) as year')
            ->whereNotNull('date_submitted')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();
    }

    public function getAvailableQuarters(): array
    {
        return ['Q1', 'Q2', 'Q3', 'Q4'];
    }

    public function previewPhotoCount(): void
    {
        $this->photoAffectedCount = $this->buildPhotoQuery()->count();
    }

    public function openPhotoConfirmModal(): void
    {
        $this->photoAffectedCount = $this->buildPhotoQuery()->count();

        if ($this->photoAffectedCount === 0) {
            $this->dispatch('showToast', message: 'No form submissions with photos match the selected criteria.', type: 'error');
            return;
        }

        $this->photoConfirmationText = '';
        $this->showPhotoConfirmModal = true;
    }

    public function confirmPhotoPurge(): void
    {
        if ($this->photoConfirmationText !== (string) $this->photoAffectedCount) {
            return;
        }

        PurgeFormPhotos::dispatch(
            purgeMode: $this->photoPurgeMode,
            dateFrom: $this->photoDateFrom ?: null,
            dateTo: $this->photoDateTo ?: null,
            selectedYear: $this->photoSelectedYear ?: null,
            selectedQuarter: $this->photoSelectedQuarter ?: null,
            userId: auth()->id(),
        );

        $count = $this->photoAffectedCount;

        $this->showPhotoConfirmModal = false;
        $this->reset(['photoConfirmationText', 'photoDateFrom', 'photoDateTo', 'photoSelectedYear', 'photoSelectedQuarter']);
        $this->photoAffectedCount = 0;

        $this->dispatch('showToast', message: "Purge of photos from {$count} form submissions has been queued and will be processed in the background.", type: 'success');
    }

    public function closePhotoModal(): void
    {
        $this->showPhotoConfirmModal = false;
        $this->photoConfirmationText = '';
    }

    public function previewCount(): void
    {
        $this->affectedCount = $this->buildQuery()->count();
    }

    public function previewLogCount(): void
    {
        $this->logAffectedCount = $this->buildLogQuery()->count();
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

    public function openLogConfirmModal(): void
    {
        $this->logAffectedCount = $this->buildLogQuery()->count();

        if ($this->logAffectedCount === 0) {
            $this->dispatch('showToast', message: 'No activity logs match the selected criteria.', type: 'error');
            return;
        }

        $this->logConfirmationText = '';
        $this->showLogConfirmModal = true;
    }

    public function confirmWipe(): void
    {
        if ($this->confirmationText !== (string) $this->affectedCount) {
            return;
        }

        WipeFormSubmissions::dispatch(
            wipeMode: $this->wipeMode,
            dateFrom: $this->dateFrom ?: null,
            dateTo: $this->dateTo ?: null,
            selectedYear: $this->selectedYear ?: null,
            userId: auth()->id(),
        );

        $count = $this->affectedCount;

        $this->showConfirmModal = false;
        $this->reset(['confirmationText', 'dateFrom', 'dateTo', 'selectedYear']);
        $this->affectedCount = 0;

        $this->dispatch('showToast', message: "Wipe of {$count} form submissions has been queued and will be processed in the background.", type: 'success');
    }

    public function confirmLogWipe(): void
    {
        if ($this->logConfirmationText !== (string) $this->logAffectedCount) {
            return;
        }

        PurgeActivityLogs::dispatch(
            logWipeMode: $this->logWipeMode,
            dateFrom: $this->logDateFrom ?: null,
            dateTo: $this->logDateTo ?: null,
            selectedYear: $this->logSelectedYear ?: null,
            expectedCount: $this->logAffectedCount,
            userId: auth()->id(),
        );

        $count = $this->logAffectedCount;

        $this->showLogConfirmModal = false;
        $this->reset(['logConfirmationText', 'logDateFrom', 'logDateTo', 'logSelectedYear']);
        $this->logAffectedCount = 0;

        $this->dispatch('showToast', message: "Purge of {$count} activity log entries has been queued and will be processed in the background.", type: 'success');
    }

    public function closeModal(): void
    {
        $this->showConfirmModal = false;
        $this->confirmationText = '';
    }

    public function closeLogModal(): void
    {
        $this->showLogConfirmModal = false;
        $this->logConfirmationText = '';
    }

    public function render()
    {
        return view('livewire.admin.danger-zone.display', [
            'availableYears' => $this->getAvailableYears(),
            'logAvailableYears' => $this->getLogAvailableYears(),
            'photoAvailableYears' => $this->getPhotoAvailableYears(),
            'availableQuarters' => $this->getAvailableQuarters(),
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

    private function buildPhotoQuery()
    {
        $query = Form::query()->whereNull('photos_purged_at');

        return match ($this->photoPurgeMode) {
            'quarter' => $this->applyQuarterFilter($query),
            'custom' => $query
                ->when($this->photoDateFrom, fn ($q) => $q->whereDate('date_submitted', '>=', $this->photoDateFrom))
                ->when($this->photoDateTo, fn ($q) => $q->whereDate('date_submitted', '<=', $this->photoDateTo)),
            default => $query,
        };
    }

    private function applyQuarterFilter($query)
    {
        if (! $this->photoSelectedYear || ! $this->photoSelectedQuarter) {
            return $query;
        }

        $quarterRanges = [
            'Q1' => ['01-01', '03-31'],
            'Q2' => ['04-01', '06-30'],
            'Q3' => ['07-01', '09-30'],
            'Q4' => ['10-01', '12-31'],
        ];

        $range = $quarterRanges[$this->photoSelectedQuarter] ?? null;

        if (! $range) {
            return $query;
        }

        return $query->whereDate('date_submitted', '>=', "{$this->photoSelectedYear}-{$range[0]}")
            ->whereDate('date_submitted', '<=', "{$this->photoSelectedYear}-{$range[1]}");
    }

    private function buildLogQuery()
    {
        $query = ActivityLog::query();

        return match ($this->logWipeMode) {
            'date_range' => $query
                ->when($this->logDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->logDateFrom))
                ->when($this->logDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->logDateTo)),
            'year' => $query->whereYear('created_at', $this->logSelectedYear),
            default => $query,
        };
    }
}
