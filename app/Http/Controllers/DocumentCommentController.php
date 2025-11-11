<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Events\NotificationCreated;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentCommentController extends Controller
{
    /**
     * Store a new comment.
     */
    public function store(Request $request, Document $document)
    {
        // Check if user can view the document
        $this->authorize('view', $document);

        $validated = $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:document_comments,id',
            'is_internal' => 'boolean',
        ]);

        $user = Auth::user();

        // Only admin and involved users can make internal comments
        if (($validated['is_internal'] ?? false) && ! $user->hasAdminPrivileges()) {
            return response()->json(['error' => 'Unauthorized to create internal comments'], 403);
        }

        $comment = DocumentComment::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'comment' => $validated['comment'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        $comment->load('user');

        try {
            // Broadcast comment created event
            \Log::info('Broadcasting CommentCreated event', [
                'comment_id' => $comment->id,
                'document_id' => $document->id,
                'user_id' => $user->id,
            ]);
            
            $broadcastResult = broadcast(new CommentCreated($comment));
            
            \Log::info('CommentCreated broadcast result', [
                'comment_id' => $comment->id,
                'broadcasted' => $broadcastResult !== null,
            ]);

            // Create notifications for involved users
            $this->notifyInvolvedUsers($document, $comment, $user);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast comment or send notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'comment_id' => $comment->id,
            ]);
            // Continue - comment was created successfully
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => [
                'id' => $comment->id,
                'document_id' => $comment->document_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'is_internal' => $comment->is_internal,
                'created_at' => $comment->created_at->toISOString(),
                'user' => [
                    'id' => $comment->user->id,
                    'first_name' => $comment->user->first_name,
                    'last_name' => $comment->user->last_name,
                    'full_name' => $comment->user->full_name,
                    'avatar' => $comment->user->avatar,
                    'avatar_url' => $comment->user->avatar_url,
                ],
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(DocumentComment $comment)
    {
        $user = Auth::user();

        // Only the comment owner or admin can delete
        if ($comment->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $commentId = $comment->id;
        $documentId = $comment->document_id;

        $comment->delete();

        // Broadcast comment deleted event
        broadcast(new \App\Events\CommentDeleted($commentId, $documentId));

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Notify involved users about new comment.
     */
    protected function notifyInvolvedUsers(Document $document, DocumentComment $comment, User $commenter)
    {
        // Get all users involved in the document
        $userIds = collect();

        // Document creator
        if ($document->created_by && $document->created_by !== $commenter->id) {
            $userIds->push($document->created_by);
        }

        // Current holder
        if ($document->current_holder_id && $document->current_holder_id !== $commenter->id) {
            $userIds->push($document->current_holder_id);
        }

        // Users in tracking history (optimized single query)
        $trackingUsers = $document->tracking()
            ->select('from_user_id', 'to_user_id')
            ->get()
            ->flatMap(function ($tracking) {
                return [$tracking->from_user_id, $tracking->to_user_id];
            })
            ->filter(function ($id) use ($commenter) {
                return $id && $id !== $commenter->id;
            });

        $userIds = $userIds->merge($trackingUsers)->unique();

        // Create notifications
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            // Skip if internal comment and user is not admin
            if ($comment->is_internal && !$user->isAdmin()) {
                continue;
            }

            try {
                $notification = Notification::create([
                    'user_id' => $userId,
                    'type' => 'comment_added',
                    'title' => 'New Comment on Document',
                    'message' => "{$commenter->full_name} commented on \"{$document->title}\"",
                    'link' => route('documents.show', $document->id),
                    'data' => [
                        'document_id' => $document->id,
                        'comment_id' => $comment->id,
                        'commenter_id' => $commenter->id,
                        'tracking_number' => $document->tracking_number,
                    ],
                    'read' => false,
                ]);

                // Broadcast notification
                broadcast(new NotificationCreated($notification, $userId));
            } catch (\Exception $e) {
                \Log::error('Failed to create comment notification', ['error' => $e->getMessage()]);
            }
        }
    }
}
