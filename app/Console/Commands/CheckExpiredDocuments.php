<?php

namespace App\Console\Commands;

use App\Events\DocumentExpired;
use App\Models\Document;
use App\Services\EmailNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expired {--archive : Auto-archive expired documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired documents and optionally auto-archive them';

    protected $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired documents...');

        // Get documents that have expiration dates
        $documents = Document::whereNotNull('expiration_date')
            ->where('is_expired', false)
            ->whereDate('expiration_date', '<=', now())
            ->get();

        if ($documents->isEmpty()) {
            $this->info('No expired documents found.');
            return 0;
        }

        $expiredCount = 0;
        $archivedCount = 0;

        foreach ($documents as $document) {
            // Mark as expired
            $document->update([
                'is_expired' => true,
                'expired_at' => now(),
            ]);

            $expiredCount++;

            // Broadcast expiration event
            broadcast(new DocumentExpired($document))->toOthers();

            // Send notification to document creator and current holder
            $this->notifyExpiration($document);

            // Auto-archive if enabled
            if ($document->auto_archive_on_expiration || $this->option('archive')) {
                $document->update(['status' => 'archived']);
                $archivedCount++;
                $this->line("  ✓ Archived: {$document->tracking_number}");
            } else {
                $this->line("  ! Expired: {$document->tracking_number}");
            }
        }

        $this->info("\nProcessed {$expiredCount} expired document(s).");
        if ($archivedCount > 0) {
            $this->info("Auto-archived {$archivedCount} document(s).");
        }

        Log::info('Expired documents check completed', [
            'expired_count' => $expiredCount,
            'archived_count' => $archivedCount,
        ]);

        return 0;
    }

    /**
     * Send expiration notification
     */
    protected function notifyExpiration(Document $document)
    {
        try {
            // Notify creator
            if ($document->creator) {
                $this->emailService->sendEmail(
                    $document->creator->email,
                    'Document Expired',
                    "Document {$document->tracking_number} has expired.",
                    [
                        'document' => $document,
                        'message' => 'This document has reached its expiration date.',
                    ]
                );
            }

            // Notify current holder if different from creator
            if ($document->currentHolder && $document->currentHolder->id !== $document->created_by) {
                $this->emailService->sendEmail(
                    $document->currentHolder->email,
                    'Document Expired',
                    "Document {$document->tracking_number} has expired.",
                    [
                        'document' => $document,
                        'message' => 'This document in your possession has reached its expiration date.',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send expiration notification', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
