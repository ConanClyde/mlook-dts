<?php

namespace App\Events;

use App\Models\DocumentAttachment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentUploaded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection = 'sync';
    public $attachment;

    public function __construct(DocumentAttachment $attachment)
    {
        $this->attachment = $attachment->load('uploadedBy');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('document.' . $this->attachment->document_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attachment.uploaded';
    }

    public function broadcastWith(): array
    {
        return [
            'attachment' => [
                'id' => $this->attachment->id,
                'document_id' => $this->attachment->document_id,
                'filename' => $this->attachment->filename,
                'original_filename' => $this->attachment->original_filename,
                'file_size' => $this->attachment->file_size,
                'mime_type' => $this->attachment->mime_type,
                'file_path' => $this->attachment->file_path,
                'uploaded_by' => $this->attachment->uploadedBy ? [
                    'id' => $this->attachment->uploadedBy->id,
                    'name' => $this->attachment->uploadedBy->full_name,
                ] : null,
                'created_at' => $this->attachment->created_at->toISOString(),
            ],
        ];
    }
}
