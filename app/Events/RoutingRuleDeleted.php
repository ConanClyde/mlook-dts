<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoutingRuleDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $ruleId;

    public function __construct(int $ruleId)
    {
        $this->ruleId = $ruleId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.settings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'routing-rule.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->ruleId,
        ];
    }
}

