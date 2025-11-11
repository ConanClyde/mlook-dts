<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentReceiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'tracking_id',
        'receiver_id',
        'department_id',
        'status',
        'received_at',
        'completed_at',
        'remarks',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the document this receiver belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the tracking record.
     */
    public function tracking(): BelongsTo
    {
        return $this->belongsTo(DocumentTracking::class, 'tracking_id');
    }

    /**
     * Get the receiver user.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
