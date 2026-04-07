<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PurgeActivityLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        public readonly string $logWipeMode,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly ?string $selectedYear,
        public readonly int $expectedCount,
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        try {
            $deleted = $this->buildQuery()->delete();

            $modeDesc = match ($this->logWipeMode) {
                'date_range' => "by date range ({$this->dateFrom} to {$this->dateTo})",
                'year' => "for year {$this->selectedYear}",
                default => '(all logs)',
            };

            ActivityLogger::logForUser($this->userId, 'bulk_deleted_activity_logs', "Purged {$deleted} activity logs {$modeDesc}.", 'ActivityLog', null, [
                'mode' => $this->logWipeMode,
                'logs_deleted' => $deleted,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'year' => $this->selectedYear,
            ]);

            Log::info("PurgeActivityLogs completed: {$deleted} logs deleted.");
        } catch (\Throwable $e) {
            Log::error("PurgeActivityLogs failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function buildQuery()
    {
        $query = ActivityLog::query();

        return match ($this->logWipeMode) {
            'date_range' => $query
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo)),
            'year' => $query->whereYear('created_at', $this->selectedYear),
            default => $query,
        };
    }
}
