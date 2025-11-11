<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'department_id',
        'user_id',
        'priority',
        'condition_type',
        'condition_value',
        'is_active',
        'description',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the document type.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get active routing rules for a document type.
     */
    public static function getActiveRulesForDocumentType($documentTypeId)
    {
        return self::where('document_type_id', $documentTypeId)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Check if rule conditions match the document.
     */
    public function matchesDocument(Document $document): bool
    {
        if ($this->condition_type === 'always') {
            return true;
        }

        if ($this->condition_type === 'urgency') {
            return $document->urgency_level === $this->condition_value;
        }

        if ($this->condition_type === 'department') {
            return $document->origin_department_id == $this->condition_value;
        }

        return false;
    }
}
