@props(['document'])

@php
    // WORKAROUND: Eager loading is broken, use lazy loading instead
    // Load tags using the query builder directly to bypass eager loading issue
    $documentTags = [];
    $tagsCollection = $document->tags()->get();
    
    if ($tagsCollection && $tagsCollection->count() > 0) {
        $documentTags = $tagsCollection->map(function($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name
            ];
        })->toArray();
    }
    
    // Debug: Log the tags being passed
    \Log::info('Document Tags Component - Document ID: ' . $document->id . ', Tags count: ' . count($documentTags), ['tags' => $documentTags]);
@endphp

<div x-data="documentTags({{ $document->id }}, @js($documentTags), {{ auth()->id() }})" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
    <div class="mb-4">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Tags</h3>
    </div>

    <!-- Tags Display (Read-only) -->
    <div class="flex flex-wrap gap-2">
        <div x-show="tags.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-2">
            No tags added yet
        </div>

        <template x-for="tag in tags" :key="tag.id">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-gray-800 dark:bg-gray-700 text-white shadow-sm">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
                <span x-text="tag.name"></span>
            </span>
        </template>
    </div>
</div>

<script>
function documentTags(documentId, initialTags, currentUserId) {
    return {
        documentId: documentId,
        tags: initialTags || [],
        currentUserId: currentUserId || null,
        
        init() {
            this.setupBroadcasting();
        },
        
        setupBroadcasting() {
            // Listen for real-time tag updates on this document
            if (window.Echo) {
                window.Echo.private(`document.${this.documentId}`)
                    .listen('.document.tags.updated', (e) => {
                        // Only process if tags are provided and it's an array
                        if (e.tags && Array.isArray(e.tags)) {
                            // Update tags array with ALL tags (not just new ones)
                            this.tags = e.tags;
                        }
                    });
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
