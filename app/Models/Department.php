<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'head_user_id',
    ];

    /**
     * Get the department head.
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    /**
     * Get all documents in this department.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'current_department_id');
    }

    /**
     * Get all documents that originated from this department.
     */
    public function originatedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'origin_department_id');
    }

    /**
     * Get all programs in this college (if this is a college).
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'college_id');
    }

    /**
     * Get all users assigned to this department.
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this is a college.
     */
    public function isCollege(): bool
    {
        return $this->type === 'college';
    }

    /**
     * Check if this is an administrative department.
     */
    public function isAdministrativeDepartment(): bool
    {
        return $this->type === 'department';
    }
}
