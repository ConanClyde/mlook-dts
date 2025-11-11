<div id="activityModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeActivityModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Account Activity</h3>
            <button type="button" onclick="closeActivityModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Content -->
        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
            
            <div class="space-y-4" id="activityContent">
                @php
                    $activities = [
                        [
                            'type' => 'registration',
                            'description' => 'Account created',
                            'date' => auth()->user()->created_at,
                        ],
                        [
                            'type' => 'login',
                            'description' => 'Last login',
                            'date' => auth()->user()->updated_at,
                        ],
                    ];
                @endphp
                
                @foreach($activities as $activity)
                <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <x-icon name="clock" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white break-words">{{ $activity['description'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $activity['date']->format('F d, Y h:i A') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeActivityModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openActivityModal() {
    document.getElementById('activityModal').classList.remove('hidden');
    document.getElementById('activityModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeActivityModal() {
    document.getElementById('activityModal').classList.add('hidden');
    document.getElementById('activityModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}
</script>

