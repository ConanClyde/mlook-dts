<?php

namespace App\Http\Controllers;

use App\Events\DocumentApproved;
use App\Events\DocumentCompleted;
use App\Events\DocumentForwarded;
use App\Events\DocumentReceived;
use App\Events\DocumentRejected;
use App\Events\DocumentReturned;
use App\Events\DocumentUpdated;
use App\Events\NotificationCreated;
use App\Http\Requests\ApproveDocumentRequest;
use App\Http\Requests\CompleteDocumentRequest;
use App\Http\Requests\ForwardDocumentRequest;
use App\Http\Requests\RejectDocumentRequest;
use App\Http\Requests\ReturnDocumentRequest;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentReceiver;
use App\Models\DocumentTracking;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\EmailNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTrackingController extends Controller
{
    /**
     * Forward document to another user or multiple users.
     */
    public function forward(ForwardDocumentRequest $request, Document $document, AuditLogService $auditLog, EmailNotificationService $emailService)
    {
        $user = Auth::user();

        // Check authorization and return JSON error if unauthorized (for AJAX/JSON requests)
        $this->handleAuthorization(
            fn () => $this->authorize('forward', $document),
            $request,
            'You are not authorized to forward this document. Only the current holder can forward documents.'
        );

        $validated = $request->validated();

        // Support both single and multiple receivers
        $receiverIds = [];
        if (isset($validated['to_user_id'])) {
            $receiverIds = [$validated['to_user_id']];
        } elseif (isset($validated['receiver_ids'])) {
            $receiverIds = is_array($validated['receiver_ids'])
                ? $validated['receiver_ids']
                : explode(',', $validated['receiver_ids']);
        }

        if (empty($receiverIds)) {
            return response()->json([
                'success' => false,
                'error' => 'No receivers specified',
            ], 422);
        }

        $toUser = User::findOrFail($receiverIds[0]); // Primary receiver

        // Prevent forwarding to students
        if ($toUser->isStudent()) {
            return response()->json([
                'error' => 'Documents cannot be forwarded to students',
                'success' => false,
            ], 422);
        }

        // Load document type and check if recipient can receive this document type
        $document->loadMissing('documentType');
        if ($document->documentType && ! $user->isAdmin()) {
            $recipientRole = $toUser->getUserRole();
            if (! $document->documentType->canBeReceivedBy($recipientRole)) {
                return response()->json([
                    'error' => "This document type cannot be forwarded to users with role: {$recipientRole}",
                    'success' => false,
                ], 422);
            }
        }

        $intent = $validated['intent'] ?? 'route';

        if ($intent === 'approval' && ! $toUser->hasAdminPrivileges() && ! $toUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'error' => 'Only administrative users can receive documents for approval.',
                'message' => 'Invalid recipient for approval request',
            ], 422);
        }

        $trackingAction = $intent === 'approval'
            ? DocumentTracking::ACTION_SENT_FOR_APPROVAL
            : DocumentTracking::ACTION_FORWARDED;

        $nextStatus = $intent === 'approval'
            ? Document::STATUS_FOR_APPROVAL
            : Document::STATUS_ROUTING;

        $tracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $toUser->id,
            'from_department_id' => $user->department_id,
            'to_department_id' => $toUser->department_id,
            'action' => $trackingAction,
            'remarks' => $validated['remarks'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        $document->current_holder_id = $toUser->id;
        $document->status = $nextStatus;

        if ($intent === 'approval') {
            $document->approval_status = 'pending';
            $document->approved_by = null;
            $document->approved_at = null;
            $document->approval_remarks = null;
            $document->rejected_by = null;
            $document->rejected_at = null;
            $document->rejection_reason = null;
        }

        $document->save();

        $actionRemarks = $intent === 'approval'
            ? "Document sent to {$toUser->full_name} for approval"
            : "Document forwarded from {$user->full_name} to {$toUser->full_name}";

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => $intent === 'approval' ? 'approval_requested' : 'forwarded',
            'remarks' => $actionRemarks,
            'metadata' => [
                'tracking_id' => $tracking->id,
                'intent' => $intent,
            ],
        ]);

        $notificationTitle = $intent === 'approval'
            ? 'Document Requires Your Approval'
            : 'Document Forwarded to You';

        $notificationMessage = $intent === 'approval'
            ? "{$user->full_name} sent a document for your approval: {$document->title}"
            : "{$user->full_name} forwarded a document to you: {$document->title}";

        $notificationType = $intent === 'approval'
            ? 'document_for_approval'
            : 'document_forwarded';

        $notification = Notification::create([
            'user_id' => $toUser->id,
            'type' => $notificationType,
            'title' => $notificationTitle,
            'message' => $notificationMessage,
            'link' => route('documents.show', $document->id),
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'from_user_id' => $user->id,
                'tracking_id' => $tracking->id,
                'intent' => $intent,
            ],
            'read' => false,
        ]);

        broadcast(new DocumentForwarded($document, $tracking));
        broadcast(new NotificationCreated($notification, $toUser->id));

        // Send email notification to primary receiver
        $emailService->sendDocumentForwarded($document, $toUser, $user, $validated['remarks'] ?? '');

        // Log document forwarding
        $auditLog->logDocumentForwarded($document, $toUser);

        // Create receiver records for all recipients
        $receivers = [];
        foreach ($receiverIds as $receiverId) {
            $receiver = User::find($receiverId);
            if ($receiver && ! $receiver->isStudent()) {
                $documentReceiver = DocumentReceiver::create([
                    'document_id' => $document->id,
                    'tracking_id' => $tracking->id,
                    'receiver_id' => $receiverId,
                    'department_id' => $receiver->department_id,
                    'status' => 'pending',
                ]);
                $receivers[] = $documentReceiver;

                // Send notification to additional receivers (not the primary one)
                if ($receiverId != $toUser->id) {
                    $additionalNotification = Notification::create([
                        'user_id' => $receiverId,
                        'type' => $notificationType,
                        'title' => $notificationTitle,
                        'message' => $notificationMessage,
                        'link' => route('documents.show', $document->id),
                        'data' => [
                            'document_id' => $document->id,
                            'tracking_number' => $document->tracking_number,
                            'from_user_id' => $user->id,
                            'tracking_id' => $tracking->id,
                            'intent' => $intent,
                        ],
                        'read' => false,
                    ]);
                    broadcast(new NotificationCreated($additionalNotification, $receiverId));

                    // Send email to additional receivers
                    $emailService->sendDocumentForwarded($document, $receiver, $user, $validated['remarks'] ?? '');
                }
            }
        }

        $receiverCount = count($receivers);
        $message = $receiverCount > 1
            ? "Document forwarded to {$receiverCount} recipients"
            : ($intent === 'approval'
                ? "Document sent to {$toUser->full_name} for approval"
                : "Document forwarded to {$toUser->full_name}");

        return response()->json([
            'success' => true,
            'message' => $message,
            'tracking' => $tracking->load(['fromUser', 'toUser']),
            'receivers' => $receivers,
            'receiver_count' => $receiverCount,
        ]);
    }

    /**
     * Receive/acknowledge document.
     */
    public function receive(Request $request, DocumentTracking $tracking, EmailNotificationService $emailService)
    {
        $user = Auth::user();

        // Students cannot receive documents
        if ($user->isStudent()) {
            return response()->json(['error' => 'Students cannot receive documents'], 403);
        }

        // Only recipient can receive
        if ($tracking->to_user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark as received
        $tracking->update([
            'received_at' => now(),
            'is_read' => true,
        ]);

        $document = $tracking->document;

        $nextStatus = match ($document->status) {
            Document::STATUS_FOR_APPROVAL => Document::STATUS_FOR_APPROVAL,
            Document::STATUS_ROUTING, Document::STATUS_RECEIVED => Document::STATUS_IN_REVIEW,
            default => $document->status,
        };

        if ($document->status !== $nextStatus) {
            $document->status = $nextStatus;
            $document->save();
        }

        $acknowledgement = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'from_department_id' => $user->department_id,
            'to_department_id' => $user->department_id,
            'action' => DocumentTracking::ACTION_ACKNOWLEDGED,
            'remarks' => $tracking->remarks,
            'instructions' => $tracking->instructions,
            'sent_at' => now(),
            'received_at' => now(),
            'is_read' => true,
        ]);

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'received',
            'remarks' => "Document acknowledged by {$user->full_name}",
            'metadata' => [
                'tracking_id' => $acknowledgement->id,
            ],
        ]);

        // Notify sender
        if ($tracking->from_user_id) {
            $notification = Notification::create([
                'user_id' => $tracking->from_user_id,
                'type' => 'document_received',
                'title' => 'Document Received',
                'message' => "{$user->full_name} received the document: {$document->title}",
                'link' => route('documents.show', $document->id),
                'data' => [
                    'document_id' => $document->id,
                    'tracking_number' => $document->tracking_number,
                    'received_by' => $user->id,
                    'tracking_id' => $tracking->id,
                ],
                'read' => false,
            ]);

            broadcast(new DocumentReceived($document, $tracking));
            broadcast(new NotificationCreated($notification, $tracking->from_user_id));

            // Send email notification to sender
            $sender = User::find($tracking->from_user_id);
            if ($sender) {
                $emailService->sendDocumentReceived($document, $sender, $user, $tracking->remarks ?? '');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Document received successfully',
        ]);
    }

    /**
     * Complete document processing.
     */
    public function complete(CompleteDocumentRequest $request, Document $document, EmailNotificationService $emailService)
    {
        $user = Auth::user();

        // Check authorization and return JSON error if unauthorized
        $this->handleAuthorization(
            fn () => $this->authorize('complete', $document),
            $request,
            'You are not authorized to complete this document. Only the current holder can complete documents.'
        );

        $validated = $request->validated();

        $document->status = Document::STATUS_COMPLETED;
        $document->completed_at = now();
        $document->remarks = $validated['remarks'] ?? $document->remarks;
        // Set current holder back to creator when document is completed
        $document->current_holder_id = $document->created_by;
        $document->save();

        $completionRecipientId = $document->created_by ?: $user->id;
        $completionRecipientDepartmentId = optional($document->creator)->department_id ?? $user->department_id;

        $completionTracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $completionRecipientId,
            'from_department_id' => $user->department_id,
            'to_department_id' => $completionRecipientDepartmentId,
            'action' => DocumentTracking::ACTION_COMPLETED,
            'remarks' => $validated['remarks'] ?? 'Document processing completed',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'completed',
            'remarks' => "Document marked as completed by {$user->full_name}",
            'metadata' => [
                'tracking_id' => $completionTracking->id,
            ],
        ]);

        // Notify creator if different from current user
        if ($document->created_by != $user->id) {
            $notification = Notification::create([
                'user_id' => $document->created_by,
                'type' => 'document_completed',
                'title' => 'Document Completed',
                'message' => "Your document '{$document->title}' has been completed by {$user->full_name}",
                'link' => route('documents.show', $document->id),
                'data' => [
                    'document_id' => $document->id,
                    'tracking_number' => $document->tracking_number,
                    'completed_by' => $user->id,
                ],
                'read' => false,
            ]);

            broadcast(new NotificationCreated($notification, $document->created_by));

            // Send email notification to creator
            $creator = User::find($document->created_by);
            if ($creator) {
                $emailService->sendDocumentCompleted($document, $creator, $user);
            }
        }

        // Broadcast document completed event
        broadcast(new DocumentCompleted($document));
        broadcast(new DocumentUpdated($document));

        return response()->json([
            'success' => true,
            'message' => 'Document marked as completed',
        ]);
    }

    /**
     * Return document to sender.
     */
    public function return(ReturnDocumentRequest $request, DocumentTracking $tracking)
    {
        $user = Auth::user();

        // Only recipient can return
        if ($tracking->to_user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $document = $tracking->document;

        // Cannot return your own documents
        if ($document->created_by === $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'You cannot return documents you created yourself.',
                'message' => 'Invalid operation',
            ], 403);
        }

        $returnRecipientId = $tracking->from_user_id ?: $document->created_by;
        $returnRecipientDepartmentId = $tracking->from_department_id ?? optional($document->creator)->department_id;

        $returnTracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $returnRecipientId,
            'from_department_id' => $user->department_id,
            'to_department_id' => $returnRecipientDepartmentId,
            'action' => DocumentTracking::ACTION_RETURNED,
            'remarks' => $validated['remarks'],
            'sent_at' => now(),
            'is_read' => false,
        ]);

        $document->current_holder_id = $returnRecipientId;
        $document->status = Document::STATUS_RETURNED;
        $document->save();

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'returned',
            'remarks' => "Document returned by {$user->full_name} with remarks: {$validated['remarks']}",
            'metadata' => [
                'tracking_id' => $returnTracking->id,
            ],
        ]);

        // Notify original sender
        $notification = Notification::create([
            'user_id' => $returnRecipientId,
            'type' => 'document_returned',
            'title' => 'Document Returned',
            'message' => "{$user->full_name} returned the document: {$document->title}. Remarks: {$validated['remarks']}",
            'link' => route('documents.show', $document->id),
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'remarks' => $validated['remarks'],
            ],
            'read' => false,
        ]);

        broadcast(new NotificationCreated($notification, $returnRecipientId));

        // Broadcast document returned event
        broadcast(new DocumentReturned($document));
        broadcast(new DocumentUpdated($document));

        return response()->json([
            'success' => true,
            'message' => 'Document returned successfully',
        ]);
    }

    /**
     * Approve document.
     */
    public function approve(ApproveDocumentRequest $request, Document $document, AuditLogService $auditLog, EmailNotificationService $emailService)
    {
        $user = Auth::user();

        // Check authorization and return JSON error if unauthorized
        $this->handleAuthorization(
            fn () => $this->authorize('approve', $document),
            $request,
            'You are not authorized to approve this document.'
        );

        $validated = $request->validated();

        $document->approval_status = 'approved';
        $document->approved_by = $user->id;
        $document->approved_at = now();
        $document->approval_remarks = $validated['remarks'] ?? null;
        $document->status = Document::STATUS_APPROVED;
        $document->completed_at = $document->completed_at ?? now();
        // Set current holder back to creator when document is approved (considered completed)
        $document->current_holder_id = $document->created_by;
        $document->save();

        $approvalTracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $document->created_by,
            'from_department_id' => $user->department_id,
            'to_department_id' => optional($document->creator)->department_id,
            'action' => DocumentTracking::ACTION_APPROVED,
            'remarks' => $validated['remarks'] ?? 'Document approved',
            'sent_at' => now(),
            'is_read' => false,
        ]);

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'approved',
            'remarks' => "Document approved by {$user->full_name}",
            'metadata' => [
                'tracking_id' => $approvalTracking->id,
            ],
        ]);

        // Notify creator
        if ($document->created_by != $user->id) {
            $notification = Notification::create([
                'user_id' => $document->created_by,
                'type' => 'document_approved',
                'title' => 'Document Approved',
                'message' => "{$user->full_name} approved your document: {$document->title}",
                'link' => route('documents.show', $document->id),
                'data' => [
                    'document_id' => $document->id,
                    'tracking_number' => $document->tracking_number,
                    'approved_by' => $user->id,
                ],
                'read' => false,
            ]);

            broadcast(new NotificationCreated($notification, $document->created_by));

            // Send email notification to creator
            $creator = User::find($document->created_by);
            if ($creator) {
                $emailService->sendDocumentApproved($document, $creator, $user, $validated['remarks'] ?? '');
            }
        }

        // Log document approval
        $auditLog->logDocumentApproved($document);

        // Broadcast document approved event
        broadcast(new DocumentApproved($document));
        broadcast(new DocumentUpdated($document));

        return response()->json([
            'success' => true,
            'message' => 'Document approved successfully',
        ]);
    }

    /**
     * Reject document.
     */
    public function reject(RejectDocumentRequest $request, Document $document, AuditLogService $auditLog, EmailNotificationService $emailService)
    {
        $user = Auth::user();

        // Check authorization and return JSON error if unauthorized
        $this->handleAuthorization(
            fn () => $this->authorize('reject', $document),
            $request,
            'You are not authorized to reject this document.'
        );

        $validated = $request->validated();

        $document->approval_status = 'rejected';
        $document->rejected_by = $user->id;
        $document->rejected_at = now();
        $document->rejection_reason = $validated['reason'];
        $document->status = Document::STATUS_REJECTED;
        $document->current_holder_id = $document->created_by;
        $document->save();

        $rejectionTracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $document->created_by,
            'from_department_id' => $user->department_id,
            'to_department_id' => optional($document->creator)->department_id,
            'action' => DocumentTracking::ACTION_REJECTED,
            'remarks' => $validated['reason'],
            'sent_at' => now(),
            'is_read' => false,
        ]);

        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'rejected',
            'remarks' => "Document rejected by {$user->full_name}: {$validated['reason']}",
            'metadata' => [
                'tracking_id' => $rejectionTracking->id,
            ],
        ]);

        // Notify creator
        $notification = Notification::create([
            'user_id' => $document->created_by,
            'type' => 'document_rejected',
            'title' => 'Document Rejected',
            'message' => "{$user->full_name} rejected your document: {$document->title}. Reason: {$validated['reason']}",
            'link' => route('documents.show', $document->id),
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'rejected_by' => $user->id,
                'reason' => $validated['reason'],
            ],
            'read' => false,
        ]);

        broadcast(new NotificationCreated($notification, $document->created_by));

        // Send email notification to creator
        $creator = User::find($document->created_by);
        if ($creator) {
            $emailService->sendDocumentRejected($document, $creator, $user, $validated['reason']);
        }

        // Log document rejection
        $auditLog->logDocumentRejected($document, $validated['reason']);

        // Broadcast document rejected event
        broadcast(new DocumentRejected($document));
        broadcast(new DocumentUpdated($document));

        return response()->json([
            'success' => true,
            'message' => 'Document rejected successfully',
        ]);
    }
}
