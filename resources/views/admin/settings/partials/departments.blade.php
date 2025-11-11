<!-- Action Button -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div></div>
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <!-- Broadcast Status -->
        <div id="broadcast-status" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 w-full sm:w-auto justify-center sm:justify-start">
            <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-pulse" id="broadcast-indicator"></span>
            <span id="broadcast-text">Connecting...</span>
        </div>
        
        <button type="button" onclick="openDeptCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Create Department/College</span>
            <span class="sm:hidden">Create</span>
        </button>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
    <form method="GET" action="{{ route('admin.settings.index') }}" id="dept-filter-form" class="w-full">
        <input type="hidden" name="tab" value="departments">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:flex lg:flex-row gap-3 sm:gap-4 w-full">
            <!-- Search -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="dept_search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Search
                </label>
                <input 
                    type="text" 
                    id="dept_search" 
                    name="dept_search" 
                    value="{{ request('dept_search') }}"
                    placeholder="Name or code..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    oninput="autoSubmitDeptForm('dept-filter-form')"
                >
            </div>

            <!-- Type Filter -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="dept_type" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Type
                </label>
                <select 
                    id="dept_type" 
                    name="dept_type" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('dept-filter-form').submit();"
                >
                    <option value="">All Types</option>
                    <option value="college" {{ request('dept_type') === 'college' ? 'selected' : '' }}>College</option>
                    <option value="department" {{ request('dept_type') === 'department' ? 'selected' : '' }}>Department</option>
                </select>
            </div>

            <!-- Per Page -->
            <div class="lg:w-auto lg:flex-shrink-0">
                <label for="dept_per_page" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Per Page
                </label>
                <select 
                    id="dept_per_page" 
                    name="dept_per_page" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('dept-filter-form').submit();"
                >
                    <option value="10" {{ request('dept_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('dept_per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('dept_per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('dept_per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Clear Button -->
            <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                <a href="{{ route('admin.settings.index', ['tab' => 'departments']) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                    Clear
                </a>
            </div>
        </div>

        <!-- Results Count -->
        @if(request()->hasAny(['dept_search', 'dept_type']))
            <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                {{ $departments->total() }} {{ Str::plural('result', $departments->total()) }} found
            </div>
        @endif
    </form>
</div>

<!-- Table -->
<div id="departments-table" class="border border-gray-200 dark:border-gray-700 rounded-lg sm:rounded-xl overflow-hidden bg-white dark:bg-gray-800">
    <!-- Mobile Card View -->
    <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($departments as $dept)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-2 break-words">{{ $dept->name }}</div>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="font-mono">{{ $dept->code }}</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dept->type === 'college' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' }}">
                                {{ ucfirst($dept->type) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button onclick="viewDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                        <x-icon name="eye" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="editDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                        <x-icon name="pencil" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="deleteDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                        <x-icon name="trash" type="solid" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-icon name="building-office" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No departments found.</p>
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
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($departments as $dept)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white break-words" style="max-width: 200px;">{{ $dept->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden mt-1 font-mono">{{ $dept->code }}</div>
                        </td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-600 dark:text-gray-300">{{ $dept->code }}</div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dept->type === 'college' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' }}">
                                {{ ucfirst($dept->type) }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                    <x-icon name="eye" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="editDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                    <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="deleteDept({{ $dept->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                    <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 sm:px-6 py-12 text-center">
                            <x-icon name="building-office" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">No departments found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($departments->hasPages())
    <div class="mt-6">
        {{ $departments->links('vendor.pagination.minimal') }}
    </div>
@endif

<!-- Modals -->
<div id="deptCreateModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDeptCreateModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Create Department/College</h3>
            <button type="button" onclick="closeDeptCreateModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="deptCreateForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <label for="create_dept_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="create_dept_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="create_dept_code" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" id="create_dept_code" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="create_dept_type" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Type <span class="text-red-500">*</span>
                </label>
                <select id="create_dept_type" name="type" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="department">Department</option>
                    <option value="college">College</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeDeptCreateModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="deptViewModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDeptViewModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Department/College Details</h3>
            <button type="button" onclick="closeDeptViewModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="view_dept_name"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-white" id="view_dept_code"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="view_dept_type"></dd>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeDeptViewModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<div id="deptEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDeptEditModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit Department/College</h3>
            <button type="button" onclick="closeDeptEditModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="deptEditForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_dept_id" name="id">
            <div>
                <label for="edit_dept_name" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit_dept_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="edit_dept_code" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit_dept_code" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label for="edit_dept_type" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Type <span class="text-red-500">*</span>
                </label>
                <select id="edit_dept_type" name="type" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="department">Department</option>
                    <option value="college">College</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeDeptEditModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// Create Department
document.getElementById('deptCreateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('{{ route("admin.departments.store") }}', {
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
            closeDeptCreateModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Department/College created successfully!');
            }
            // Refresh table without reload
            await window.refreshDepartmentsTable();
        } else {
            const errorMessage = data.message || data.error || (data.errors ? Object.values(data.errors).flat().join(', ') : 'An error occurred');
            alert(errorMessage);
        }
    } catch (error) {
        alert('An error occurred while creating the department');
        console.error(error);
    }
});

// View Department
async function viewDept(id) {
    try {
        const response = await fetch(`{{ route('admin.departments.show', ':id') }}`.replace(':id', id));
        if (!response.ok) {
            throw new Error('Failed to fetch department');
        }
        const data = await response.json();
        
        const dept = data.department || data;
        document.getElementById('view_dept_name').textContent = dept.name;
        document.getElementById('view_dept_code').textContent = dept.code;
        document.getElementById('view_dept_type').textContent = dept.type ? dept.type.charAt(0).toUpperCase() + dept.type.slice(1) : 'N/A';
        
        document.getElementById('deptViewModal').classList.remove('hidden');
        document.getElementById('deptViewModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error fetching department:', error);
        alert('An error occurred while fetching department details');
    }
}

// Edit Department
async function editDept(id) {
    try {
        const response = await fetch(`{{ route('admin.departments.show', ':id') }}`.replace(':id', id));
        if (!response.ok) {
            throw new Error('Failed to fetch department');
        }
        const data = await response.json();
        
        const dept = data.department || data;
        document.getElementById('edit_dept_id').value = dept.id;
        document.getElementById('edit_dept_name').value = dept.name;
        document.getElementById('edit_dept_code').value = dept.code;
        document.getElementById('edit_dept_type').value = dept.type;
        
        document.getElementById('deptEditModal').classList.remove('hidden');
        document.getElementById('deptEditModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error fetching department:', error);
        alert('An error occurred while fetching department details');
    }
}

document.getElementById('deptEditForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('edit_dept_id').value;
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(`{{ route('admin.departments.update', ':id') }}`.replace(':id', id), {
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
            closeDeptEditModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', data.message || 'Department/College updated successfully!');
            }
            // Refresh table without reload
            await window.refreshDepartmentsTable();
        } else {
            const errorMessage = data.message || data.error || (data.errors ? Object.values(data.errors).flat().join(', ') : 'An error occurred');
            alert(errorMessage);
        }
    } catch (error) {
        console.error('Error updating department:', error);
        alert('An error occurred while updating the department');
    }
});

// Delete Department
function deleteDept(id) {
    window.showDeleteModal(
        'Delete Department/College',
        'Are you sure you want to delete this department/college? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`{{ route('admin.departments.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', data.message || 'Department/College deleted successfully!');
                    }
                    // Refresh table without reload
                    await window.refreshDepartmentsTable();
                } else {
                    const errorMessage = data.message || data.error || 'An error occurred';
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('Error deleting department:', error);
                alert('An error occurred while deleting the department');
            }
        }
    );
}

// Modal Controls
function openDeptCreateModal() {
    document.getElementById('deptCreateModal').classList.remove('hidden');
    document.getElementById('deptCreateModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDeptCreateModal() {
    document.getElementById('deptCreateModal').classList.add('hidden');
    document.getElementById('deptCreateModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('deptCreateForm').reset();
}

function closeDeptViewModal() {
    document.getElementById('deptViewModal').classList.add('hidden');
    document.getElementById('deptViewModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function closeDeptEditModal() {
    document.getElementById('deptEditModal').classList.add('hidden');
    document.getElementById('deptEditModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('deptEditForm').reset();
}

// Close on overlay click
document.getElementById('deptCreateModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeDeptCreateModal();
});
document.getElementById('deptViewModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeDeptViewModal();
});
document.getElementById('deptEditModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeDeptEditModal();
});

// Auto submit form with debounce
let deptSearchTimeout;
window.autoSubmitDeptForm = function(formId) {
    clearTimeout(deptSearchTimeout);
    deptSearchTimeout = setTimeout(() => {
        document.getElementById(formId).submit();
    }, 500);
};

// Update broadcast status indicator
function updateBroadcastStatus(status, message) {
    const indicator = document.getElementById('broadcast-indicator');
    const text = document.getElementById('broadcast-text');
    const container = document.getElementById('broadcast-status');
    
    if (!indicator || !text || !container) return;
    
    // Remove all status classes
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

// Real-time broadcasting listeners (comments-style: retry + subscribed/error)
function setupDepartmentsBroadcastingListeners() {
    if (!window.Echo || window.Echo._isDummy) {
        updateBroadcastStatus('connecting', 'Connecting...');
        setTimeout(setupDepartmentsBroadcastingListeners, 100);
        return;
    }
    @if(auth()->user()->isAdmin())
    try {
    window.Echo.private('admin.settings')
            .subscribed(() => updateBroadcastStatus('connected', 'Live Updates'))
            .error(() => updateBroadcastStatus('error', 'Auth Error'))
            .listen('.department.created', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.department.updated', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.department.deleted', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.program.created', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.program.updated', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.program.deleted', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.document-type.created', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.document-type.updated', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); })
            .listen('.document-type.deleted', () => { if (typeof window.refreshDepartmentsTable === 'function') window.refreshDepartmentsTable(); });
    } catch (e) {
        updateBroadcastStatus('error', 'Setup Failed');
        console.error('Departments broadcasting init failed:', e);
    }
    @endif
}

// Refresh departments table without reload
async function refreshDepartmentsTable() {
    try {
        const params = new URLSearchParams(window.location.search);
        params.set('tab', 'departments');
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
                currentContentCard.style.transition = 'opacity 0.3s';
                currentContentCard.style.opacity = '0.5';
                
                setTimeout(() => {
                    if (parentEl && currentContentCard.parentElement === parentEl) {
                        const newContentClone = document.importNode(newContentCard, true);
                        parentEl.replaceChild(newContentClone, currentContentCard);
                        setupDepartmentsBroadcastingListeners();
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
        console.error('Error refreshing departments table:', error);
        // Try a soft refresh of the content area again
        try { await window.refreshDepartmentsTable(); } catch (_) {}
    }
}

// Initialize listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    setupDepartmentsBroadcastingListeners();
});
</script>
