<script>
// Realtime for tags (private channel 'tags')
(function initializeTagsBroadcasting() {
	if (!window.Echo || window.Echo._isDummy) {
		setTimeout(initializeTagsBroadcasting, 100);
		return;
	}
	try {
		window.Echo.private('tags')
			.listen('.tag.updated', () => {
				if (typeof window.refreshTagsTable === 'function') window.refreshTagsTable();
				if (typeof window.refreshTagSelectors === 'function') window.refreshTagSelectors();
			});
	} catch (e) {
		console.error('Tags broadcasting init failed:', e);
	}
})();
</script>
<!-- Action Button -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div></div>
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <!-- Broadcast Status -->
        <div id="broadcast-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 w-full sm:w-auto justify-center sm:justify-start">
            <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" id="broadcast-indicator"></span>
            <span id="broadcast-text">Connecting...</span>
        </div>
        
        <button type="button" onclick="openTagCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Create Tag</span>
            <span class="sm:hidden">Create</span>
        </button>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
    <form method="GET" action="{{ route('admin.settings.index') }}" id="tag-filter-form" class="w-full">
        <input type="hidden" name="tab" value="tags">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:flex lg:flex-row gap-3 sm:gap-4 w-full">
            <!-- Search -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="tag_search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Search
                </label>
                <input 
                    type="text" 
                    id="tag_search" 
                    name="tag_search" 
                    value="{{ request('tag_search') }}"
                    placeholder="Search tags..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    oninput="autoSubmitTagForm()"
                >
            </div>

            <!-- Per Page -->
            <div class="lg:w-auto lg:flex-shrink-0">
                <label for="tag_per_page" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Per Page
                </label>
                <select 
                    id="tag_per_page" 
                    name="tag_per_page" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('tag-filter-form').submit();"
                >
                    <option value="10" {{ request('tag_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('tag_per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('tag_per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('tag_per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Clear Button -->
            <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                <a href="{{ route('admin.settings.index', ['tab' => 'tags']) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                    <span class="hidden sm:inline">Clear</span>
                    <span class="sm:hidden">Clear Filters</span>
                </a>
            </div>
        </div>

        <!-- Results Count -->
        @if(request()->hasAny(['tag_search']))
            <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                {{ $tags->total() }} {{ Str::plural('result', $tags->total()) }} found
            </div>
        @endif
    </form>
</div>

<!-- Table -->
<div id="tags-table" class="border border-gray-200 dark:border-gray-700 rounded-lg sm:rounded-xl overflow-hidden bg-white dark:bg-gray-800">
    <!-- Mobile Card View -->
    <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($tags as $tag)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ $tag->name }}</div>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>{{ $tag->usage_count }} documents</span>
                        </div>
                        <div>
                            @if($tag->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button onclick="editTag({{ $tag->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                        <x-icon name="pencil" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="deleteTag({{ $tag->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                        <x-icon name="trash" type="solid" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-icon name="tag" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No tags found. Create your first tag!</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usage</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tags as $tag)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $tag->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden mt-1">{{ $tag->usage_count }} documents</div>
                    </td>
                    <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900 dark:text-white">{{ $tag->usage_count }} documents</span>
                    </td>
                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                        @if($tag->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editTag({{ $tag->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                            </button>
                            <button onclick="deleteTag({{ $tag->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No tags found. Create your first tag!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tags->hasPages())
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                {{ $tags->links('vendor.pagination.minimal') }}
            </div>
        </div>
    @endif
</div>

<!-- Create/Edit Tag Modal -->
<div id="tagModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeTagModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white" id="tagModalTitle">Create Tag</h3>
            <button type="button" onclick="closeTagModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="tagForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <input type="hidden" id="tag_id" name="tag_id">
            <div>
                <label for="tag_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tag Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="tag_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="tag_description" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Description
                </label>
                <textarea id="tag_description" name="description" rows="3" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeTagModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary" id="tagSubmitBtn">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
// Use global updateBroadcastStatus if available, otherwise create a local fallback
if (typeof window.updateBroadcastStatus !== 'function') {
    window.updateBroadcastStatus = function(status, message) {
        const indicators = document.querySelectorAll('#broadcast-indicator');
        const texts = document.querySelectorAll('#broadcast-text');
        
        indicators.forEach(indicator => {
            if (!indicator) return;
            if (status === 'connected') {
                indicator.className = 'w-2 h-2 rounded-full bg-green-500';
            } else if (status === 'connecting') {
                indicator.className = 'w-2 h-2 rounded-full bg-yellow-500 animate-pulse';
            } else if (status === 'disconnected') {
                indicator.className = 'w-2 h-2 rounded-full bg-red-500';
            } else {
                indicator.className = 'w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse';
            }
        });
        
        texts.forEach(text => {
            if (text) {
                text.textContent = message;
            }
        });
    };
}

// Auto submit tag form with debounce
let tagSearchTimeout;
function autoSubmitTagForm() {
    clearTimeout(tagSearchTimeout);
    tagSearchTimeout = setTimeout(() => {
        document.getElementById('tag-filter-form').submit();
    }, 500);
}

// Initialize broadcast status - will be updated by realtime-listeners.js when Echo connects
// Set initial state
if (typeof window.updateBroadcastStatus === 'function') {
    window.updateBroadcastStatus('connecting', 'Connecting...');
}

function openTagCreateModal() {
    document.getElementById('tagModalTitle').textContent = 'Create Tag';
    document.getElementById('tagSubmitBtn').textContent = 'Create';
    document.getElementById('tagForm').reset();
    document.getElementById('tag_id').value = '';
    document.getElementById('tagModal').classList.remove('hidden');
    document.getElementById('tagModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

async function editTag(id) {
    try {
        const response = await fetch(`{{ route('admin.tags.show', ':id') }}`.replace(':id', id));
        if (!response.ok) {
            throw new Error('Failed to fetch tag');
        }
        const data = await response.json();
        
        document.getElementById('tagModalTitle').textContent = 'Edit Tag';
        document.getElementById('tagSubmitBtn').textContent = 'Update';
        document.getElementById('tag_id').value = data.id;
        document.getElementById('tag_name').value = data.name;
        document.getElementById('tag_description').value = data.description || '';
        document.getElementById('tagModal').classList.remove('hidden');
        document.getElementById('tagModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error fetching tag:', error);
        alert('An error occurred while fetching tag details');
    }
}

function closeTagModal() {
    document.getElementById('tagModal').classList.add('hidden');
    document.getElementById('tagModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('tagForm').reset();
}

function deleteTag(id) {
    window.showDeleteModal(
        'Delete Tag',
        'Are you sure you want to delete this tag? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`{{ route('admin.tags.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', data.message || 'Tag deleted successfully!');
                    }
					// Refresh table without reloading the page
					if (typeof window.refreshTagsTable === 'function') {
						await window.refreshTagsTable();
					}
                } else {
                    alert(data.message || 'An error occurred while deleting the tag');
                }
            } catch (error) {
                console.error('Error deleting tag:', error);
                alert('An error occurred while deleting the tag');
            }
        }
    );
}

document.getElementById('tagForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('tag_id').value;
    const url = id 
        ? `{{ route('admin.tags.update', ':id') }}`.replace(':id', id)
        : '{{ route('admin.tags.store') }}';
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: document.getElementById('tag_name').value,
                description: document.getElementById('tag_description').value
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            closeTagModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || (id ? 'Tag updated successfully!' : 'Tag created successfully!'));
            }
			// Refresh table without reloading the page
			if (typeof window.refreshTagsTable === 'function') {
				await window.refreshTagsTable();
			}
        } else {
            // Handle validation errors
            const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'An error occurred');
            alert(errorMessage);
        }
    } catch (error) {
        console.error('Error saving tag:', error);
        alert('An error occurred while saving the tag');
    }
});
</script>
