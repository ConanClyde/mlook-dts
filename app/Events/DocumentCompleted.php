<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentCompleted implements ShouldBroadcastNow
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
        $channels = [
            // Broadcast to creator's channel
            new PrivateChannel('App.Models.User.'.$this->document->created_by),
            // Broadcast to current holder's channel
            new PrivateChannel('App.Models.User.'.$this->document->current_holder_id),
            // Broadcast to document-specific channel
            new PrivateChannel('document.'.$this->document->id),
            // Broadcast to documents channel for all users
            new PrivateChannel('documents'),
        ];

        // Also broadcast to all users involved in tracking
        $trackingUserIds = \App\Models\DocumentTracking::where('document_id', $this->document->id)
            ->distinct()
            ->pluck('from_user_id')
            ->merge(\App\Models\DocumentTracking::where('document_id', $this->document->id)
                ->distinct()
                ->pluck('to_user_id'))
            ->unique()
            ->filter()
            ->toArray();

        foreach ($trackingUserIds as $userId) {
            $channels[] = new PrivateChannel('App.Models.User.'.$userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'document.completed';
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
