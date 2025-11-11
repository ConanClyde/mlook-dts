<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ROUTING = 'routing';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_FOR_APPROVAL = 'for_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Statuses that indicate the document still needs attention from the current holder.
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ROUTING,
        self::STATUS_RECEIVED,
        self::STATUS_IN_REVIEW,
        self::STATUS_FOR_APPROVAL,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_RETURNED,
    ];

    protected $fillable = [
        'tracking_number',
        'document_type_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'created_by',
        'current_holder_id',
        'origin_department_id',
        'status',
        'urgency_level',
        'deadline',
        'is_overdue',
        'remarks',
        'completed_at',
        'archived_at',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
        'deadline' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_overdue' => 'boolean',
    ];

    /**
     * Get the document type.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the user who created the document.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the origin department.
     */
    public function originDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'origin_department_id');
    }

    /**
     * Get the current holder.
     */
    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    /**
     * Get all tracking records of this document.
     */
    public function tracking(): HasMany
    {
        return $this->hasMany(DocumentTracking::class)->orderBy('created_at');
    }

    /**
     * Get all actions taken on this document.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(DocumentAction::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all comments on this document.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    /**
     * Get all attachments for this document.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all receivers for this document.
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(DocumentReceiver::class);
    }

    /**
     * Get all views for this document.
     */
    public function views(): HasMany
    {
        return $this->hasMany(DocumentView::class);
    }

    /**
     * Check if document has been viewed by a specific user.
     */
    public function hasBeenViewedBy(int $userId): bool
    {
        return $this->views()->where('user_id', $userId)->exists();
    }

    /**
     * Mark document as viewed by a user.
     */
    public function markAsViewedBy(int $userId): void
    {
        $this->views()->updateOrCreate(
            ['user_id' => $userId],
            ['viewed_at' => now()]
        );
    }

    /**
     * Get the user who escalated to.
     */
    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    /**
     * Get the user who approved this document.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this document.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }


    /**
     * Get all tags for this document.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tag', 'document_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Get all signatures for this document.
     */
    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /**
     * Generate a unique tracking number.
     */
    public static function generateTrackingNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -6));

        return "DMMMSU-{$year}{$month}-{$random}";
    }

    /**
     * Get the current status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'yellow',
            self::STATUS_ROUTING => 'blue',
            self::STATUS_RECEIVED, self::STATUS_IN_REVIEW => 'indigo',
            self::STATUS_FOR_APPROVAL => 'purple',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_RETURNED => 'orange',
            self::STATUS_ARCHIVED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the urgency level badge color.
     */
    public function getUrgencyColorAttribute(): string
    {
        return match ($this->urgency_level) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if document is overdue.
     */
    public function isOverdue(): bool
    {
        if (! $this->deadline) {
            return false;
        }

        return now()->isAfter($this->deadline) && ! in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_ARCHIVED,
            self::STATUS_APPROVED,
        ]);
    }

    /**
     * Check if deadline is approaching (within 24 hours).
     */
    public function isDeadlineApproaching(): bool
    {
        if (! $this->deadline) {
            return false;
        }

        $hoursUntilDeadline = now()->diffInHours($this->deadline, false);

        return $hoursUntilDeadline > 0 && $hoursUntilDeadline <= 24;
    }

    /**
     * Get hours until deadline.
     */
    public function getHoursUntilDeadline(): ?int
    {
        if (! $this->deadline) {
            return null;
        }

        return now()->diffInHours($this->deadline, false);
    }

    /**
     * Scope: Filter by search term (title, tracking number, description).
     */
    public function scopeSearch($query, ?string $searchTerm): void
    {
        if (empty($searchTerm)) {
            return;
        }

        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('tracking_number', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: Filter by document type.
     */
    public function scopeOfType($query, ?int $documentTypeId): void
    {
        if ($documentTypeId) {
            $query->where('document_type_id', $documentTypeId);
        }
    }

    /**
     * Scope: Filter by urgency level.
     */
    public function scopeWithUrgency($query, ?string $urgencyLevel): void
    {
        if ($urgencyLevel) {
            $query->where('urgency_level', $urgencyLevel);
        }
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeWithStatus($query, ?string $status): void
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    /**
     * Scope: Filter by date range.
     */
    public function scopeDateRange($query, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
    }

    /**
     * Scope: Filter by tags.
     */
    public function scopeWithTags($query, array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        $tagIds = array_filter($tagIds);
        if (! empty($tagIds)) {
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }
    }

    /**
     * Scope: Documents created by user.
     */
    public function scopeCreatedBy($query, int $userId): void
    {
        $query->where('created_by', $userId);
    }

    /**
     * Scope: Documents held by user.
     */
    public function scopeHeldBy($query, int $userId): void
    {
        $query->where('current_holder_id', $userId);
    }

    /**
     * Scope: Incoming documents for user (held by but not created by).
     */
    public function scopeIncomingFor($query, int $userId): void
    {
        $query->where('current_holder_id', $userId)
            ->where('created_by', '!=', $userId);
    }

    /**
     * Scope: Exclude statuses.
     */
    public function scopeExcludeStatuses($query, array $statuses): void
    {
        $query->whereNotIn('status', $statuses);
    }

    /**
     * Scope: Include only statuses.
     */
    public function scopeWithStatuses($query, array $statuses): void
    {
        $query->whereIn('status', $statuses);
    }

    /**
     * Scope: Documents accessible by user (based on role and involvement).
     */
    public function scopeAccessibleBy($query, User $user): void
    {
        if ($user->isAdmin()) {
            return; // Admins see all
        }

        $query->where(function ($q) use ($user) {
            // Documents created by user
            $q->where('created_by', $user->id)
                // Documents held by user
                ->orWhere('current_holder_id', $user->id)
                // Documents in user's department (if applicable)
                ->when($user->department_id, function ($subQ) use ($user) {
                    $subQ->orWhere('origin_department_id', $user->department_id);
                })
                // Documents user is involved in via tracking
                ->orWhereHas('tracking', function ($tq) use ($user) {
                    $tq->where('from_user_id', $user->id)
                        ->orWhere('to_user_id', $user->id);
                });

            // For deans and department heads, also include department-related documents
            if ($user->isDean() || $user->isDepartmentHead()) {
                $q->orWhereHas('creator', function ($uq) use ($user) {
                    $uq->where('department_id', $user->department_id);
                });
            }
        });
    }

    /**
     * Scope: Eager load common relationships.
     */
    public function scopeWithCommonRelations($query): void
    {
        $query->with(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tags']);
    }
}
