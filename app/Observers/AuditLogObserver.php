<?php

namespace App\Observers;

use App\Events\AuditLogCreated;
use App\Models\AuditLog;

class AuditLogObserver
{
    /**
     * Handle the AuditLog "created" event.
     */
    public function created(AuditLog $auditLog): void
    {
        // Broadcast the audit log creation event
        broadcast(new AuditLogCreated($auditLog));
    }
}
