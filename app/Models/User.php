<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Auditable, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'university_id',
        'usertype',
        'email',
        'password',
        'program_id',
        'department_id',
        'avatar',
        'notification_preferences',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's role.
     */
    public function getUserRole(): string
    {
        return $this->usertype;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->usertype === 'admin';
    }

    /**
     * Check if user is registrar.
     */
    public function isRegistrar(): bool
    {
        return $this->usertype === 'registrar';
    }

    /**
     * Check if user is a dean.
     */
    public function isDean(): bool
    {
        return $this->usertype === 'dean';
    }

    /**
     * Check if user is a department head.
     */
    public function isDepartmentHead(): bool
    {
        return $this->usertype === 'department_head';
    }

    /**
     * Check if user is faculty.
     */
    public function isFaculty(): bool
    {
        return $this->usertype === 'faculty';
    }

    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->usertype === 'staff';
    }

    /**
     * Check if user is student.
     */
    public function isStudent(): bool
    {
        return $this->usertype === 'student';
    }

    /**
     * Check if user has administrative privileges.
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->usertype, ['admin', 'registrar', 'dean', 'department_head']);
    }

    /**
     * Check if user can create documents.
     */
    public function canCreateDocuments(): bool
    {
        return in_array($this->usertype, ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff', 'student']);
    }

    /**
     * Get documents created by this user.
     */
    public function createdDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /**
     * Get documents currently held by this user.
     */
    public function heldDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'current_holder_id');
    }

    /**
     * Get document attachments uploaded by this user.
     */
    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class, 'uploaded_by');
    }

    /**
     * Get departments headed by this user.
     */
    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_user_id');
    }

    /**
     * Get the program this user belongs to (for students/faculty).
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the department this user belongs to (for staff, department heads, etc.).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get programs headed by this user.
     */
    public function headedPrograms(): HasMany
    {
        return $this->hasMany(Program::class, 'head_user_id');
    }

    /**
     * Get notifications for this user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return null;
    }

    /**
     * Get notification preference for a specific channel.
     */
    public function getNotificationPreference(string $channel): bool
    {
        $preferences = $this->notification_preferences ?? [];

        return $preferences[$channel] ?? true; // Default to enabled
    }

    /**
     * Update notification preferences.
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        $this->update(['notification_preferences' => $preferences]);
    }
}
