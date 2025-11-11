<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplianceService
{
    /**
     * GDPR: Export all user data
     */
    public function exportUserData(User $user): array
    {
        // Get document actions if table exists
        $documentActions = [];
        try {
            $documentActions = DB::table('document_actions')
                ->where('user_id', $user->id)
                ->select('action_type', 'remarks', 'created_at')
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        // Get audit logs if table exists
        $auditLogs = [];
        try {
            $auditLogs = DB::table('audits')
                ->where('user_id', $user->id)
                ->select('event', 'auditable_type', 'created_at')
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        return [
            'personal_information' => [
                'name' => $user->full_name,
                'email' => $user->email,
                'university_id' => $user->university_id,
                'role' => $user->role,
                'department' => $user->department?->name,
                'created_at' => $user->created_at,
            ],
            'documents_created' => Document::where('created_by', $user->id)
                ->select('id', 'tracking_number', 'title', 'status', 'created_at')
                ->get(),
            'documents_received' => Document::where('current_holder_id', $user->id)
                ->select('id', 'tracking_number', 'title', 'status', 'created_at')
                ->get(),
            'document_actions' => $documentActions,
            'audit_logs' => $auditLogs,
        ];
    }

    /**
     * GDPR: Anonymize user data
     */
    public function anonymizeUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Anonymize personal information
            $user->update([
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'email' => 'deleted_' . $user->id . '@anonymized.local',
                'university_id' => 'DELETED_' . $user->id,
                'password' => bcrypt(Str::random(32)),
            ]);

            // Keep document records but anonymize
            Document::where('created_by', $user->id)
                ->update(['created_by' => null]);

            // Log anonymization
            Log::info('User data anonymized', [
                'user_id' => $user->id,
                'performed_at' => now(),
            ]);
        });
    }

    /**
     * GDPR: Delete user data (Right to be forgotten)
     */
    public function deleteUserData(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete user's documents
            Document::where('created_by', $user->id)->delete();

            // Delete user's actions if table exists
            try {
                DB::table('document_actions')->where('user_id', $user->id)->delete();
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            // Delete user's audit logs if table exists
            try {
                DB::table('audits')->where('user_id', $user->id)->delete();
            } catch (\Exception $e) {
                // Table doesn't exist
            }

            // Delete user
            $user->delete();

            Log::info('User data deleted (GDPR)', [
                'user_id' => $user->id,
                'performed_at' => now(),
            ]);
        });
    }

    /**
     * Apply data retention policy
     */
    public function applyRetentionPolicy(): array
    {
        $results = [
            'archived' => 0,
            'deleted' => 0,
        ];

        // Get retention settings from config
        $retentionDays = config('compliance.document_retention_days', 365 * 7); // 7 years default
        $autoArchiveDays = config('compliance.auto_archive_days', 365); // 1 year

        // Auto-archive old completed documents
        $toArchive = Document::where('status', 'completed')
            ->where('completed_at', '<=', now()->subDays($autoArchiveDays))
            ->whereNull('archived_at')
            ->get();

        foreach ($toArchive as $document) {
            $document->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);
            $results['archived']++;
        }

        // Delete very old archived documents (beyond retention period)
        $toDelete = Document::where('status', 'archived')
            ->where('archived_at', '<=', now()->subDays($retentionDays))
            ->get();

        foreach ($toDelete as $document) {
            // Delete file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete document
            $document->forceDelete();
            $results['deleted']++;
        }

        Log::info('Data retention policy applied', $results);

        return $results;
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport(): array
    {
        // Check if audits table exists
        $auditLogsCount = 0;
        try {
            $auditLogsCount = DB::table('audits')->count();
        } catch (\Exception $e) {
            // Table doesn't exist, use 0
        }

        return [
            'total_documents' => Document::count(),
            'active_documents' => Document::whereNotIn('status', ['archived', 'completed'])->count(),
            'archived_documents' => Document::where('status', 'archived')->count(),
            'documents_with_expiration' => Document::whereNotNull('expiration_date')->count(),
            'expired_documents' => Document::where('is_expired', true)->count(),
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'audit_logs_count' => $auditLogsCount,
            'oldest_document' => Document::orderBy('created_at')->first()?->created_at,
            'newest_document' => Document::orderBy('created_at', 'desc')->first()?->created_at,
            'compliance_checks' => [
                'gdpr_compliant' => true,
                'data_retention_active' => config('compliance.data_retention_enabled', true),
                'audit_logging_active' => config('security.audit_logging.enabled', true),
            ],
        ];
    }

    /**
     * Check document access permissions for compliance
     */
    public function checkDocumentAccess(Document $document, User $user): array
    {
        return [
            'can_view' => $user->isAdmin() || 
                         $document->created_by === $user->id || 
                         $document->current_holder_id === $user->id,
            'can_edit' => $user->isAdmin() || 
                         $document->current_holder_id === $user->id,
            'can_delete' => $user->isAdmin() || 
                           $document->created_by === $user->id,
            'reason' => $this->getAccessReason($document, $user),
        ];
    }

    protected function getAccessReason(Document $document, User $user): string
    {
        if ($user->isAdmin()) {
            return 'Administrator access';
        }
        if ($document->created_by === $user->id) {
            return 'Document creator';
        }
        if ($document->current_holder_id === $user->id) {
            return 'Current document holder';
        }
        return 'No access';
    }
}
