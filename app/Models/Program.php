<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'college_id',
        'head_user_id',
    ];

    /**
     * Get the college that this program belongs to.
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'college_id');
    }

    /**
     * Get the program head.
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    /**
     * Get all users enrolled/assigned to this program.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all documents in this program.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'current_program_id');
    }
}
