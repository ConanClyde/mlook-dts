<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignatureDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';

    public function shouldBroadcast(): bool
    {
        return true;
    }

    public $signatureId;
    public $documentId;

    public function __construct(int $signatureId, int $documentId)
    {
        $this->signatureId = $signatureId;
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
        return 'signature.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'signature_id' => $this->signatureId,
            'document_id' => $this->documentId,
        ];
    }
}

