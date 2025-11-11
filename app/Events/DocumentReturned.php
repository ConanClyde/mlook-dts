<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentReturned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document->load(['documentType', 'creator', 'currentHolder', 'originDepartment']);
    }

    public function broadcastOn(): array
    {
        return [
            // Broadcast to creator's channel
            new PrivateChannel('App.Models.User.'.$this->document->created_by),
            // Broadcast to document-specific channel
            new PrivateChannel('document.'.$this->document->id),
            // Broadcast to documents channel for all users
            new PrivateChannel('documents'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.returned';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->document->id,
            'tracking_number' => $this->document->tracking_number,
            'title' => $this->document->title,
            'status' => $this->document->status,
            'created_by' => $this->document->created_by,
            'current_holder_id' => $this->document->current_holder_id,
        ];
    }
}
