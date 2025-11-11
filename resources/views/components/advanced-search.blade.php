@props(['documentTypes', 'departments', 'statuses'])

<div x-data="{ showFilters: false }" class="mb-6">
    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="flex-1">
            <input type="text" 
                   name="search" 
                   id="search-input"
                   placeholder="Search by title, tracking number, or description..." 
                   value="{{ request('search') }}"
                   class="w-full px-3 sm:px-4 py-2.5 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
        </div>
        <button type="button" 
                @click="showFilters = !showFilters"
                class="px-4 py-2.5 min-h-[44px] bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2 w-full sm:w-auto">
            <x-icon name="adjustments-horizontal" class="w-5 h-5" />
            <span>Filters</span>
            <span x-show="showFilters" class="text-xs">(Hide)</span>
        </button>
        <button type="submit" 
                class="px-4 sm:px-6 py-2.5 min-h-[44px] bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2 w-full sm:w-auto">
            <x-icon name="magnifying-glass" class="w-5 h-5" />
            <span>Search</span>
        </button>
    </div>

    <!-- Advanced Filters Panel -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Document Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Document Type
                </label>
                <select name="document_type" 
                        class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Types</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type->id }}" {{ request('document_type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select name="status_filter" 
                        class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status_filter') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Department
                </label>
                <select name="department" 
                        class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Urgency Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Urgency Level
                </label>
                <select name="urgency" 
                        class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Levels</option>
                    <option value="low" {{ request('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('urgency') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('urgency') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date From
                </label>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date To
                </label>
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Overdue Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Deadline Status
                </label>
                <select name="deadline_status" 
                        class="w-full px-3 py-2 min-h-[44px] border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All</option>
                    <option value="overdue" {{ request('deadline_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="approaching" {{ request('deadline_status') == 'approaching' ? 'selected' : '' }}>Approaching (24hrs)</option>
                    <option value="on_time" {{ request('deadline_status') == 'on_time' ? 'selected' : '' }}>On Time</option>
                </select>
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ url()->current() }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
                <x-icon name="x-mark" class="w-4 h-4" />
                Clear All Filters
            </a>
            
            <div class="flex flex-col sm:flex-row gap-2">
                <button type="button" 
                        @click="showFilters = false"
                        class="px-4 py-2.5 min-h-[44px] text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2.5 min-h-[44px] text-sm text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>
