<?php

namespace App\Events;

use App\Models\Document;
use App\Models\DocumentTracking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentReceived implements ShouldBroadcastNow
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

        \Log::info('DocumentReceived event created', [
            'document_id' => $document->id,
            'received_by' => $tracking->to_user_id,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            // Broadcast to recipient's channel
            new PrivateChannel('App.Models.User.'.$this->tracking->to_user_id),
            // Broadcast to sender's channel (if exists)
            new PrivateChannel('App.Models.User.'.($this->tracking->from_user_id ?? 1)),
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
        return 'document.received';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->document->id,
            'tracking_id' => $this->tracking->id,
            'tracking_number' => $this->document->tracking_number,
            'title' => $this->document->title,
            'received_by' => [
                'id' => $this->tracking->toUser->id,
                'name' => $this->tracking->toUser->full_name,
            ],
            'received_at' => $this->tracking->received_at->toISOString(),
        ];
    }
}
