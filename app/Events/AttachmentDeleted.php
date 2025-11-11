<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $attachmentId;
    public $documentId;

    public function __construct(int $attachmentId, int $documentId)
    {
        $this->attachmentId = $attachmentId;
        $this->documentId = $documentId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('document.' . $this->documentId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attachment.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'attachment_id' => $this->attachmentId,
            'document_id' => $this->documentId,
        ];
    }
}
