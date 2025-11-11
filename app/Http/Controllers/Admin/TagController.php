<?php

namespace App\Http\Controllers\Admin;

use App\Events\TagUpdated;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Display tags management page
     */
    public function index(Request $request)
    {
        $tags = Tag::with('creator')->orderBy('name')->get();

        // Return JSON if requested via AJAX
        if ($this->expectsJson($request)) {
            return response()->json([
                'success' => true,
                'tags' => $tags->map(function($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'usage_count' => $tag->usage_count ?? 0,
                    ];
                }),
            ]);
        }

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show a single tag
     */
    public function show(Tag $tag)
    {
        return response()->json([
            'id' => $tag->id,
            'name' => $tag->name,
            'description' => $tag->description,
            'is_active' => $tag->is_active,
            'usage_count' => $tag->usage_count,
        ]);
    }

    /**
     * Store a new tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = Auth::id();

        $tag = Tag::create($validated);

        // Broadcast the event
        broadcast(new TagUpdated($tag, 'created'));

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag' => $tag->load('creator'),
        ]);
    }

    /**
     * Update a tag
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,'.$tag->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $tag->update($validated);

        // Broadcast the event
        broadcast(new TagUpdated($tag, 'updated'));

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'tag' => $tag->load('creator'),
        ]);
    }

    /**
     * Delete a tag
     */
    public function destroy(Tag $tag)
    {
        // Check if tag is in use
        if ($tag->usage_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete tag '{$tag->name}' because it is currently used by {$tag->usage_count} document(s). Please remove it from all documents first.",
            ], 422);
        }

        // Broadcast the event before deletion (tag model is still available)
        broadcast(new TagUpdated($tag, 'deleted'));

        // Delete the tag after broadcasting
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
        ]);
    }

    /**
     * Toggle tag active status
     */
    public function toggleTag(Tag $tag)
    {
        $tag->update(['is_active' => ! $tag->is_active]);

        // Broadcast the event
        broadcast(new TagUpdated($tag, 'toggled'));

        return response()->json([
            'success' => true,
            'message' => $tag->is_active ? 'Tag activated' : 'Tag deactivated',
            'tag' => $tag,
        ]);
    }

    /**
     * Get tag statistics
     */
    public function statistics()
    {
        $tagStats = [
            'total_tags' => Tag::count(),
            'active_tags' => Tag::where('is_active', true)->count(),
            'most_used_tags' => Tag::orderBy('usage_count', 'desc')->limit(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $tagStats,
        ]);
    }
}
