<?php

namespace App\Http\Controllers\Api;

use App\Events\CommentCreated;
use App\Events\DocumentApproved;
use App\Events\DocumentCompleted;
use App\Events\DocumentCreated;
use App\Events\DocumentForwarded;
use App\Events\DocumentRejected;
use App\Events\DocumentUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentTracking;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Get documents for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');
        $type = $request->query('type', 'all'); // all, created, inbox, sent

        $query = Document::with(['documentType', 'creator', 'currentHolder', 'originDepartment']);

        // Filter by type
        if ($type === 'created') {
            $query->where('created_by', $user->id);
        } elseif ($type === 'inbox') {
            $query->where('current_holder_id', $user->id)
                ->whereIn('status', [Document::STATUS_ROUTING, Document::STATUS_RECEIVED]);
        } elseif ($type === 'sent') {
            $query->where('created_by', $user->id)
                ->where('status', '!=', Document::STATUS_DRAFT);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        return DocumentResource::collection($documents);
    }

    /**
     * Create a new document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'urgency_level' => 'required|in:low,normal,high,urgent',
            'deadline' => 'nullable|date|after:now',
        ]);

        $document = Document::create([
            'tracking_number' => Document::generateTrackingNumber(),
            'document_type_id' => $request->document_type_id,
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $request->user()->id,
            'current_holder_id' => $request->user()->id,
            'origin_department_id' => $request->user()->department_id,
            'status' => Document::STATUS_DRAFT,
            'approval_status' => 'pending',
            'urgency_level' => $request->urgency_level,
            'deadline' => $request->deadline,
            'is_overdue' => false,
        ]);

        // Broadcast document created event
        broadcast(new DocumentCreated($document->load(['documentType', 'creator', 'currentHolder', 'originDepartment'])));

        return new DocumentResource($document->load(['documentType', 'creator', 'currentHolder', 'originDepartment']));
    }

    /**
     * Get a specific document.
     */
    public function show(Document $document)
    {
        $document->load(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tracking', 'comments']);

        return new DocumentResource($document);
    }

    /**
     * Update a document.
     */
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'urgency_level' => 'sometimes|in:low,normal,high,urgent',
            'deadline' => 'nullable|date',
        ]);

        $document->update($request->only([
            'title',
            'description',
            'urgency_level',
            'deadline',
        ]));

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh(['documentType', 'creator', 'currentHolder', 'originDepartment'])));

        return new DocumentResource($document->load(['documentType', 'creator', 'currentHolder', 'originDepartment']));
    }

    /**
     * Delete a document.
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Forward document to another user.
     */
    public function forward(Request $request, Document $document)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $toUser = User::findOrFail($request->to_user_id);

        $tracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $request->user()->id,
            'to_user_id' => $toUser->id,
            'from_department_id' => $request->user()->department_id,
            'to_department_id' => $toUser->department_id,
            'action' => 'forwarded',
            'remarks' => $request->remarks,
            'instructions' => $request->instructions,
            'sent_at' => now(),
        ]);

        $document->update([
            'current_holder_id' => $toUser->id,
            'status' => Document::STATUS_ROUTING,
        ]);

        // Broadcast document forwarded event
        broadcast(new DocumentForwarded($document->fresh(), $tracking));

        return response()->json(['message' => 'Document forwarded successfully']);
    }

    /**
     * Approve document.
     */
    public function approve(Request $request, Document $document)
    {
        $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $document->update([
            'status' => Document::STATUS_APPROVED,
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_remarks' => $request->remarks,
        ]);

        // Broadcast document approved event
        broadcast(new DocumentApproved($document->fresh()));
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json(['message' => 'Document approved successfully']);
    }

    /**
     * Reject document.
     */
    public function reject(Request $request, Document $document)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $document->update([
            'status' => Document::STATUS_REJECTED,
            'approval_status' => 'rejected',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        // Broadcast document rejected event
        broadcast(new DocumentRejected($document->fresh()));
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json(['message' => 'Document rejected']);
    }

    /**
     * Mark document as complete.
     */
    public function complete(Document $document)
    {
        $document->update([
            'status' => Document::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Broadcast document completed event
        broadcast(new DocumentCompleted($document->fresh()));
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json(['message' => 'Document marked as complete']);
    }

    /**
     * Get document comments.
     */
    public function comments(Document $document)
    {
        $comments = $document->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Add comment to document.
     */
    public function addComment(Request $request, Document $document)
    {
        $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $comment = DocumentComment::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
            'is_internal' => $request->is_internal ?? false,
        ]);

        // Broadcast comment created event
        broadcast(new CommentCreated($comment->load('user')));

        return response()->json($comment->load('user'), 201);
    }
}
