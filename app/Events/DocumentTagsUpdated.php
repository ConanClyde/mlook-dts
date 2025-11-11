<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentTagsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $document;
    public $tags;
    public $action; // 'added' or 'removed'
    public $userId; // User who made the change

    public function __construct(Document $document, array $tags, string $action = 'updated', ?int $userId = null)
    {
        $this->document = $document;
        $this->tags = $tags;
        $this->action = $action;
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        return [
            // Broadcast to specific document channel
            new PrivateChannel('document.'.$this->document->id),
            // Broadcast to creator's channel
            new PrivateChannel('App.Models.User.'.$this->document->created_by),
            // Broadcast to current holder's channel
            new PrivateChannel('App.Models.User.'.$this->document->current_holder_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.tags.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->document->id,
            'tags' => $this->tags,
            'action' => $this->action,
            'user_id' => $this->userId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
