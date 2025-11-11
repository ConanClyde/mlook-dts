<?php

use App\Jobs\CheckDocumentDeadlines;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule deadline checks every hour
Schedule::job(new CheckDocumentDeadlines)->hourly();

// Schedule automated backups
// Daily database backup at 2:00 AM with email notifications
Schedule::command('backup:database --notify')
    ->dailyAt('02:00')
    ->name('daily-database-backup')
    ->onSuccess(function () {
        \Log::info('Daily database backup completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Daily database backup failed');
    });

// Weekly full backup every Sunday at 3:00 AM with email notifications
Schedule::command('backup:full --notify')
    ->weeklyOn(0, '03:00')
    ->name('weekly-full-backup')
    ->onSuccess(function () {
        \Log::info('Weekly full backup completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Weekly full backup failed');
    });

// Clean old backups (older than 30 days) every week
Schedule::command('backup:clean --days=30')
    ->weekly()
    ->name('cleanup-old-backups')
    ->onSuccess(function () {
        \Log::info('Old backups cleaned successfully');
    });

// Clean old performance metrics (older than 30 days) every week
Schedule::command('performance:clean --days=30')
    ->weekly()
    ->name('cleanup-old-performance-metrics')
    ->onSuccess(function () {
        \Log::info('Old performance metrics cleaned successfully');
    });

// Send deadline reminders twice daily (9 AM and 3 PM)
Schedule::command('documents:send-deadline-reminders')
    ->twiceDaily(9, 15)
    ->name('deadline-reminders');
