<?php

namespace App\Events;

use App\Models\RoutingRule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoutingRuleCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $rule;

    public function __construct(RoutingRule $rule)
    {
        $this->rule = $rule->load(['documentType', 'department', 'user']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.settings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'routing-rule.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->rule->id,
            'document_type_id' => $this->rule->document_type_id,
            'department_id' => $this->rule->department_id,
            'user_id' => $this->rule->user_id,
            'priority' => $this->rule->priority,
            'is_active' => $this->rule->is_active,
            'created_at' => $this->rule->created_at->toISOString(),
        ];
    }
}

