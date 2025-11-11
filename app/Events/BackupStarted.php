<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $backupName;

    public function __construct(string $backupName)
    {
        $this->backupName = $backupName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.backups'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'backup.started';
    }

    public function broadcastWith(): array
    {
        return [
            'backup_name' => $this->backupName,
            'started_at' => now()->toISOString(),
        ];
    }
}
