<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $key;
    public $value;
    public $category;

    public function __construct(string $key, $value, string $category = 'general')
    {
        $this->key = $key;
        $this->value = $value;
        $this->category = $category;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.settings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'setting.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'category' => $this->category,
        ];
    }
}
