@props(['id', 'title' => 'Confirm Delete', 'message' => 'Are you sure you want to delete this item?', 'deleteText' => 'Delete', 'cancelText' => 'Cancel', 'formId' => null])

@php
    $modalId = 'delete-modal-' . $id;
    $overlayId = 'delete-overlay-' . $id;
    $cancelClass = 'delete-cancel-' . $id;
    $confirmClass = 'delete-confirm-' . $id;
    $functionName = 'openDeleteModal' . $id;
@endphp

<!-- Delete Confirmation Modal -->
<div id="{{ $modalId }}" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) window['close{{ $functionName }}']()" aria-labelledby="modal-title-{{ $id }}" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-red-600 dark:text-red-400" id="modal-title-{{ $id }}">
                {{ $title }}
            </h3>
            <button type="button" onclick="window['close{{ $functionName }}']()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="mx-auto flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {!! $message !!}
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" class="{{ $cancelClass }} flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                {{ $cancelText }}
            </button>
            <button type="button" class="{{ $confirmClass }} flex-1 btn-danger min-h-[44px]">
                {{ $deleteText }}
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    let submitCallback = null;
    
    function openModal(callback) {
        const modal = document.getElementById('{{ $modalId }}');
        if (!modal) {
            console.error('Modal not found: {{ $modalId }}');
            return;
        }
        submitCallback = callback;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        const modal = document.getElementById('{{ $modalId }}');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        submitCallback = null;
    }
    
    // Expose functions globally immediately
    window['{{ $functionName }}'] = openModal;
    window['close{{ $functionName }}'] = closeModal;
    
    // Initialize event listeners when DOM is ready
    function initDeleteModal() {
        const modal = document.getElementById('{{ $modalId }}');
        const cancelBtn = document.querySelector('.{{ $cancelClass }}');
        const confirmBtn = document.querySelector('.{{ $confirmClass }}');
        
        if (!modal) return;
        
        // Close on cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }
        
        // Confirm button action
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (submitCallback) {
                    submitCallback();
                }
                closeModal();
            });
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeleteModal);
    } else {
        initDeleteModal();
    }
})();
</script>

