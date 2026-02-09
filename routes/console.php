<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cleanup of orphaned photos every hour
Schedule::command('app:cleanup-orphaned-photos')
    ->hourly()
    ->description('Clean up orphaned photos that are not associated with any submitted form')
    ->onSuccess(function () {
        Log::info('Orphaned photos cleanup completed successfully');
    })
    ->onFailure(function () {
        Log::error('Orphaned photos cleanup failed');
    });
