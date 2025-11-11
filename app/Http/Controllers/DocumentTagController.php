<?php

namespace App\Http\Controllers;

use App\Events\DocumentTagsUpdated;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTagController extends Controller
{
    /**
     * Get all available tags
     */
    public function index()
    {
        // Get all unique tags from documents
        $tags = Document::whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'tags' => $tags,
        ]);
    }

    /**
     * Add tags to a document
     */
    public function addTags(Request $request, Document $document)
    {
        $user = Auth::user();

        // Only current holder or admin can add tags
        if (! $user->isAdmin() && $document->current_holder_id !== $user->id && $document->created_by !== $user->id) {
            abort(403, 'You do not have permission to add tags to this document');
        }

        $validated = $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:50',
        ]);

        $currentTags = $document->tags ?? [];
        $newTags = array_unique(array_merge($currentTags, $validated['tags']));
        $addedTags = array_diff($newTags, $currentTags);

        $document->update(['tags' => $newTags]);

        // Broadcast document tags updated event
        if (!empty($addedTags)) {
            broadcast(new DocumentTagsUpdated($document->fresh(), array_values($addedTags), 'added', $user->id));
        }

        return response()->json([
            'success' => true,
            'message' => 'Tags added successfully',
            'tags' => $newTags,
        ]);
    }

    /**
     * Remove a tag from a document
     */
    public function removeTag(Request $request, Document $document)
    {
        $user = Auth::user();

        // Only current holder or admin can remove tags
        if (! $user->isAdmin() && $document->current_holder_id !== $user->id && $document->created_by !== $user->id) {
            abort(403, 'You do not have permission to remove tags from this document');
        }

        $validated = $request->validate([
            'tag' => 'required|string',
        ]);

        $currentTags = $document->tags ?? [];
        $newTags = array_values(array_diff($currentTags, [$validated['tag']]));

        $document->update(['tags' => $newTags]);

        // Broadcast document tags updated event
        broadcast(new DocumentTagsUpdated($document->fresh(), [$validated['tag']], 'removed', $user->id));

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully',
            'tags' => $newTags,
        ]);
    }

    /**
     * Get documents by tag
     */
    public function getByTag(Request $request, string $tag)
    {
        $user = Auth::user();

        $query = Document::whereJsonContains('tags', $tag)
            ->with(['documentType', 'creator', 'currentHolder']);

        // Apply user-based filtering
        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'documents' => $documents,
        ]);
    }

    /**
     * Get tag statistics
     */
    public function statistics()
    {
        $user = Auth::user();

        // Get tag usage counts
        $tagStats = Document::whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(20);

        return response()->json([
            'success' => true,
            'tag_statistics' => $tagStats,
        ]);
    }
}
