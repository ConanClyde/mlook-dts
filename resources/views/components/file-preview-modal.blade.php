@props(['attachments'])

<!-- File Preview Modal -->
<div id="file-preview-modal" class="fixed inset-0 bg-black/70 z-[60] hidden items-center justify-center p-4" onclick="if(event.target === this) closeFilePreview()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-5xl max-h-[95vh] h-[95vh] flex flex-col shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white" id="preview-file-name">File Preview</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400" id="preview-file-info"></p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeFilePreview()" class="p-2 min-w-[36px] min-h-[36px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center justify-center">
                    <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
                </button>
            </div>
        </div>

        <!-- Preview Content -->
        <div class="flex-1 overflow-auto p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 rounded-b-lg sm:rounded-b-xl" id="preview-content" oncontextmenu="return false;" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
            <div class="flex items-center justify-center h-full">
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <x-icon name="document" class="w-16 h-16 mx-auto mb-4 opacity-50" />
                    <p>Select a file to preview</p>
                </div>
            </div>
        </div>

        <!-- Attachments List (if multiple) -->
        @if(isset($attachments) && count($attachments) > 1)
        <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">All Attachments ({{ count($attachments) }})</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 max-h-32 overflow-y-auto">
                @foreach($attachments as $attachment)
                <button type="button" 
                        onclick="previewFile('{{ $attachment->id }}', '{{ $attachment->file_name }}', '{{ $attachment->file_type }}', '{{ $attachment->file_url }}', '{{ $attachment->formatted_file_size }}')"
                        class="flex items-center gap-2 p-2 min-h-[44px] text-left text-xs sm:text-sm bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    <x-icon name="document" class="w-4 h-4 text-gray-600 dark:text-gray-400 flex-shrink-0" />
                    <span class="truncate text-gray-900 dark:text-white">{{ $attachment->file_name }}</span>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function openFilePreview() {
    const modal = document.getElementById('file-preview-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeFilePreview() {
    const modal = document.getElementById('file-preview-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function previewFile(id, fileName, fileType, fileUrl, fileSize) {
    openFilePreview();
    
    // Update header
    document.getElementById('preview-file-name').textContent = fileName;
    document.getElementById('preview-file-info').textContent = `${fileSize} • ${fileType}`;
    
    const previewContent = document.getElementById('preview-content');
    
    // Determine preview type based on file type
    if (fileType.includes('pdf')) {
        // PDF Preview - non-downloadable
        previewContent.innerHTML = `
            <iframe src="${fileUrl}#toolbar=0&navpanes=0&scrollbar=1" 
                    class="w-full h-full min-h-[600px] rounded-lg border border-gray-200 dark:border-gray-700"
                    frameborder="0"
                    oncontextmenu="return false;">
            </iframe>
        `;
    } else if (fileType.includes('image')) {
        // Image Preview - non-downloadable
        previewContent.innerHTML = `
            <div class="flex items-center justify-center h-full" oncontextmenu="return false;" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                <img src="${fileUrl}" 
                     alt="${fileName}" 
                     class="max-w-full max-h-full object-contain rounded-lg shadow-lg pointer-events-none" 
                     draggable="false" 
                     oncontextmenu="return false;" 
                     style="user-select: none; -webkit-user-drag: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
            </div>
        `;
    } else if (fileType.includes('text') || fileType.includes('plain')) {
        // Text Preview
        fetch(fileUrl)
            .then(response => response.text())
            .then(text => {
                previewContent.innerHTML = `
                    <pre class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 overflow-auto text-sm text-gray-900 dark:text-white whitespace-pre-wrap">${escapeHtml(text)}</pre>
                `;
            })
            .catch(error => {
                previewContent.innerHTML = `
                    <div class="text-center text-red-600 dark:text-red-400">
                        <p>Unable to preview this file.</p>
                        <a href="${fileUrl}" download="${fileName}" class="text-orange-600 hover:text-orange-700 underline mt-2 inline-block">Download instead</a>
                    </div>
                `;
            });
    } else {
        // Unsupported file type
        previewContent.innerHTML = `
            <div class="text-center">
                <x-icon name="document" class="w-24 h-24 mx-auto mb-4 text-gray-400 dark:text-gray-600" />
                <p class="text-gray-600 dark:text-gray-400 mb-4">Preview not available for this file type</p>
                <a href="${fileUrl}" 
                   download="${fileName}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                    <x-icon name="arrow-down-tray" class="w-5 h-5" />
                    Download File
                </a>
            </div>
        `;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close on Escape key and prevent download shortcuts
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeFilePreview();
    }
    // Prevent common download shortcuts
    if ((event.ctrlKey || event.metaKey) && (event.key === 's' || event.key === 'S')) {
        const modal = document.getElementById('file-preview-modal');
        if (modal && !modal.classList.contains('hidden')) {
            event.preventDefault();
            return false;
        }
    }
});

// Prevent right-click context menu on preview modal
document.addEventListener('contextmenu', function(event) {
    const modal = document.getElementById('file-preview-modal');
    if (modal && !modal.classList.contains('hidden')) {
        const content = document.getElementById('preview-content');
        if (content && content.contains(event.target)) {
            event.preventDefault();
            return false;
        }
    }
}, false);
</script>
