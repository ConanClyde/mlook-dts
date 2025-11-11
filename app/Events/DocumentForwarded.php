<?php

namespace App\Events;

use App\Models\Document;
use App\Models\DocumentTracking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentForwarded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $document;

    public $tracking;

    public function __construct(Document $document, DocumentTracking $tracking)
    {
        $this->document = $document;
        $this->tracking = $tracking;

        \Log::info('DocumentForwarded event created', [
            'document_id' => $document->id,
            'to_user_id' => $tracking->to_user_id,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            // Broadcast to recipient's channel
            new PrivateChannel('App.Models.User.'.$this->tracking->to_user_id),
            // Broadcast to sender's channel
            new PrivateChannel('App.Models.User.'.$this->tracking->from_user_id),
            // Broadcast to document-specific channel
            new PrivateChannel('document.'.$this->document->id),
            // Broadcast to documents channel for all users
            new PrivateChannel('documents'),
            // Broadcast to admin notifications
            new PrivateChannel('admin.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.forwarded';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->document->id,
            'tracking_id' => $this->tracking->id,
            'tracking_number' => $this->document->tracking_number,
            'title' => $this->document->title,
            'urgency_level' => $this->document->urgency_level,
            'from_user' => $this->tracking->fromUser ? [
                'id' => $this->tracking->fromUser->id,
                'name' => $this->tracking->fromUser->full_name,
            ] : null,
            'to_user_id' => $this->tracking->to_user_id,
            'created_at' => $this->tracking->created_at->toISOString(),
            'document' => [
                'id' => $this->document->id,
                'created_by' => $this->document->created_by,
                'current_holder_id' => $this->document->current_holder_id,
                'status' => $this->document->status,
            ],
        ];
    }
}
