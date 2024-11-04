<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:cleanup-archived-tasks')->dailyAt('00:00')
    ->withoutOverlapping(10)
    ->before(function () {
        Log::channel('scheduler')->info('Starting archived tasks cleanup schedule');
    })
    ->after(function () {
        Log::channel('scheduler')->info('Completed archived tasks cleanup schedule');
    })
    ->onSuccess(function () {
        Log::channel('scheduler')->info('Archived tasks cleanup completed successfully');
    })
    ->onFailure(function () {
        Log::channel('scheduler')->error('Archived tasks cleanup failed');
    })
    ->appendOutputTo(storage_path('logs/archived-tasks-cleanup.log'))
    ->runInBackground();
