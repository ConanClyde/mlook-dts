@props(['user'])

<div id="viewModal{{ $user->id }}" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) window['closeViewModal{{ $user->id }}']()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">User Details</h3>
            <button type="button" onclick="window['closeViewModal{{ $user->id }}']()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Content -->
        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            <!-- Name Section -->
            <div class="flex items-center space-x-4">
                @if($user->avatar)
                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                @else
                    @php
                        $firstLetter = strtoupper(substr($user->first_name, 0, 1));
                        $colors = [
                            'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
                            'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
                            'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
                            'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
                            'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
                            'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
                            'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
                            'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
                            'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
                            'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
                            'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
                            'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
                            'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
                            'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
                            'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
                            'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
                            'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
                            'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
                            'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
                            'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
                            'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
                            'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
                            'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
                            'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
                            'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
                            'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
                        ];
                        $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
                    @endphp
                    <div class="w-16 h-16 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-2xl">{{ $firstLetter }}</span>
                    </div>
                @endif
                <div>
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $user->usertype) }}</p>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email</label>
                    <p class="text-sm text-gray-900 dark:text-white break-all">{{ $user->email }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">University ID</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $user->university_id }}</p>
                </div>

                @if($user->program)
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Program/Course</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $user->program->name }}</p>
                        @if($user->program->college)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->program->college->name }}</p>
                        @endif
                    </div>
                @endif

                @if($user->department)
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ $user->department->type === 'college' ? 'College' : 'Department' }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $user->department->name }} ({{ $user->department->code }})</p>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Member Since</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="window['closeViewModal{{ $user->id }}']()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    // Ensure functions are in global scope
    window['openViewModal{{ $user->id }}'] = function() {
        const modal = document.getElementById('viewModal{{ $user->id }}');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    };

    window['closeViewModal{{ $user->id }}'] = function() {
        const modal = document.getElementById('viewModal{{ $user->id }}');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    };

    // Initialize event listeners when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModal{{ $user->id }});
    } else {
        initModal{{ $user->id }}();
    }

    function initModal{{ $user->id }}() {
        const modal = document.getElementById('viewModal{{ $user->id }}');
        if (!modal) return;

        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                window['closeViewModal{{ $user->id }}']();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                window['closeViewModal{{ $user->id }}']();
            }
        });
    }
})();
</script>

