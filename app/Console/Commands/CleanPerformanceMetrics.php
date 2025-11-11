<?php

namespace App\Console\Commands;

use App\Services\PerformanceMonitoringService;
use Illuminate\Console\Command;

class CleanPerformanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:clean {--days=30 : Number of days to keep metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old performance metrics based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(PerformanceMonitoringService $monitoringService): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning performance metrics older than {$days} days...");

        try {
            $deletedCount = $monitoringService->cleanOldMetrics($days);

            $this->info("Deleted {$deletedCount} old performance metric(s).");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clean performance metrics: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
