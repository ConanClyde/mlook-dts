<?php

namespace App\Events;

use App\Models\DocumentSignature;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignatureCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $signature;

    public function __construct(DocumentSignature $signature)
    {
        $this->signature = $signature;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('document.' . $this->signature->document_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'signature.created';
    }

    public function broadcastWith(): array
    {
        return [
            'signature' => [
                'id' => $this->signature->id,
                'document_id' => $this->signature->document_id,
                'user' => $this->signature->user()->select('id', 'first_name', 'last_name')->first(),
                'signature_type' => $this->signature->signature_type,
                'signed_at' => $this->signature->signed_at,
                'is_verified' => $this->signature->is_verified,
                // Exclude signature_data to reduce payload size
            ],
            'message' => 'New signature added',
        ];
    }
}
