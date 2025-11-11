<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue connection to use when broadcasting the event.
     */
    public $connection = 'sync';

    /**
     * Determine if this event should broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        \Log::info('UserUpdated event created', [
            'user_id' => $user->id,
            'channels' => ['App.Models.User.'.$user->id, 'admin.users'],
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Broadcast to the user's personal channel
            new PrivateChannel('App.Models.User.'.$this->user->id),
            // Broadcast to admin channel if user is admin (so admins see updates in users table)
            new PrivateChannel('admin.users'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->user->id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'email' => $this->user->email,
            'university_id' => $this->user->university_id,
            'usertype' => $this->user->usertype,
            'avatar' => $this->user->avatar,
            'avatar_url' => $this->user->avatar ? asset('storage/'.$this->user->avatar) : null,
            'program_id' => $this->user->program_id,
            'department_id' => $this->user->department_id,
            'updated_at' => $this->user->updated_at->toISOString(),
        ];
    }
}
