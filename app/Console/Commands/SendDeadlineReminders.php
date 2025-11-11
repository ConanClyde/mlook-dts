<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\EmailNotificationService;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:send-deadline-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for documents approaching their deadlines';

    /**
     * Execute the console command.
     */
    public function handle(EmailNotificationService $emailService): int
    {
        $this->info('Checking for documents with approaching deadlines...');

        // Get documents with deadlines in the next 24 hours that are not completed/archived
        $documents = Document::whereNotNull('deadline')
            ->whereNotIn('status', [
                Document::STATUS_COMPLETED,
                Document::STATUS_ARCHIVED,
                Document::STATUS_APPROVED,
            ])
            ->where('deadline', '>', now())
            ->where('deadline', '<=', now()->addHours(24))
            ->with(['currentHolder'])
            ->get();

        $count = 0;

        foreach ($documents as $document) {
            if ($document->currentHolder) {
                $hoursRemaining = (int) now()->diffInHours($document->deadline, false);

                if ($hoursRemaining > 0 && $hoursRemaining <= 24) {
                    $emailService->sendDeadlineReminder(
                        $document,
                        $document->currentHolder,
                        $hoursRemaining
                    );

                    $count++;
                    $this->line("Sent reminder for: {$document->tracking_number} to {$document->currentHolder->full_name}");
                }
            }
        }

        $this->info("Sent {$count} deadline reminder(s).");

        return Command::SUCCESS;
    }
}
