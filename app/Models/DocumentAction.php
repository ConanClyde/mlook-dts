<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'action_type',
        'remarks',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the document this action belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the action type label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'viewed' => 'Viewed',
            'downloaded' => 'Downloaded',
            'commented' => 'Commented',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'approval_requested' => 'Approval Requested',
            'signed' => 'Signed',
            'archived' => 'Archived',
            'restored' => 'Restored',
            'edited' => 'Edited',
            'deleted' => 'Deleted',
            default => 'Unknown'
        };
    }

    /**
     * Get the action color for badges.
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action_type) {
            'viewed', 'downloaded' => 'blue',
            'commented', 'edited' => 'yellow',
            'approved', 'signed' => 'green',
            'rejected', 'deleted' => 'red',
            'approval_requested' => 'purple',
            'archived' => 'gray',
            'restored' => 'indigo',
            default => 'gray'
        };
    }
}
