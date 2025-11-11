<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'from_user_id',
        'from_department_id',
        'to_user_id',
        'to_department_id',
        'action',
        'remarks',
        'forwarded_at',
        'received_at',
    ];

    protected $casts = [
        'forwarded_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Get the document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the sender user.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the sender department.
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the receiver user.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the receiver department.
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Calculate processing time in hours.
     */
    public function getProcessingTimeInHours(): ?float
    {
        if (! $this->forwarded_at || ! $this->received_at) {
            return null;
        }

        return $this->forwarded_at->diffInHours($this->received_at);
    }
}
