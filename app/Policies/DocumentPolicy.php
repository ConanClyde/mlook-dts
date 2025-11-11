<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the documents list (filtered by their access)
        return true;
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // Admin can view all documents
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can ALWAYS view their own documents
        if ($document->created_by === $user->id) {
            return true;
        }

        // Current holder can ALWAYS view
        if ($document->current_holder_id === $user->id) {
            return true;
        }

        // Users involved in the document tracking can ALWAYS view
        if ($this->isInvolvedInDocument($user, $document)) {
            return true;
        }

        // Load document type relationship
        $document->loadMissing('documentType');

        // Check if user can access this document type based on their role
        if ($document->documentType && $document->documentType->canBeAccessedBy($user)) {
            return true;
        }

        // Deny access if none of the above conditions are met
        return false;
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        return $user->canCreateDocuments();
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        // Admin can update all documents
        if ($user->isAdmin()) {
            return true;
        }

        // Only creator can update their own documents
        // And only if not yet completed or archived
        return $document->created_by === $user->id
            && ! in_array($document->status, [
                Document::STATUS_COMPLETED,
                Document::STATUS_ARCHIVED,
                Document::STATUS_APPROVED,
                Document::STATUS_REJECTED,
            ]);
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Admin can delete all documents
        if ($user->isAdmin()) {
            return true;
        }

        // Only creator can delete their own documents
        // And only if still pending (not yet forwarded)
        return $document->created_by === $user->id
            && $document->status === Document::STATUS_DRAFT;
    }

    /**
     * Determine if the user can forward the document.
     */
    public function forward(User $user, Document $document): bool
    {
        // Don't allow forwarding if document is completed or archived
        if (in_array($document->status, [
            Document::STATUS_COMPLETED,
            Document::STATUS_ARCHIVED,
        ])) {
            return false;
        }

        // Admin can forward any document
        if ($user->isAdmin()) {
            return true;
        }

        // Students can only send their own documents (if they created it and are the current holder)
        if ($user->isStudent()) {
            return $document->created_by === $user->id
                && $document->current_holder_id === $user->id;
        }

        // Only current holder can forward
        return $document->current_holder_id === $user->id;
    }

    /**
     * Determine if the user can receive documents.
     */
    public function receive(User $user): bool
    {
        // Students cannot receive documents
        return ! $user->isStudent();
    }

    /**
     * Determine if the user can complete the document.
     */
    public function complete(User $user, Document $document): bool
    {
        // Cannot complete your own documents
        if ($document->created_by === $user->id) {
            return false;
        }

        // Admin can complete any document (except their own)
        if ($user->isAdmin()) {
            return true;
        }

        // Only current holder can complete (and not if already completed/archived)
        return $document->current_holder_id === $user->id
            && ! in_array($document->status, [
                Document::STATUS_COMPLETED,
                Document::STATUS_ARCHIVED,
            ]);
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        // Same rules as viewing
        return $this->view($user, $document);
    }

    /**
     * Determine if the user can approve the document.
     */
    public function approve(User $user, Document $document): bool
    {
        // Cannot approve your own documents
        if ($document->created_by === $user->id) {
            return false;
        }

        // Students and regular staff cannot approve
        if ($user->isStudent() || $user->isStaff()) {
            return false;
        }

        // Admin can approve any document (except their own)
        if ($user->isAdmin()) {
            return true;
        }

        // Administrative users (Registrar, Dean, Dept Head) can approve if they're the current holder
        return $user->hasAdminPrivileges()
            && $document->current_holder_id === $user->id
            && $document->status === Document::STATUS_FOR_APPROVAL;
    }

    /**
     * Determine if the user can reject the document.
     */
    public function reject(User $user, Document $document): bool
    {
        // Same rules as approve
        return $this->approve($user, $document);
    }

    /**
     * Determine if the user can manually change document status.
     */
    public function changeStatus(User $user, Document $document): bool
    {
        // Only admins can manually change status
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view comments on the document.
     */
    public function viewComments(User $user, Document $document): bool
    {
        // Can view comments if involved in document
        return $user->id === $document->created_by 
            || $user->id === $document->current_holder_id
            || $this->isInvolvedInDocument($user, $document)
            || $user->isAdmin();
    }

    /**
     * Determine if the user can add comments to the document.
     */
    public function addComment(User $user, Document $document): bool
    {
        // Same as viewComments - can comment if involved
        return $this->viewComments($user, $document);
    }

    /**
     * Determine if the user can delete a specific comment.
     * Note: This should be called with the comment's user_id, not the comment object.
     */
    public function deleteComment(User $user, int $commentUserId): bool
    {
        // Can delete own comments or admin can delete any
        return $user->id === $commentUserId || $user->isAdmin();
    }

    /**
     * Check if user is involved in the document.
     */
    protected function isInvolvedInDocument(User $user, Document $document): bool
    {
        return $document->tracking()
            ->where(function ($query) use ($user) {
                $query->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
            ->exists();
    }
}
