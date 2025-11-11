<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public int $documentId;

    public function __construct(int $documentId)
    {
        $this->documentId = $documentId;
    }

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('documents'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->documentId,
        ];
    }
}


