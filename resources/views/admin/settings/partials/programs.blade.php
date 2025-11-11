<script>
// Comments-style realtime for programs on admin.settings private channel
function initializeProgramsBroadcasting() {
	if (!window.Echo || window.Echo._isDummy) {
		setTimeout(initializeProgramsBroadcasting, 100);
		return;
	}
	@if(auth()->user()->isAdmin())
	try {
		window.Echo.private('admin.settings')
			.subscribed(() => {})
			.error(() => {})
			.listen('.program.created', () => { if (typeof window.refreshProgramsTable === 'function') window.refreshProgramsTable(); })
			.listen('.program.updated', () => { if (typeof window.refreshProgramsTable === 'function') window.refreshProgramsTable(); })
			.listen('.program.deleted', () => { if (typeof window.refreshProgramsTable === 'function') window.refreshProgramsTable(); });
	} catch (e) {
		console.error('Programs broadcasting init failed:', e);
	}
	@endif
}
document.addEventListener('DOMContentLoaded', initializeProgramsBroadcasting);
</script>
<!-- Action Button -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div></div>
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <!-- Broadcast Status -->
        <div id="broadcast-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 w-full sm:w-auto justify-center sm:justify-start">
            <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" id="broadcast-indicator"></span>
            <span id="broadcast-text">Connecting...</span>
        </div>
        
        <button type="button" onclick="openProgCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Create Program</span>
            <span class="sm:hidden">Create</span>
        </button>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
    <form method="GET" action="{{ route('admin.settings.index') }}" id="prog-filter-form" class="w-full">
        <input type="hidden" name="tab" value="programs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:flex lg:flex-row gap-3 sm:gap-4 w-full">
            <!-- Search -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="prog_search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Search
                </label>
                <input 
                    type="text" 
                    id="prog_search" 
                    name="prog_search" 
                    value="{{ request('prog_search') }}"
                    placeholder="Name or code..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    oninput="autoSubmitProgForm('prog-filter-form')"
                >
            </div>

            <!-- College Filter -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="prog_college" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    College
                </label>
                <select 
                    id="prog_college" 
                    name="prog_college" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('prog-filter-form').submit();"
                >
                    <option value="">All Colleges</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" {{ request('prog_college') == $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page -->
            <div class="lg:w-auto lg:flex-shrink-0">
                <label for="prog_per_page" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Per Page
                </label>
                <select 
                    id="prog_per_page" 
                    name="prog_per_page" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('prog-filter-form').submit();"
                >
                    <option value="10" {{ request('prog_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('prog_per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('prog_per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('prog_per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Clear Button -->
            <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                <a href="{{ route('admin.settings.index', ['tab' => 'programs']) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                    Clear
                </a>
            </div>
        </div>

        <!-- Results Count -->
        @if(request()->hasAny(['prog_search', 'prog_college']))
            <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                {{ $programs->total() }} {{ Str::plural('result', $programs->total()) }} found
            </div>
        @endif
    </form>
</div>

<!-- Table -->
<div id="programs-table" class="border border-gray-200 dark:border-gray-700 rounded-lg sm:rounded-xl overflow-hidden bg-white dark:bg-gray-800">
    <!-- Mobile Card View -->
    <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($programs as $program)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-2 break-words">{{ $program->name }}</div>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="font-mono">{{ $program->code }}</span>
                            <span>•</span>
                            <span>{{ $program->college ? $program->college->name : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button onclick="viewProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                        <x-icon name="eye" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="editProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                        <x-icon name="pencil" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="deleteProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                        <x-icon name="trash" type="solid" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-icon name="academic-cap" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No programs found.</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="max-width: 200px;">Name</th>
                    <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                    <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">College</th>
                    <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($programs as $program)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white break-words" style="max-width: 200px;">{{ $program->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden mt-1 font-mono">{{ $program->code }}</div>
                        </td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-600 dark:text-gray-300">{{ $program->code }}</div>
                        </td>
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $program->college ? $program->college->name : 'N/A' }}</div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                    <x-icon name="eye" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="editProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                    <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="deleteProg({{ $program->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                    <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 sm:px-6 py-12 text-center">
                            <x-icon name="academic-cap" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">No programs found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($programs->hasPages())
    <div class="mt-6">
        {{ $programs->links('vendor.pagination.minimal') }}
    </div>
@endif

<!-- Modals -->
<div id="progCreateModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeProgCreateModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Create Program</h3>
            <button type="button" onclick="closeProgCreateModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="progCreateForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <label for="create_prog_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="create_prog_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="create_prog_code" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" id="create_prog_code" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="create_prog_college" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    College <span class="text-red-500">*</span>
                </label>
                <select id="create_prog_college" name="college_id" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Select a college</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeProgCreateModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="progViewModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeProgViewModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Program Details</h3>
            <button type="button" onclick="closeProgViewModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="view_prog_name"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-white" id="view_prog_code"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">College</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="view_prog_college"></dd>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeProgViewModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<div id="progEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeProgEditModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit Program</h3>
            <button type="button" onclick="closeProgEditModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="progEditForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_prog_id" name="id">
            <div>
                <label for="edit_prog_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit_prog_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="edit_prog_code" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit_prog_code" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="edit_prog_college" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    College <span class="text-red-500">*</span>
                </label>
                <select id="edit_prog_college" name="college_id" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Select a college</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeProgEditModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// Create Program
document.getElementById('progCreateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('{{ route("admin.programs.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (response.ok) {
            closeProgCreateModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Program created successfully!');
            }
            // Refresh table without reload
            await window.refreshProgramsTable();
        } else {
            const errorMessage = data.message || data.error || (data.errors ? Object.values(data.errors).flat().join(', ') : 'An error occurred');
            alert(errorMessage);
        }
    } catch (error) {
        alert('An error occurred while creating the program');
        console.error(error);
    }
});

// View Program
async function viewProg(id) {
    try {
        const response = await fetch(`{{ route('admin.programs.show', ':id') }}`.replace(':id', id));
        if (!response.ok) {
            throw new Error('Failed to fetch program');
        }
        const data = await response.json();
        
        const prog = data.program || data;
        document.getElementById('view_prog_name').textContent = prog.name;
        document.getElementById('view_prog_code').textContent = prog.code;
        document.getElementById('view_prog_college').textContent = prog.college ? prog.college.name : 'N/A';
        
        document.getElementById('progViewModal').classList.remove('hidden');
        document.getElementById('progViewModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error fetching program:', error);
        alert('An error occurred while fetching program details');
    }
}

// Edit Program
async function editProg(id) {
    try {
        const response = await fetch(`{{ route('admin.programs.show', ':id') }}`.replace(':id', id));
        if (!response.ok) {
            throw new Error('Failed to fetch program');
        }
        const data = await response.json();
        
        const prog = data.program || data;
        document.getElementById('edit_prog_id').value = prog.id;
        document.getElementById('edit_prog_name').value = prog.name;
        document.getElementById('edit_prog_code').value = prog.code;
        document.getElementById('edit_prog_college').value = prog.college_id;
        
        document.getElementById('progEditModal').classList.remove('hidden');
        document.getElementById('progEditModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error fetching program:', error);
        alert('An error occurred while fetching program details');
    }
}

document.getElementById('progEditForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('edit_prog_id').value;
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(`{{ route('admin.programs.update', ':id') }}`.replace(':id', id), {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (response.ok) {
            closeProgEditModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Program updated successfully!');
            }
            // Refresh table without reload
            await window.refreshProgramsTable();
        } else {
            const errorMessage = data.message || data.error || (data.errors ? Object.values(data.errors).flat().join(', ') : 'An error occurred');
            alert(errorMessage);
        }
    } catch (error) {
        console.error('Error updating program:', error);
        alert('An error occurred while updating the program');
    }
});

// Delete Program
function deleteProg(id) {
    window.showDeleteModal(
        'Delete Program',
        'Are you sure you want to delete this program? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`{{ route('admin.programs.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', data.message || 'Program deleted successfully!');
                    }
                    // Refresh table without reload
                    await window.refreshProgramsTable();
                } else {
                    const errorMessage = data.message || data.error || 'An error occurred';
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('Error deleting program:', error);
                alert('An error occurred while deleting the program');
            }
        }
    );
}

// Modal Controls
function openProgCreateModal() {
    document.getElementById('progCreateModal').classList.remove('hidden');
    document.getElementById('progCreateModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeProgCreateModal() {
    document.getElementById('progCreateModal').classList.add('hidden');
    document.getElementById('progCreateModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('progCreateForm').reset();
}

function closeProgViewModal() {
    document.getElementById('progViewModal').classList.add('hidden');
    document.getElementById('progViewModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function closeProgEditModal() {
    document.getElementById('progEditModal').classList.add('hidden');
    document.getElementById('progEditModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('progEditForm').reset();
}

// Close on overlay click
document.getElementById('progCreateModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeProgCreateModal();
});
document.getElementById('progViewModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeProgViewModal();
});
document.getElementById('progEditModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeProgEditModal();
});

// Auto submit form with debounce
let progSearchTimeout;
window.autoSubmitProgForm = function(formId) {
    clearTimeout(progSearchTimeout);
    progSearchTimeout = setTimeout(() => {
        document.getElementById(formId).submit();
    }, 500);
};

// Update broadcast status indicator
function updateBroadcastStatus(status, message) {
    const indicator = document.getElementById('broadcast-indicator');
    const text = document.getElementById('broadcast-text');
    const container = document.getElementById('broadcast-status');
    
    if (!indicator || !text || !container) return;
    
    indicator.classList.remove('bg-gray-400', 'bg-yellow-400', 'bg-green-500', 'bg-red-500', 'animate-pulse');
    container.classList.remove('bg-gray-100', 'bg-yellow-50', 'bg-green-50', 'bg-red-50', 
                                'dark:bg-gray-700', 'dark:bg-yellow-900/20', 'dark:bg-green-900/20', 'dark:bg-red-900/20',
                                'text-gray-600', 'text-yellow-600', 'text-green-600', 'text-red-600',
                                'dark:text-gray-400', 'dark:text-yellow-400', 'dark:text-green-400', 'dark:text-red-400');
    
    if (status === 'connecting') {
        indicator.classList.add('bg-yellow-400', 'animate-pulse');
        container.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20', 'text-yellow-600', 'dark:text-yellow-400');
    } else if (status === 'connected') {
        indicator.classList.add('bg-green-500');
        container.classList.add('bg-green-50', 'dark:bg-green-900/20', 'text-green-600', 'dark:text-green-400');
    } else if (status === 'error') {
        indicator.classList.add('bg-red-500', 'animate-pulse');
        container.classList.add('bg-red-50', 'dark:bg-red-900/20', 'text-red-600', 'dark:text-red-400');
    } else {
        indicator.classList.add('bg-gray-400');
        container.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
    }
    
    text.textContent = message;
}

// Real-time broadcasting listeners
function setupProgramsBroadcastingListeners() {
    if (!window.Echo) {
        updateBroadcastStatus('connecting', 'Connecting...');
        setTimeout(setupProgramsBroadcastingListeners, 100);
        return;
    }
    
    updateBroadcastStatus('connecting', 'Setting up...');
    
    @if(auth()->user()->isAdmin())
    window.Echo.private('admin.settings')
        .listen('.program.created', (e) => {
            refreshProgramsTable();
        })
        .listen('.program.updated', (e) => {
            refreshProgramsTable();
        })
        .listen('.program.deleted', (e) => {
            refreshProgramsTable();
        })
        .listen('.document-type.created', (e) => {
            refreshProgramsTable();
        })
        .listen('.document-type.updated', (e) => {
            refreshProgramsTable();
        })
        .listen('.document-type.deleted', (e) => {
            refreshProgramsTable();
        })
        .listen('.department.created', (e) => {
            refreshProgramsTable();
        })
        .listen('.department.updated', (e) => {
            refreshProgramsTable();
        })
        .listen('.department.deleted', (e) => {
            refreshProgramsTable();
        });
    
    updateBroadcastStatus('connected', 'Live Updates');
    @endif
}

// Refresh programs table without reload
async function refreshProgramsTable() {
    try {
        const params = new URLSearchParams(window.location.search);
        params.set('tab', 'programs');
        const response = await fetch('{{ route("admin.settings.index") }}?' + params.toString());
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the content card inside flex-1 div
        const newContentCard = doc.querySelector('.flex-1 > .bg-white.dark\\:bg-gray-800');
        if (newContentCard) {
            const currentContentCard = document.querySelector('.flex-1 > .bg-white.dark\\:bg-gray-800');
            if (currentContentCard && currentContentCard.parentElement) {
                const parentEl = currentContentCard.parentElement; // Store reference to parent
                // Add fade animation
                currentContentCard.style.transition = 'opacity 0.3s';
                currentContentCard.style.opacity = '0.5';
                
                setTimeout(() => {
                    if (parentEl && currentContentCard.parentElement === parentEl) {
                        const newContentClone = document.importNode(newContentCard, true);
                        parentEl.replaceChild(newContentClone, currentContentCard);
                        
                        // Re-initialize listeners
                        setupProgramsBroadcastingListeners();
                        
                        // Restore opacity
                        setTimeout(() => {
                            const restoredContent = document.querySelector('.flex-1 > .bg-white.dark\\:bg-gray-800');
                            if (restoredContent) {
                                restoredContent.style.opacity = '1';
                            }
                        }, 50);
                    }
                }, 300);
            }
        }
    } catch (error) {
        console.error('Error refreshing programs table:', error);
        try { await window.refreshProgramsTable(); } catch (_) {}
    }
}

// Initialize listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    setupProgramsBroadcastingListeners();
});
</script>
