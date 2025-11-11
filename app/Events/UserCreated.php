<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user->load(['program', 'department']);
    }

    public function broadcastOn(): array
    {
        return [
            // Broadcast to admin users channel
            new PrivateChannel('admin.users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->user->id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'email' => $this->user->email,
            'university_id' => $this->user->university_id,
            'usertype' => $this->user->usertype,
            'program_id' => $this->user->program_id,
            'department_id' => $this->user->department_id,
            'created_at' => $this->user->created_at->toISOString(),
        ];
    }
}

