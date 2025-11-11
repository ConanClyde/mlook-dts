<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $backupName;
    public $fileSize;

    public function __construct(string $backupName, int $fileSize)
    {
        $this->backupName = $backupName;
        $this->fileSize = $fileSize;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.backups'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'backup.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'backup_name' => $this->backupName,
            'file_size' => $this->fileSize,
            'completed_at' => now()->toISOString(),
        ];
    }
}
