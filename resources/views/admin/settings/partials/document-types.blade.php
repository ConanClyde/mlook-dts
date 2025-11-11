<script>
// Comments-style realtime for document types on admin.settings
(function initializeDocTypesBroadcasting() {
	if (!window.Echo || window.Echo._isDummy) {
		setTimeout(initializeDocTypesBroadcasting, 100);
		return;
	}
	@if(auth()->user()->isAdmin())
	try {
		window.Echo.private('admin.settings')
			.subscribed(() => {})
			.error(() => {})
			.listen('.document-type.created', () => { if (typeof window.refreshDocumentTypesTable === 'function') window.refreshDocumentTypesTable(); })
			.listen('.document-type.updated', () => { if (typeof window.refreshDocumentTypesTable === 'function') window.refreshDocumentTypesTable(); })
			.listen('.document-type.deleted', () => { if (typeof window.refreshDocumentTypesTable === 'function') window.refreshDocumentTypesTable(); });
	} catch (e) {
		console.error('Document types broadcasting init failed:', e);
	}
	@endif
})();
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
        
        <button type="button" onclick="openDTCreateModal()" class="btn-primary w-full sm:w-auto text-sm sm:text-base">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Create Document Type</span>
            <span class="sm:hidden">Create Type</span>
        </button>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6 w-full">
    <form method="GET" action="{{ route('admin.settings.index') }}" id="dt-filter-form" class="w-full">
        <input type="hidden" name="tab" value="document-types">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:flex lg:flex-row gap-3 sm:gap-4 w-full">
            <!-- Search -->
            <div class="lg:flex-1 lg:min-w-0">
                <label for="dt_search" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Search
                </label>
                <input 
                    type="text" 
                    id="dt_search" 
                    name="dt_search" 
                    value="{{ request('dt_search') }}"
                    placeholder="Name or code..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    oninput="autoSubmitDTForm('dt-filter-form')"
                >
            </div>

            <!-- Per Page -->
            <div class="lg:w-auto lg:flex-shrink-0">
                <label for="dt_per_page" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Per Page
                </label>
                <select 
                    id="dt_per_page" 
                    name="dt_per_page" 
                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]"
                    onchange="document.getElementById('dt-filter-form').submit();"
                >
                    <option value="10" {{ request('dt_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('dt_per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('dt_per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('dt_per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Clear Button -->
            <div class="lg:w-auto lg:flex-shrink-0 flex items-end">
                <a href="{{ route('admin.settings.index', ['tab' => 'document-types']) }}" class="w-full lg:w-auto btn-secondary text-sm px-3 py-2 text-center min-h-[44px] flex items-center justify-center">
                    <span class="hidden sm:inline">Clear</span>
                    <span class="sm:hidden">Clear Filters</span>
                </a>
            </div>
        </div>

        <!-- Results Count -->
        @if(request()->hasAny(['dt_search']))
            <div class="mt-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                {{ $documentTypes->total() }} {{ Str::plural('result', $documentTypes->total()) }} found
            </div>
        @endif
    </form>
</div>

<!-- Table -->
<div id="document-types-table" class="border border-gray-200 dark:border-gray-700 rounded-lg sm:rounded-xl overflow-hidden bg-white dark:bg-gray-800">
    <!-- Mobile Card View -->
    <div class="block md:hidden divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($documentTypes as $type)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-2 break-words">{{ $type->name }}</div>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="font-mono">{{ $type->code }}</span>
                        </div>
                        @if($type->description)
                            <div class="text-xs text-gray-600 dark:text-gray-400 break-words">
                                {{ Str::limit($type->description, 80) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button onclick="viewDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                        <x-icon name="eye" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="editDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                        <x-icon name="pencil" type="solid" class="w-5 h-5" />
                    </button>
                    <button onclick="deleteDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                        <x-icon name="trash" type="solid" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <x-icon name="document-duplicate" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No document types found.</p>
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
                    <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                    <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($documentTypes as $type)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white break-words" style="max-width: 200px;">{{ $type->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden mt-1 font-mono">{{ $type->code }}</div>
                        </td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-600 dark:text-gray-300">{{ $type->code }}</div>
                        </td>
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $type->description ? Str::limit($type->description, 50) : 'N/A' }}</div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="View">
                                    <x-icon name="eye" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="editDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors" title="Edit">
                                    <x-icon name="pencil" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                                <button onclick="deleteDT({{ $type->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Delete">
                                    <x-icon name="trash" type="solid" class="w-4 h-4 sm:w-5 sm:h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 sm:px-6 py-12 text-center">
                            <x-icon name="document-duplicate" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">No document types found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($documentTypes->hasPages())
    <div class="mt-6">
        {{ $documentTypes->links('vendor.pagination.minimal') }}
    </div>
@endif

<!-- Modals -->
<div id="dtCreateModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDTCreateModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Create Document Type</h3>
            <button type="button" onclick="closeDTCreateModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Scrollable Form Content -->
        <div class="flex-1 overflow-y-auto">
            <form id="dtCreateForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g., Memorandum">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g., MEMO">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Enter description"></textarea>
            </div>

            <!-- Allowed Roles -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Who can create this document type? <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_admin" name="allowed_roles[]" value="admin" checked class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_registrar" name="allowed_roles[]" value="registrar" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_registrar" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Registrar</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_dean" name="allowed_roles[]" value="dean" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_dean" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Dean</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_dept_head" name="allowed_roles[]" value="department_head" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_dept_head" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Department Head</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_faculty" name="allowed_roles[]" value="faculty" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_faculty" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Faculty</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_staff" name="allowed_roles[]" value="staff" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_staff" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Staff</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_role_student" name="allowed_roles[]" value="student" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_create_role_student" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Student</label>
                    </div>
                </div>
            </div>

            <!-- Allowed Receive -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Who can receive/hold this document type?
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_admin" name="allowed_receive[]" value="admin" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_registrar" name="allowed_receive[]" value="registrar" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_registrar" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Registrar</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_dean" name="allowed_receive[]" value="dean" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_dean" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Dean</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_dept_head" name="allowed_receive[]" value="department_head" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_dept_head" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Department Head</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_faculty" name="allowed_receive[]" value="faculty" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_faculty" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Faculty</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_staff" name="allowed_receive[]" value="staff" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_staff" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Staff</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_create_receive_student" name="allowed_receive[]" value="student" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_create_receive_student" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Student</label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">If empty, anyone can receive this document type.</p>
            </div>

            <!-- Auto-Assignment Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="dt_create_auto_assign" name="auto_assign_enabled" value="1" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500" onchange="toggleDTCreateAutoAssign()">
                    <label for="dt_create_auto_assign" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Auto-Assignment</label>
                </div>

                <div id="dt_create_auto_assign_fields" class="hidden space-y-4 pl-6 border-l-2 border-orange-200 dark:border-orange-800">
                    <!-- Routing Logic -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Routing Logic <span class="text-red-500">*</span>
                        </label>
                        <select name="routing_logic" id="dt_create_routing_logic" onchange="toggleDTCreateRoutingFields()" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select routing logic</option>
                            <option value="role">By Role</option>
                            <option value="department">By Department</option>
                            <option value="specific_user">Specific User</option>
                            <option value="routing_rules">Use Routing Rules</option>
                        </select>
                    </div>

                    <!-- By Role -->
                    <div id="dt_create_role_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Default Receiver Role <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_role" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select role</option>
                            <option value="registrar">Registrar</option>
                            <option value="dean">Dean</option>
                            <option value="department_head">Department Head</option>
                            <option value="faculty">Faculty</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    <!-- By Department -->
                    <div id="dt_create_department_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Default Department <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_department_id" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Specific User -->
                    <div id="dt_create_user_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Specific User <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_user_id" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->usertype }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="dt_create_is_active" name="is_active" value="1" checked class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="dt_create_is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
            </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-b-lg sm:rounded-b-xl">
            <div class="flex gap-3">
                <button type="button" onclick="closeDTCreateModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" form="dtCreateForm" class="flex-1 btn-primary">Create</button>
            </div>
        </div>
    </div>
</div>

<div id="dtEditModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDTEditModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Edit Document Type</h3>
            <button type="button" onclick="closeDTEditModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Scrollable Form Content -->
        <div class="flex-1 overflow-y-auto">
            <form id="dtEditForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="dt_edit_id">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="dt_edit_name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" id="dt_edit_code" name="code" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea id="dt_edit_description" name="description" rows="3" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
            </div>

            <!-- Allowed Roles -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Who can create this document type? <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_admin" name="allowed_roles[]" value="admin" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_registrar" name="allowed_roles[]" value="registrar" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_registrar" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Registrar</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_dean" name="allowed_roles[]" value="dean" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_dean" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Dean</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_dept_head" name="allowed_roles[]" value="department_head" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_dept_head" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Department Head</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_faculty" name="allowed_roles[]" value="faculty" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_faculty" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Faculty</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_staff" name="allowed_roles[]" value="staff" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_staff" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Staff</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_role_student" name="allowed_roles[]" value="student" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="dt_edit_role_student" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Student</label>
                    </div>
                </div>
            </div>

            <!-- Allowed Receive -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Who can receive/hold this document type?
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_admin" name="allowed_receive[]" value="admin" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_registrar" name="allowed_receive[]" value="registrar" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_registrar" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Registrar</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_dean" name="allowed_receive[]" value="dean" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_dean" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Dean</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_dept_head" name="allowed_receive[]" value="department_head" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_dept_head" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Department Head</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_faculty" name="allowed_receive[]" value="faculty" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_faculty" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Faculty</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_staff" name="allowed_receive[]" value="staff" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_staff" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Staff</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dt_edit_receive_student" name="allowed_receive[]" value="student" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="dt_edit_receive_student" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Student</label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">If empty, anyone can receive this document type.</p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="dt_edit_is_active" name="is_active" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="dt_edit_is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
            </div>

            <!-- Auto-Assignment Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="dt_edit_auto_assign" name="auto_assign_enabled" value="1" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500" onchange="toggleDTEditAutoAssign()">
                    <label for="dt_edit_auto_assign" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Auto-Assignment</label>
                </div>

                <div id="dt_edit_auto_assign_fields" class="hidden space-y-4 pl-6 border-l-2 border-orange-200 dark:border-orange-800">
                    <!-- Routing Logic -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Routing Logic <span class="text-red-500">*</span>
                        </label>
                        <select name="routing_logic" id="dt_edit_routing_logic" onchange="toggleDTEditRoutingFields()" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select routing logic</option>
                            <option value="role">By Role</option>
                            <option value="department">By Department</option>
                            <option value="specific_user">Specific User</option>
                            <option value="routing_rules">Use Routing Rules</option>
                        </select>
                    </div>

                    <!-- By Role -->
                    <div id="dt_edit_role_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Default Receiver Role <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_role" id="dt_edit_default_receiver_role" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select role</option>
                            <option value="registrar">Registrar</option>
                            <option value="dean">Dean</option>
                            <option value="department_head">Department Head</option>
                            <option value="faculty">Faculty</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    <!-- By Department -->
                    <div id="dt_edit_department_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Default Department <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_department_id" id="dt_edit_default_receiver_department_id" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Specific User -->
                    <div id="dt_edit_user_field" class="hidden">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Specific User <span class="text-red-500">*</span>
                        </label>
                        <select name="default_receiver_user_id" id="dt_edit_default_receiver_user_id" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->usertype }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-b-lg sm:rounded-b-xl">
            <div class="flex gap-3">
                <button type="button" onclick="closeDTEditModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" form="dtEditForm" class="flex-1 btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>

<div id="dtViewModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDTViewModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Document Type Details</h3>
            <button type="button" onclick="closeDTViewModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="dt_view_name"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-white" id="dt_view_code"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="dt_view_description"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Allowed Roles</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white" id="dt_view_allowed_roles"></dd>
            </div>
            <div>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1" id="dt_view_status"></dd>
            </div>

            <!-- Auto-Assignment Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Auto-Assignment</dt>
                <dd class="mt-1 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Enabled:</span>
                        <span id="dt_view_auto_assign_enabled" class="text-sm text-gray-900 dark:text-white"></span>
                    </div>
                    <div id="dt_view_auto_assign_details" class="hidden pl-4 space-y-2 border-l-2 border-orange-200 dark:border-orange-800">
                        <div>
                            <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Routing Logic:</span>
                            <span id="dt_view_routing_logic" class="ml-2 text-sm text-gray-900 dark:text-white"></span>
                        </div>
                        <div id="dt_view_routing_details" class="text-sm text-gray-900 dark:text-white"></div>
                    </div>
                </dd>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-b-lg sm:rounded-b-xl">
            <div class="flex gap-3">
                <button type="button" onclick="closeDTViewModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openDTCreateModal() {
    document.getElementById('dtCreateModal').classList.remove('hidden');
    document.getElementById('dtCreateModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeDTCreateModal() {
    document.getElementById('dtCreateModal').classList.add('hidden');
    document.getElementById('dtCreateModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('dtCreateForm').reset();
}
function closeDTEditModal() {
    document.getElementById('dtEditModal').classList.add('hidden');
    document.getElementById('dtEditModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('dtEditForm').reset();
    // Reset auto assign fields visibility
    document.getElementById('dt_edit_auto_assign_fields').classList.add('hidden');
    document.getElementById('dt_edit_role_field').classList.add('hidden');
    document.getElementById('dt_edit_department_field').classList.add('hidden');
    document.getElementById('dt_edit_user_field').classList.add('hidden');
}
function closeDTViewModal() {
    document.getElementById('dtViewModal').classList.add('hidden');
    document.getElementById('dtViewModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Toggle auto-assignment fields
function toggleDTCreateAutoAssign() {
    const checkbox = document.getElementById('dt_create_auto_assign');
    const fields = document.getElementById('dt_create_auto_assign_fields');
    if (checkbox.checked) {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
    }
}

function toggleDTEditAutoAssign() {
    const checkbox = document.getElementById('dt_edit_auto_assign');
    const fields = document.getElementById('dt_edit_auto_assign_fields');
    if (checkbox.checked) {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
    }
}

// Toggle routing logic fields
function toggleDTCreateRoutingFields() {
    const select = document.getElementById('dt_create_routing_logic');
    const roleField = document.getElementById('dt_create_role_field');
    const deptField = document.getElementById('dt_create_department_field');
    const userField = document.getElementById('dt_create_user_field');
    
    // Hide all fields first
    roleField.classList.add('hidden');
    deptField.classList.add('hidden');
    userField.classList.add('hidden');
    
    // Show relevant field based on selection
    if (select.value === 'role') {
        roleField.classList.remove('hidden');
    } else if (select.value === 'department') {
        deptField.classList.remove('hidden');
    } else if (select.value === 'specific_user') {
        userField.classList.remove('hidden');
    }
}

function toggleDTEditRoutingFields() {
    const select = document.getElementById('dt_edit_routing_logic');
    const roleField = document.getElementById('dt_edit_role_field');
    const deptField = document.getElementById('dt_edit_department_field');
    const userField = document.getElementById('dt_edit_user_field');
    
    // Hide all fields first
    roleField.classList.add('hidden');
    deptField.classList.add('hidden');
    userField.classList.add('hidden');
    
    // Show relevant field based on selection
    if (select.value === 'role') {
        roleField.classList.remove('hidden');
    } else if (select.value === 'department') {
        deptField.classList.remove('hidden');
    } else if (select.value === 'specific_user') {
        userField.classList.remove('hidden');
    }
}

document.getElementById('dtCreateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'allowed_roles[]') {
            if (!data.allowed_roles) data.allowed_roles = [];
            data.allowed_roles.push(value);
        } else {
            data[key] = value;
        }
    }
    try {
        const response = await fetch('{{ route('admin.document-types.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeDTCreateModal();
            showSuccessModal(result.message);
            // Table will refresh via broadcast listener
        }
    } catch (error) { console.error('Error:', error); }
});

async function viewDT(id) {
    try {
        const response = await fetch(`/admin/document-types/${id}`);
        const result = await response.json();
        const type = result.documentType;
        
        document.getElementById('dt_view_name').textContent = type.name;
        document.getElementById('dt_view_code').textContent = type.code;
        document.getElementById('dt_view_description').textContent = type.description || 'N/A';
        
        // Display allowed roles
        const allowedRoles = type.allowed_roles || [];
        const roleLabels = {
            'admin': 'Admin',
            'registrar': 'Registrar',
            'dean': 'Dean',
            'department_head': 'Department Head',
            'faculty': 'Faculty',
            'staff': 'Staff',
            'student': 'Student'
        };
        const rolesText = allowedRoles.map(role => roleLabels[role] || role).join(', ') || 'None';
        document.getElementById('dt_view_allowed_roles').textContent = rolesText;
        
        document.getElementById('dt_view_status').innerHTML = type.is_active 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Active</span>' 
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">Inactive</span>';
        
        // Display auto-assignment info
        const autoAssignEnabled = type.auto_assign_enabled || false;
        document.getElementById('dt_view_auto_assign_enabled').innerHTML = autoAssignEnabled
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Yes</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">No</span>';
        
        const detailsDiv = document.getElementById('dt_view_auto_assign_details');
        if (autoAssignEnabled && type.routing_logic) {
            detailsDiv.classList.remove('hidden');
            
            // Display routing logic
            const logicLabels = {
                'role': 'By Role',
                'department': 'By Department',
                'specific_user': 'Specific User',
                'routing_rules': 'Use Routing Rules'
            };
            document.getElementById('dt_view_routing_logic').textContent = logicLabels[type.routing_logic] || type.routing_logic;
            
            // Display routing details
            const detailsContent = document.getElementById('dt_view_routing_details');
            let detailsHtml = '<span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Details: </span>';
            
            if (type.routing_logic === 'role' && type.default_receiver_role) {
                detailsHtml += `<span class="ml-2 font-medium">${roleLabels[type.default_receiver_role] || type.default_receiver_role}</span>`;
            } else if (type.routing_logic === 'department' && type.default_receiver_department) {
                detailsHtml += `<span class="ml-2 font-medium">${type.default_receiver_department.name} (${type.default_receiver_department.code})</span>`;
            } else if (type.routing_logic === 'specific_user' && type.default_receiver_user) {
                detailsHtml += `<span class="ml-2 font-medium">${type.default_receiver_user.full_name} (${type.default_receiver_user.usertype})</span>`;
            } else if (type.routing_logic === 'routing_rules') {
                detailsHtml += '<span class="ml-2 font-medium">Uses system routing rules</span>';
            } else {
                detailsHtml += '<span class="ml-2 text-gray-500">Not configured</span>';
            }
            
            detailsContent.innerHTML = detailsHtml;
        } else {
            detailsDiv.classList.add('hidden');
        }
        
        document.getElementById('dtViewModal').classList.remove('hidden');
        document.getElementById('dtViewModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) { console.error('Error:', error); }
}

async function editDT(id) {
    try {
        const response = await fetch(`/admin/document-types/${id}`);
        const result = await response.json();
        const type = result.documentType;
        document.getElementById('dt_edit_id').value = type.id;
        document.getElementById('dt_edit_name').value = type.name;
        document.getElementById('dt_edit_code').value = type.code;
        document.getElementById('dt_edit_description').value = type.description || '';
        document.getElementById('dt_edit_is_active').checked = type.is_active;
        
        // Set allowed roles
        const allowedRoles = type.allowed_roles || [];
        document.querySelectorAll('[id^="dt_edit_role_"]').forEach(checkbox => {
            checkbox.checked = allowedRoles.includes(checkbox.value);
        });
        
        // Set allowed receive
        const allowedReceive = type.allowed_receive || [];
        document.querySelectorAll('[id^="dt_edit_receive_"]').forEach(checkbox => {
            checkbox.checked = allowedReceive.includes(checkbox.value);
        });
        
        // Set auto-assignment fields
        const autoAssignEnabled = type.auto_assign_enabled || false;
        document.getElementById('dt_edit_auto_assign').checked = autoAssignEnabled;
        toggleDTEditAutoAssign();
        
        if (autoAssignEnabled) {
            if (type.routing_logic) {
                document.getElementById('dt_edit_routing_logic').value = type.routing_logic;
                toggleDTEditRoutingFields();
                
                if (type.routing_logic === 'role' && type.default_receiver_role) {
                    document.getElementById('dt_edit_default_receiver_role').value = type.default_receiver_role;
                } else if (type.routing_logic === 'department' && type.default_receiver_department_id) {
                    document.getElementById('dt_edit_default_receiver_department_id').value = type.default_receiver_department_id;
                } else if (type.routing_logic === 'specific_user' && type.default_receiver_user_id) {
                    document.getElementById('dt_edit_default_receiver_user_id').value = type.default_receiver_user_id;
                }
            }
        }
        
        document.getElementById('dtEditModal').classList.remove('hidden');
        document.getElementById('dtEditModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (error) { console.error('Error:', error); }
}

document.getElementById('dtEditForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dt_edit_id').value;
    const formData = new FormData(this);
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'allowed_roles[]') {
            if (!data.allowed_roles) data.allowed_roles = [];
            data.allowed_roles.push(value);
        } else {
            data[key] = value;
        }
    }
    try {
        const response = await fetch(`/admin/document-types/${id}`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeDTEditModal();
            showSuccessModal(result.message);
            // Table will refresh via broadcast listener
        }
    } catch (error) { console.error('Error:', error); }
});

function deleteDT(id) {
    showDeleteModal(
        'Delete Document Type',
        'Are you sure you want to delete this document type?',
        async () => {
            try {
                const response = await fetch(`/admin/document-types/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });
                const result = await response.json();
                if (result.success) {
                    showSuccessModal(result.message);
                    // Table will refresh via broadcast listener
                } else {
                    alert(result.error || 'Error deleting document type');
                }
            } catch (error) { console.error('Error:', error); }
        }
    );
}

// Auto submit form with debounce
let dtSearchTimeout;
window.autoSubmitDTForm = function(formId) {
    clearTimeout(dtSearchTimeout);
    dtSearchTimeout = setTimeout(() => {
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
function setupDocumentTypesBroadcastingListeners() {
    if (!window.Echo) {
        updateBroadcastStatus('connecting', 'Connecting...');
        setTimeout(setupDocumentTypesBroadcastingListeners, 100);
        return;
    }
    
    updateBroadcastStatus('connecting', 'Setting up...');
    
    @if(auth()->user()->isAdmin())
    window.Echo.private('admin.settings')
        .listen('.document-type.created', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.document-type.updated', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.document-type.deleted', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.program.created', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.program.updated', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.program.deleted', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.department.created', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.department.updated', (e) => {
            refreshDocumentTypesTable();
        })
        .listen('.department.deleted', (e) => {
            refreshDocumentTypesTable();
        });
    
    updateBroadcastStatus('connected', 'Live Updates');
    @endif
}

// Refresh document types table without reload
async function refreshDocumentTypesTable() {
    try {
        const params = new URLSearchParams(window.location.search);
        params.set('tab', 'document-types');
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
                        setupDocumentTypesBroadcastingListeners();
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
        console.error('Error refreshing document types table:', error);
        try { await refreshDocumentTypesTable(); } catch (_) {}
    }
}

// Initialize listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    setupDocumentTypesBroadcastingListeners();
});
</script>

