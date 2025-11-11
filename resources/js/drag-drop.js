/**
 * Drag & Drop File Upload
 * Enhanced file upload with drag and drop functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop for file inputs
    initializeDragDrop();
});

function initializeDragDrop() {
    // Find all file input containers
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        const container = input.closest('.file-upload-container') || input.parentElement;
        
        if (!container || container.classList.contains('drag-drop-initialized')) {
            return;
        }
        
        container.classList.add('drag-drop-initialized');
        
        // Add drag and drop styling classes
        container.classList.add('relative', 'transition-all', 'duration-200');
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            container.addEventListener(eventName, () => highlight(container), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, () => unhighlight(container), false);
        });
        
        // Handle dropped files
        container.addEventListener('drop', (e) => handleDrop(e, input), false);
    });
    
    // Global drop zone for document pages
    createGlobalDropZone();
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(element) {
    element.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20', 'ring-2', 'ring-orange-500');
}

function unhighlight(element) {
    element.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20', 'ring-2', 'ring-orange-500');
}

function handleDrop(e, input) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    // Set files to input
    input.files = files;
    
    // Trigger change event
    const event = new Event('change', { bubbles: true });
    input.dispatchEvent(event);
    
    // Show file names
    displaySelectedFiles(files, input);
}

function displaySelectedFiles(files, input) {
    const container = input.closest('.file-upload-container') || input.parentElement;
    let fileList = container.querySelector('.file-list');
    
    if (!fileList) {
        fileList = document.createElement('div');
        fileList.className = 'file-list mt-3 space-y-2';
        container.appendChild(fileList);
    }
    
    fileList.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg';
        fileItem.innerHTML = `
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">${file.name}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</div>
                </div>
            </div>
            <button type="button" class="text-red-500 hover:text-red-700" onclick="removeFile(${index}, this)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Global drop zone for quick uploads
function createGlobalDropZone() {
    let dropZone = null;
    let dragCounter = 0;
    
    document.addEventListener('dragenter', function(e) {
        // Only show drop zone if dragging files
        if (e.dataTransfer.types.includes('Files')) {
            dragCounter++;
            
            if (!dropZone) {
                dropZone = document.createElement('div');
                dropZone.id = 'global-drop-zone';
                dropZone.className = 'fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center';
                dropZone.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-12 text-center border-4 border-dashed border-orange-500">
                        <svg class="w-20 h-20 mx-auto text-orange-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Drop files to upload</h3>
                        <p class="text-gray-600 dark:text-gray-400">Release to upload files</p>
                    </div>
                `;
                document.body.appendChild(dropZone);
                
                dropZone.addEventListener('dragleave', function(e) {
                    dragCounter--;
                    if (dragCounter === 0) {
                        dropZone.remove();
                        dropZone = null;
                    }
                });
                
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dragCounter = 0;
                    
                    const files = e.dataTransfer.files;
                    
                    // Find file input on current page
                    const fileInput = document.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.files = files;
                        const event = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(event);
                        displaySelectedFiles(files, fileInput);
                    }
                    
                    dropZone.remove();
                    dropZone = null;
                });
            }
        }
    });
}

// Make removeFile function global
window.removeFile = function(index, button) {
    const fileList = button.closest('.file-list');
    const container = fileList.closest('.file-upload-container') || fileList.parentElement;
    const input = container.querySelector('input[type="file"]');
    
    if (input && input.files) {
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        displaySelectedFiles(input.files, input);
    }
};

// Drag & Drop upload initialized
