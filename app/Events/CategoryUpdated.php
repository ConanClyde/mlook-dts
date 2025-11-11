<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $category;
    public $action;

    public function __construct($category, string $action = 'updated')
    {
        $this->category = $category;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('categories'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'category.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'category' => $this->category,
            'action' => $this->action,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
