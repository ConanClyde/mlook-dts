<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:clean {--days=30 : Number of days to keep backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old backups based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning backups older than {$days} days...");

        try {
            // Use Spatie's built-in cleanup command
            Artisan::call('backup:clean --disable-notifications');

            // Additionally clean based on custom retention
            $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
            $backupPath = config('backup.backup.name', 'DTS');
            $deletedCount = 0;

            if ($disk->exists($backupPath)) {
                $files = $disk->files($backupPath);
                $cutoffDate = now()->subDays($days)->timestamp;

                foreach ($files as $file) {
                    if (str_ends_with($file, '.zip')) {
                        $fileDate = $disk->lastModified($file);

                        if ($fileDate < $cutoffDate) {
                            $disk->delete($file);
                            $deletedCount++;
                        }
                    }
                }
            }

            $this->info("Deleted {$deletedCount} old backup(s).");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clean backups: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
