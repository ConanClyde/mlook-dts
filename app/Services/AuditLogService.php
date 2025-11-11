<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an audit event.
     */
    public function log(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        if (! config('security.audit_logging.enabled', true)) {
            return new AuditLog;
        }

        return AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log document creation.
     */
    public function logDocumentCreated(Model $document): void
    {
        $this->log(
            'document.created',
            $document,
            null,
            $document->toArray(),
            "Document '{$document->title}' created"
        );
    }

    /**
     * Log document update.
     */
    public function logDocumentUpdated(Model $document, array $oldValues): void
    {
        $this->log(
            'document.updated',
            $document,
            $oldValues,
            $document->toArray(),
            "Document '{$document->title}' updated"
        );
    }

    /**
     * Log document forwarded.
     */
    public function logDocumentForwarded(Model $document, $toUser): void
    {
        $this->log(
            'document.forwarded',
            $document,
            null,
            ['to_user_id' => $toUser->id, 'to_user_name' => $toUser->full_name],
            "Document '{$document->title}' forwarded to {$toUser->full_name}"
        );
    }

    /**
     * Log document approved.
     */
    public function logDocumentApproved(Model $document): void
    {
        $this->log(
            'document.approved',
            $document,
            null,
            null,
            "Document '{$document->title}' approved"
        );
    }

    /**
     * Log document rejected.
     */
    public function logDocumentRejected(Model $document, string $reason): void
    {
        $this->log(
            'document.rejected',
            $document,
            null,
            ['reason' => $reason],
            "Document '{$document->title}' rejected: {$reason}"
        );
    }

    /**
     * Log user login.
     */
    public function logUserLogin(): void
    {
        $this->log(
            'user.login',
            Auth::user(),
            null,
            null,
            'User logged in'
        );
    }

    /**
     * Log user logout.
     */
    public function logUserLogout(): void
    {
        $this->log(
            'user.logout',
            Auth::user(),
            null,
            null,
            'User logged out'
        );
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $email): void
    {
        $this->log(
            'user.login.failed',
            null,
            null,
            ['email' => $email],
            "Failed login attempt for: {$email}"
        );
    }

    /**
     * Clean old audit logs based on retention policy.
     */
    public function cleanOldLogs(): int
    {
        $retentionDays = config('security.audit_logging.retention_days', 90);

        return AuditLog::where('created_at', '<', now()->subDays($retentionDays))
            ->delete();
    }
}
