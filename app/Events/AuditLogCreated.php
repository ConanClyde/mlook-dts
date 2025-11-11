<?php

namespace App\Events;

use App\Models\AuditLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditLogCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $auditLog;

    public function __construct(AuditLog $auditLog)
    {
        $this->auditLog = $auditLog->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.audit-logs'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'audit-log.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->auditLog->id,
            'user_id' => $this->auditLog->user_id,
            'user_name' => $this->auditLog->user ? $this->auditLog->user->full_name : 'System',
            'action' => $this->auditLog->action,
            'description' => $this->auditLog->description,
            'ip_address' => $this->auditLog->ip_address,
            'user_agent' => $this->auditLog->user_agent,
            'created_at' => $this->auditLog->created_at->toISOString(),
        ];
    }
}
