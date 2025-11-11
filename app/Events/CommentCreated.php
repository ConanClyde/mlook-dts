<?php

namespace App\Events;

use App\Models\DocumentComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(DocumentComment $comment)
    {
        $this->comment = $comment->load('user');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('document.' . $this->comment->document_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'document_id' => $this->comment->document_id,
                'user_id' => $this->comment->user_id,
                'comment' => $this->comment->comment,
                'is_internal' => $this->comment->is_internal,
                'created_at' => $this->comment->created_at->toISOString(),
                'user' => [
                    'id' => $this->comment->user->id,
                    'full_name' => $this->comment->user->full_name,
                    'first_name' => $this->comment->user->first_name,
                    'avatar' => $this->comment->user->avatar,
                    'avatar_url' => $this->comment->user->avatar_url ?? null,
                ],
            ],
        ];
    }
}
