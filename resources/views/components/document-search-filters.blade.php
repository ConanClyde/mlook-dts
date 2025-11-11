@props(['documentTypes' => [], 'showFilters' => false])

<div x-data="{ showFilters: {{ $showFilters ? 'true' : 'false' }} }" class="mb-6">
    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                </div>
                <input type="text" 
                       name="search" 
                       id="search-input"
                       value="{{ request('search') }}"
                       placeholder="Search by title, tracking number, or description..."
                       class="block w-full pl-10 pr-3 py-2.5 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                       @keyup.enter="submitSearch()">
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" 
                    onclick="submitSearch()"
                    class="btn-primary whitespace-nowrap min-h-[44px] flex items-center justify-center w-full sm:w-auto">
                <x-icon name="magnifying-glass" class="w-4 h-4 mr-2" />
                Search
            </button>
            
            <button type="button" 
                    @click="showFilters = !showFilters"
                    class="btn-secondary whitespace-nowrap min-h-[44px] flex items-center justify-center w-full sm:w-auto">
                <x-icon name="funnel" class="w-4 h-4 mr-2" />
                Filters
                <x-icon name="chevron-down" class="w-4 h-4 ml-2" x-show="!showFilters" />
                <x-icon name="chevron-up" class="w-4 h-4 ml-2" x-show="showFilters" x-cloak />
            </button>
            
            @if(request()->hasAny(['search', 'document_type', 'urgency', 'status_filter', 'date_from', 'date_to']))
                <button type="button" 
                        onclick="clearFilters()"
                        class="btn-secondary whitespace-nowrap min-h-[44px] flex items-center justify-center w-full sm:w-auto">
                    <x-icon name="x-mark" class="w-4 h-4 mr-2" />
                    Clear
                </button>
            @endif
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="mt-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
         x-cloak>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Document Type Filter -->
            <div>
                <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Document Type
                </label>
                <select id="document_type" 
                        name="document_type"
                        class="block w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">All Types</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type->id }}" {{ request('document_type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Urgency Filter -->
            <div>
                <label for="urgency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Urgency Level
                </label>
                <select id="urgency" 
                        name="urgency"
                        class="block w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">All Levels</option>
                    <option value="low" {{ request('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('urgency') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('urgency') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Status
                </label>
                <select id="status_filter" 
                        name="status_filter"
                        class="block w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status_filter') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="routing" {{ request('status_filter') == 'routing' ? 'selected' : '' }}>Routing</option>
                    <option value="received" {{ request('status_filter') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="in_review" {{ request('status_filter') == 'in_review' ? 'selected' : '' }}>In Review</option>
                    <option value="for_approval" {{ request('status_filter') == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                    <option value="approved" {{ request('status_filter') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status_filter') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="returned" {{ request('status_filter') == 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="archived" {{ request('status_filter') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            
            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date From
                </label>
                <input type="date" 
                       id="date_from" 
                       name="date_from"
                       value="{{ request('date_from') }}"
                       class="block w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
            
            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date To
                </label>
                <input type="date" 
                       id="date_to" 
                       name="date_to"
                       value="{{ request('date_to') }}"
                       class="block w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
        
        <!-- Apply Filters Button -->
        <div class="mt-4 flex justify-end">
            <button type="button" 
                    onclick="submitSearch()"
                    class="btn-primary min-h-[44px] px-4 py-2.5">
                Apply Filters
            </button>
        </div>
    </div>
</div>

<script>
function submitSearch() {
    const params = new URLSearchParams(window.location.search);
    
    // Get search value
    const searchValue = document.getElementById('search-input').value;
    if (searchValue) {
        params.set('search', searchValue);
    } else {
        params.delete('search');
    }
    
    // Get all filter values
    const filters = ['document_type', 'urgency', 'status_filter', 'date_from', 'date_to'];
    filters.forEach(filter => {
        const element = document.getElementById(filter);
        if (element && element.value) {
            params.set(filter, element.value);
        } else {
            params.delete(filter);
        }
    });
    
    // Preserve the tab parameter
    const currentTab = params.get('tab') || 'all';
    params.set('tab', currentTab);
    
    // Redirect with parameters
    window.location.href = window.location.pathname + '?' + params.toString();
}

function clearFilters() {
    const params = new URLSearchParams();
    const currentTab = new URLSearchParams(window.location.search).get('tab') || 'all';
    params.set('tab', currentTab);
    window.location.href = window.location.pathname + '?' + params.toString();
}
</script>

