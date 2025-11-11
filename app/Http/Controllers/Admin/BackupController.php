<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index(Request $request)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backups = collect();

        try {
            if ($disk->exists(config('backup.backup.name'))) {
                $files = $disk->files(config('backup.backup.name'));

                foreach ($files as $file) {
                    if (str_ends_with($file, '.zip')) {
                        $backups->push([
                            'path' => $file,
                            'name' => basename($file),
                            'size' => $disk->size($file),
                            'date' => $disk->lastModified($file),
                            'formatted_size' => $this->formatBytes($disk->size($file)),
                            'formatted_date' => date('M d, Y H:i', $disk->lastModified($file)),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->error('Backup listing error: '.$e->getMessage());
        }

        $backups = $backups->sortByDesc('date')->values();

        // Pagination
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $currentPage = $request->input('page', 1);
        $currentPage = max(1, (int) $currentPage);
        $total = $backups->count();
        $items = $backups->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create a LengthAwarePaginator manually
        $backupsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        $backupsPaginated->withQueryString();

        return view('admin.backups.index', compact('backupsPaginated'));
    }

    /**
     * Create a new backup.
     */
    public function store(Request $request)
    {
        try {
            $backupName = 'backup-' . now()->format('Y-m-d-H-i-s');
            
            // Broadcast backup started event
            broadcast(new \App\Events\BackupStarted($backupName));
            
            Artisan::call('backup:run');
            
            // Get the created backup file size
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $files = $disk->files(config('backup.backup.name'));
            $latestFile = collect($files)->sortByDesc(fn($f) => $disk->lastModified($f))->first();
            $fileSize = $latestFile ? $disk->size($latestFile) : 0;
            
            // Broadcast backup completed event
            broadcast(new \App\Events\BackupCompleted($backupName, $fileSize));

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            // Broadcast backup failed event
            broadcast(new \App\Events\BackupFailed('backup-failed', $e->getMessage()));
            
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to create backup: '.$e->getMessage());
        }
    }

    /**
     * Download a backup.
     */
    public function download($filename)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $path = config('backup.backup.name').'/'.$filename;

        if (! $disk->exists($path)) {
            abort(404, 'Backup file not found');
        }

        return $disk->download($path);
    }

    /**
     * Delete a backup.
     */
    public function destroy($filename)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $path = config('backup.backup.name').'/'.$filename;

            if ($disk->exists($path)) {
                $disk->delete($path);
            }

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to delete backup: '.$e->getMessage());
        }
    }

    /**
     * Clean old backups.
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $days = $request->input('days');
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $deletedCount = 0;

            if ($disk->exists(config('backup.backup.name'))) {
                $files = $disk->files(config('backup.backup.name'));
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

            return redirect()->route('admin.backups.index')
                ->with('success', "Successfully deleted {$deletedCount} old backup(s).");
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to clean backups: '.$e->getMessage());
        }
    }

    /**
     * Bulk delete backups.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'filenames' => 'required|array',
            'filenames.*' => 'required|string',
        ]);

        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $deletedCount = 0;

            foreach ($request->filenames as $filename) {
                $path = config('backup.backup.name').'/'.$filename;

                if ($disk->exists($path)) {
                    $disk->delete($path);
                    $deletedCount++;
                }
            }

            return redirect()->route('admin.backups.index')
                ->with('success', "Successfully deleted {$deletedCount} backup(s).");
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to delete backups: '.$e->getMessage());
        }
    }

    /**
     * Format bytes to human readable size.
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
