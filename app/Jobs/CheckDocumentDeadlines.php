<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckDocumentDeadlines implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Checking document deadlines...');

        // Get all active documents with deadlines
        $documents = Document::whereNotNull('deadline')
            ->whereNotIn('status', [
                Document::STATUS_COMPLETED,
                Document::STATUS_ARCHIVED,
                Document::STATUS_APPROVED,
            ])
            ->get();

        foreach ($documents as $document) {
            $this->checkDeadline($document);
        }

        Log::info('Finished checking document deadlines.');
    }

    /**
     * Check deadline for a specific document.
     */
    protected function checkDeadline(Document $document): void
    {
        $now = now();
        $deadline = $document->deadline;
        $hoursUntilDeadline = $now->diffInHours($deadline, false);

        // Mark as overdue if past deadline
        if ($now->isAfter($deadline) && ! $document->is_overdue) {
            $document->update(['is_overdue' => true]);
            $this->handleOverdueDocument($document);

            return;
        }

        // Send reminder 24 hours before deadline
        if ($hoursUntilDeadline > 0 && $hoursUntilDeadline <= 24 && ! $document->reminder_sent_at) {
            $this->sendDeadlineReminder($document, $hoursUntilDeadline);
        }

        // Send reminder 3 days before deadline
        if ($hoursUntilDeadline > 24 && $hoursUntilDeadline <= 72 && ! $document->reminder_sent_at) {
            $this->sendDeadlineReminder($document, $hoursUntilDeadline);
        }
    }

    /**
     * Send deadline reminder notification.
     */
    protected function sendDeadlineReminder(Document $document, int $hoursUntilDeadline): void
    {
        $currentHolder = $document->currentHolder;

        if (! $currentHolder) {
            return;
        }

        $daysUntilDeadline = ceil($hoursUntilDeadline / 24);
        $timeText = $daysUntilDeadline > 1
            ? "{$daysUntilDeadline} days"
            : "{$hoursUntilDeadline} hours";

        Notification::create([
            'user_id' => $currentHolder->id,
            'type' => 'deadline_reminder',
            'title' => 'Document Deadline Approaching',
            'message' => "Document '{$document->title}' is due in {$timeText}.",
            'link' => route('documents.show', $document->id),
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'deadline' => $document->deadline->toDateTimeString(),
                'hours_remaining' => $hoursUntilDeadline,
            ],
            'read' => false,
        ]);

        $document->update(['reminder_sent_at' => now()]);

        Log::info("Deadline reminder sent for document {$document->tracking_number}");
    }

    /**
     * Handle overdue document - escalate to supervisor.
     */
    protected function handleOverdueDocument(Document $document): void
    {
        $currentHolder = $document->currentHolder;

        if (! $currentHolder) {
            return;
        }

        // Notify current holder
        Notification::create([
            'user_id' => $currentHolder->id,
            'type' => 'document_overdue',
            'title' => 'Document Overdue',
            'message' => "Document '{$document->title}' is now overdue!",
            'link' => route('documents.show', $document->id),
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'deadline' => $document->deadline->toDateTimeString(),
            ],
            'read' => false,
        ]);

        // Escalate to department head or admin
        $this->escalateDocument($document, $currentHolder);

        Log::info("Document {$document->tracking_number} marked as overdue and escalated");
    }

    /**
     * Escalate document to supervisor or admin.
     */
    protected function escalateDocument(Document $document, User $currentHolder): void
    {
        // Find department head or admin to escalate to
        $escalateTo = null;

        // Try to find department head
        if ($currentHolder->department) {
            $escalateTo = User::where('department_id', $currentHolder->department_id)
                ->where('usertype', 'admin')
                ->first();
        }

        // If no department head, escalate to any admin
        if (! $escalateTo) {
            $escalateTo = User::where('usertype', 'admin')
                ->where('id', '!=', $currentHolder->id)
                ->first();
        }

        if ($escalateTo) {
            $document->update([
                'escalated_to' => $escalateTo->id,
                'escalated_at' => now(),
            ]);

            // Notify the escalation recipient
            Notification::create([
                'user_id' => $escalateTo->id,
                'type' => 'document_escalated',
                'title' => 'Overdue Document Escalated',
                'message' => "Overdue document '{$document->title}' has been escalated to you from {$currentHolder->name}.",
                'link' => route('documents.show', $document->id),
                'data' => [
                    'document_id' => $document->id,
                    'tracking_number' => $document->tracking_number,
                    'from_user' => $currentHolder->name,
                    'deadline' => $document->deadline->toDateTimeString(),
                ],
                'read' => false,
            ]);
        }
    }
}
