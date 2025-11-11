<?php

namespace App\Events;

use App\Models\DocumentType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentTypeUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $documentType;

    public function __construct(DocumentType $documentType)
    {
        $this->documentType = $documentType;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.settings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document-type.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'documentType' => $this->documentType,
        ];
    }
}
