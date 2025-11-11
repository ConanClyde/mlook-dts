<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTracking extends Model
{
    use HasFactory;

    protected $table = 'document_tracking';

    public const ACTION_CREATED = 'created';

    public const ACTION_FORWARDED = 'forwarded';

    public const ACTION_ACKNOWLEDGED = 'acknowledged';

    public const ACTION_REVIEW_STARTED = 'review_started';

    public const ACTION_REVIEW_COMPLETED = 'review_completed';

    public const ACTION_SENT_FOR_APPROVAL = 'sent_for_approval';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_COMPLETED = 'completed';

    public const ACTION_RETURNED = 'returned';

    public const ACTION_ARCHIVED = 'archived';

    protected $fillable = [
        'document_id',
        'from_user_id',
        'to_user_id',
        'from_department_id',
        'to_department_id',
        'action',
        'remarks',
        'instructions',
        'sent_at',
        'received_at',
        'is_read',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * Get the document being tracked.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who sent the document.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who received the document.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the department the document came from.
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the department the document is going to.
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Mark as received.
     */
    public function markAsReceived(): void
    {
        $this->update([
            'received_at' => now(),
            'is_read' => true,
        ]);
    }
}
