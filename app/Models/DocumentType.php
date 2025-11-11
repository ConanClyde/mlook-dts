<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'allowed_roles',
        'allowed_receive',
        'auto_assign_enabled',
        'routing_logic',
        'default_receiver_role',
        'default_receiver_department_id',
        'default_receiver_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_roles' => 'array',
        'allowed_receive' => 'array',
        'auto_assign_enabled' => 'boolean',
    ];

    /**
     * Get all documents of this type.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the default receiver department.
     */
    public function defaultReceiverDepartment()
    {
        return $this->belongsTo(Department::class, 'default_receiver_department_id');
    }

    /**
     * Get the default receiver user.
     */
    public function defaultReceiverUser()
    {
        return $this->belongsTo(User::class, 'default_receiver_user_id');
    }

    /**
     * Check if a user role is allowed to create this document type.
     */
    public function canBeCreatedBy(string $userRole): bool
    {
        // If no allowed_roles specified, deny access (security by default)
        if (empty($this->allowed_roles) || ! is_array($this->allowed_roles)) {
            return false;
        }

        return in_array($userRole, $this->allowed_roles, true);
    }

    /**
     * Check if a user role is allowed to receive this document type.
     */
    public function canBeReceivedBy(string $userRole): bool
    {
        // If no allowed_receive specified, use allowed_roles as fallback
        if (empty($this->allowed_receive) || ! is_array($this->allowed_receive)) {
            return $this->canBeCreatedBy($userRole);
        }

        return in_array($userRole, $this->allowed_receive, true);
    }

    /**
     * Check if a user can access documents of this type.
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Admin can always access
        if ($user->isAdmin()) {
            return true;
        }

        $userRole = $user->getUserRole();

        // Check if user role is in allowed_roles or allowed_receive
        return $this->canBeCreatedBy($userRole) || $this->canBeReceivedBy($userRole);
    }
}
