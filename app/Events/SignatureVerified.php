<?php

namespace App\Events;

use App\Models\DocumentSignature;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignatureVerified implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
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
        return 'signature.verified';
    }

    public function broadcastWith(): array
    {
        return [
            'signature_id' => $this->signature->id,
            'document_id' => $this->signature->document_id,
            'is_verified' => $this->signature->is_verified,
            'verified_at' => $this->signature->verified_at?->toISOString(),
        ];
    }
}
