<?php

namespace App\Services;

use App\Mail\BackupCompletedMail;
use App\Mail\DocumentApprovedMail;
use App\Mail\DocumentCompletedMail;
use App\Mail\DocumentDeadlineReminderMail;
use App\Mail\DocumentForwardedMail;
use App\Mail\DocumentReceivedMail;
use App\Mail\DocumentRejectedMail;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Send document forwarded email notification.
     */
    public function sendDocumentForwarded(Document $document, User $toUser, User $fromUser, string $remarks = ''): void
    {
        if ($this->shouldSendEmail($toUser, 'email')) {
            $this->dispatchMail(new DocumentForwardedMail($document, $fromUser, $remarks), $toUser->email);
        }
    }

    /**
     * Send document received confirmation email.
     */
    public function sendDocumentReceived(Document $document, User $creator, User $receivedBy, string $remarks = ''): void
    {
        if ($this->shouldSendEmail($creator, 'email')) {
            $this->dispatchMail(new DocumentReceivedMail($document, $receivedBy, $remarks), $creator->email);
        }
    }

    /**
     * Send document approved email notification.
     */
    public function sendDocumentApproved(Document $document, User $creator, User $approvedBy, string $remarks = ''): void
    {
        if ($this->shouldSendEmail($creator, 'email')) {
            $this->dispatchMail(new DocumentApprovedMail($document, $approvedBy, $remarks), $creator->email);
        }
    }

    /**
     * Send document rejected email notification.
     */
    public function sendDocumentRejected(Document $document, User $creator, User $rejectedBy, string $reason = ''): void
    {
        if ($this->shouldSendEmail($creator, 'email')) {
            $this->dispatchMail(new DocumentRejectedMail($document, $rejectedBy, $reason), $creator->email);
        }
    }

    /**
     * Send document completed email notification.
     */
    public function sendDocumentCompleted(Document $document, User $creator, User $completedBy): void
    {
        if ($this->shouldSendEmail($creator, 'email')) {
            $this->dispatchMail(new DocumentCompletedMail($document, $completedBy), $creator->email);
        }
    }

    /**
     * Send document deadline reminder email.
     */
    public function sendDeadlineReminder(Document $document, User $currentHolder, int $hoursRemaining): void
    {
        if ($this->shouldSendEmail($currentHolder, 'email')) {
            $this->dispatchMail(new DocumentDeadlineReminderMail($document, $hoursRemaining), $currentHolder->email);
        }
    }

    /**
     * Send backup completed email to admins.
     */
    public function sendBackupCompleted(
        string $backupName,
        string $backupType,
        string $backupSize,
        bool $success = true,
        ?string $errorMessage = null
    ): void {
        $admins = User::where('usertype', 'admin')->get();

        foreach ($admins as $admin) {
            if ($this->shouldSendEmail($admin, 'email')) {
                $this->dispatchMail(new BackupCompletedMail($backupName, $backupType, $backupSize, $success, $errorMessage), $admin->email);
            }
        }
    }

    /**
     * Check if email should be sent based on user preferences.
     */
    private function shouldSendEmail(User $user, string $channel): bool
    {
        // Check if user has email notification enabled
        return $user->getNotificationPreference($channel);
    }

    /**
     * Dispatch mail via queue when available, otherwise send immediately.
     */
    private function dispatchMail(object $mailable, string $recipient): void
    {
        $queue = config('queue.default');
        $shouldQueue = $queue && $queue !== 'sync';
        if ($shouldQueue) {
            Mail::to($recipient)->queue($mailable);
        } else {
            Mail::to($recipient)->send($mailable);
        }
    }

    /**
     * Send bulk email notifications.
     */
    public function sendBulkNotifications(array $users, callable $mailableFactory): void
    {
        foreach ($users as $user) {
            if ($this->shouldSendEmail($user, 'email')) {
                Mail::to($user->email)->queue($mailableFactory($user));
            }
        }
    }
}
