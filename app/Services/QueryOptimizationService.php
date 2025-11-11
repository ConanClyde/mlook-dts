<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizationService
{
    /**
     * Enable query logging for debugging.
     */
    public function enableQueryLog(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Get executed queries.
     */
    public function getQueryLog(): array
    {
        return DB::getQueryLog();
    }

    /**
     * Log slow queries.
     */
    public function logSlowQueries(int $thresholdMs = 1000): void
    {
        DB::listen(function ($query) use ($thresholdMs) {
            if ($query->time > $thresholdMs) {
                Log::warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time.'ms',
                ]);
            }
        });
    }

    /**
     * Analyze a query and suggest optimizations.
     */
    public function analyzeQuery(string $sql): array
    {
        $explain = DB::select("EXPLAIN QUERY PLAN {$sql}");

        $suggestions = [];

        foreach ($explain as $row) {
            $detail = (array) $row;

            // Check for table scans
            if (stripos($detail['detail'] ?? '', 'SCAN TABLE') !== false) {
                $suggestions[] = 'Consider adding an index - full table scan detected';
            }

            // Check for missing indexes
            if (stripos($detail['detail'] ?? '', 'USING INTEGER PRIMARY KEY') === false
                && stripos($detail['detail'] ?? '', 'USING INDEX') === false) {
                $suggestions[] = 'No index being used - consider adding appropriate indexes';
            }
        }

        return [
            'explain' => $explain,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Get database statistics.
     */
    public function getDatabaseStats(): array
    {
        $stats = [];

        // Get table sizes (SQLite specific)
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            $tableName = $table->name;
            $count = DB::table($tableName)->count();

            $stats[$tableName] = [
                'row_count' => $count,
            ];
        }

        return $stats;
    }

    /**
     * Vacuum database (SQLite optimization).
     */
    public function vacuumDatabase(): void
    {
        DB::statement('VACUUM');
    }

    /**
     * Analyze database (update statistics).
     */
    public function analyzeDatabase(): void
    {
        DB::statement('ANALYZE');
    }

    /**
     * Get index information for a table.
     */
    public function getTableIndexes(string $tableName): array
    {
        return DB::select("PRAGMA index_list('{$tableName}')");
    }

    /**
     * Suggest indexes based on common queries.
     */
    public function suggestIndexes(): array
    {
        return [
            'documents' => [
                'Composite index on (current_holder_id, status, created_at) for inbox queries',
                'Composite index on (created_by, status) for sent documents',
                'Index on tracking_number for quick lookups',
                'Index on deadline for overdue checks',
            ],
            'document_tracking' => [
                'Composite index on (document_id, created_at) for tracking history',
                'Index on action for filtering by action type',
            ],
            'notifications' => [
                'Composite index on (user_id, read_at) for unread notifications',
            ],
            'users' => [
                'Index on usertype for role-based queries',
                'Index on department_id for department filtering',
            ],
        ];
    }
}
