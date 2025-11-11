<?php

namespace App\Console\Commands;

use App\Services\EmailNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupFull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:full {--notify : Send email notification to admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full system backup (database + files) using Spatie Laravel Backup';

    /**
     * Execute the console command.
     */
    public function handle(EmailNotificationService $emailService): int
    {
        $this->info('Creating full system backup...');

        try {
            // Run Spatie backup command (full backup by default)
            Artisan::call('backup:run');

            $this->info('Full backup created successfully!');

            // Get the latest backup file info
            $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
            $backupPath = config('backup.backup.name', 'DTS');

            if ($disk->exists($backupPath)) {
                $files = collect($disk->files($backupPath))
                    ->filter(fn ($file) => str_ends_with($file, '.zip'))
                    ->sortByDesc(fn ($file) => $disk->lastModified($file))
                    ->first();

                if ($files) {
                    $size = $disk->size($files);
                    $formattedSize = $this->formatBytes($size);
                    $this->info("Size: {$formattedSize}");

                    // Send email notification if requested
                    if ($this->option('notify')) {
                        $emailService->sendBackupCompleted(
                            basename($files),
                            'full',
                            $formattedSize,
                            true
                        );
                        $this->info('Email notification sent to admins.');
                    }
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create full backup: {$e->getMessage()}");

            // Send failure notification if requested
            if ($this->option('notify')) {
                $emailService->sendBackupCompleted(
                    'full_backup',
                    'full',
                    '0 B',
                    false,
                    $e->getMessage()
                );
            }

            return Command::FAILURE;
        }
    }

    /**
     * Format bytes to human readable size.
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
