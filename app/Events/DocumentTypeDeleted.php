<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentTypeDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $documentTypeId;

    public function __construct($documentTypeId)
    {
        $this->documentTypeId = $documentTypeId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.settings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document-type.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'document_type_id' => $this->documentTypeId,
        ];
    }
}
