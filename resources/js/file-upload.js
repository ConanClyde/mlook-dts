// File upload state - initialize once
if (!window.fileUploadState) {
    window.fileUploadState = new Map();
}

// Get file type icon
function getFileIcon(fileName) {
    const ext = fileName.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': { bg: 'bg-red-500', text: 'PDF' },
        'doc': { bg: 'bg-blue-500', text: 'DOC' },
        'docx': { bg: 'bg-blue-500', text: 'DOCX' },
        'xls': { bg: 'bg-green-500', text: 'XLS' },
        'xlsx': { bg: 'bg-green-500', text: 'XLSX' },
        'jpg': { bg: 'bg-purple-500', text: 'JPG' },
        'jpeg': { bg: 'bg-purple-500', text: 'JPEG' },
        'png': { bg: 'bg-purple-500', text: 'PNG' },
    };
    return iconMap[ext] || { bg: 'bg-gray-500', text: ext.toUpperCase() };
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    if (bytes < 1024) return bytes + ' B';
    const k = 1024;
    const sizes = ['KB', 'MB', 'GB', 'TB'];
    // Calculate which unit to use (KB=1, MB=2, GB=3, etc.)
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    // Adjust index: i=1 means KB, i=2 means MB, etc.
    const unitIndex = Math.min(i - 1, sizes.length - 1);
    const size = bytes / Math.pow(k, i);
    // Round to 2 decimal places
    const rounded = Math.round(size * 100) / 100;
    return rounded + ' ' + sizes[unitIndex];
}

// Track if we're dragging to prevent click from opening file picker
window.isDragging = false;
let dragTimeout = null;

// Handle drag over
window.handleDragOver = function(e) {
    e.preventDefault();
    e.stopPropagation();
    window.isDragging = true;
    // Clear any pending timeout
    if (dragTimeout) {
        clearTimeout(dragTimeout);
        dragTimeout = null;
    }
    e.currentTarget.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
}

// Handle drag leave
window.handleDragLeave = function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
    // Reset dragging flag after a short delay to allow for drop
    if (dragTimeout) {
        clearTimeout(dragTimeout);
    }
    dragTimeout = setTimeout(() => {
        window.isDragging = false;
        dragTimeout = null;
    }, 200);
}

// Handle file drop
window.handleFileDrop = function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (dragTimeout) {
        clearTimeout(dragTimeout);
        dragTimeout = null;
    }
    window.isDragging = false;
    e.currentTarget.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
    
    const files = Array.from(e.dataTransfer.files);
    const container = e.currentTarget.closest('.file-upload-container');
    processFiles(files, container);
    // Reset dragging flag after processing
    setTimeout(() => {
        window.isDragging = false;
    }, 100);
}

// Handle drop zone click (only if not dragging)
window.handleDropZoneClick = function(e, inputId) {
    // Don't open file picker if we just finished dragging
    if (isDragging) {
        return;
    }
    // Don't open if clicking on the button (it has its own handler)
    if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
        return;
    }
    const input = document.getElementById(inputId);
    if (input) {
        input.click();
    }
}

// Handle file select
window.handleFileSelect = function(e) {
    const files = Array.from(e.target.files);
    const container = e.target.closest('.file-upload-container');
    
    // Store first file globally for Alpine.js components
    if (files.length > 0) {
        window.lastSelectedFile = files[0];
        console.log('✅ File stored globally:', window.lastSelectedFile.name, '(' + (window.lastSelectedFile.size / 1024 / 1024).toFixed(2) + ' MB)');
    }
    
    processFiles(files, container);
    // DON'T reset the input - we need to keep the file!
    // e.target.value = '';
}

// Process files
function processFiles(files, container) {
    const config = {
        name: container.dataset.name,
        multiple: container.dataset.multiple === 'true',
        maxSize: parseInt(container.dataset.maxSize) * 1024 * 1024, // Convert MB to bytes
        acceptedTypes: container.dataset.acceptedTypes.split(','),
        maxFiles: container.dataset.maxFiles ? parseInt(container.dataset.maxFiles) : null
    };
    
    const fileList = container.querySelector('.file-list');
    const existingFiles = fileList.querySelectorAll('.file-item').length;
    
    // Check max files
    if (config.maxFiles && existingFiles + files.length > config.maxFiles) {
        showError(`Maximum ${config.maxFiles} file(s) allowed. You tried to add ${files.length} file(s), but only ${config.maxFiles - existingFiles} slot(s) remaining.`);
        return;
    }
    
    // Check for duplicate files by name
    const existingFileNames = Array.from(fileList.querySelectorAll('.file-item')).map(item => item.dataset.fileName);
    
    const errors = [];
    const validFiles = [];
    
    files.forEach(file => {
        let fileError = null;
        
        // Check if file already exists
        if (existingFileNames.includes(file.name)) {
            fileError = `File "${file.name}" is already added.`;
        }
        // Validate file size
        else if (file.size > config.maxSize) {
            fileError = `File "${file.name}" exceeds maximum size of ${formatFileSize(config.maxSize)}. Current size: ${formatFileSize(file.size)}.`;
        }
        // Validate file type
        else {
            const fileExt = '.' + file.name.split('.').pop().toLowerCase();
            if (!config.acceptedTypes.includes(fileExt)) {
                const allowedTypes = config.acceptedTypes.map(t => t.replace('.', '').toUpperCase()).join(', ');
                fileError = `File "${file.name}" has unsupported format. Allowed formats: ${allowedTypes}.`;
            }
        }
        
        if (fileError) {
            errors.push({ fileName: file.name, error: fileError });
        } else {
            validFiles.push(file);
            existingFileNames.push(file.name); // Track added file
        }
    });
    
    // Show errors if any
    if (errors.length > 0) {
        const errorMessages = errors.map(e => e.error).join('\n');
        showError(errorMessages);
    }
    
    // Add valid files to list
    validFiles.forEach(file => {
        addFileToList(file, fileList, container);
    });
}

// Show error message
function showError(message) {
    // Create or get error container
    let errorContainer = document.getElementById('file-upload-error-container');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'file-upload-error-container';
        errorContainer.className = 'mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg';
        
        // Find the first file-upload-container and insert before it
        const firstContainer = document.querySelector('.file-upload-container');
        if (firstContainer && firstContainer.parentNode) {
            firstContainer.parentNode.insertBefore(errorContainer, firstContainer);
        }
    }
    
    errorContainer.innerHTML = `
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">Validation Error</h4>
                <div class="text-sm text-red-700 dark:text-red-400 whitespace-pre-line">${escapeHtml(message)}</div>
            </div>
            <button type="button" onclick="this.closest('#file-upload-error-container').remove()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (errorContainer && errorContainer.parentNode) {
            errorContainer.remove();
        }
    }, 10000);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Generate thumbnail for image files
function generateThumbnail(file, callback) {
    if (!file.type.startsWith('image/')) {
        callback(null);
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Calculate thumbnail size (max 100x100, maintain aspect ratio)
            const maxSize = 100;
            let width = img.width;
            let height = img.height;
            
            if (width > height) {
                if (width > maxSize) {
                    height = (height * maxSize) / width;
                    width = maxSize;
                }
            } else {
                if (height > maxSize) {
                    width = (width * maxSize) / height;
                    height = maxSize;
                }
            }
            
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);
            
            callback(canvas.toDataURL('image/jpeg', 0.8));
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Add file to list
function addFileToList(file, fileList, container) {
    const fileId = 'file-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    const icon = getFileIcon(file.name);
    const isImage = file.type.startsWith('image/');
    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center gap-4';
    fileItem.dataset.fileId = fileId;
    fileItem.dataset.fileName = file.name;
    fileItem.dataset.fileSize = file.size;
    fileItem.dataset.fileType = file.type;
    
    // Generate thumbnail for images
    generateThumbnail(file, function(thumbnail) {
        if (thumbnail) {
            const thumbnailEl = fileItem.querySelector('.file-thumbnail');
            if (thumbnailEl) {
                thumbnailEl.innerHTML = `<img src="${thumbnail}" alt="Thumbnail" class="w-full h-full object-cover rounded-lg">`;
                thumbnailEl.classList.remove('hidden');
                const iconEl = fileItem.querySelector('.file-icon-fallback');
                if (iconEl) iconEl.classList.add('hidden');
            }
        }
    });
    
    fileItem.innerHTML = `
        <!-- File Icon/Thumbnail -->
        <div class="flex-shrink-0 w-12 h-12 ${icon.bg} rounded-lg flex items-center justify-center text-white font-semibold text-xs file-icon-fallback relative overflow-hidden">
            ${icon.text}
        </div>
        <div class="flex-shrink-0 w-12 h-12 rounded-lg hidden file-thumbnail"></div>
        
        <!-- File Info -->
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${escapeHtml(file.name)}</p>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400 file-size">0 KB of ${formatFileSize(file.size)}</span>
                <span class="file-status text-xs font-medium"></span>
            </div>
            <!-- Error Message -->
            <div class="file-error hidden mt-2">
                <div class="flex items-start gap-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-700 dark:text-red-400 file-error-message"></span>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-2 hidden file-progress">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300 file-progress-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex-shrink-0 flex items-center gap-2">
            ${(isImage || isPdf) ? `
            <button type="button" onclick="previewFileFromUpload('${fileId}')" class="file-preview text-gray-400 hover:text-blue-500 transition-colors" title="Preview">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>
            ` : ''}
            <button type="button" onclick="removeFile('${fileId}')" class="file-cancel text-gray-400 hover:text-red-500 transition-colors" title="Cancel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <button type="button" onclick="retryUpload('${fileId}')" class="file-retry hidden text-gray-400 hover:text-blue-500 transition-colors" title="Retry">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            <button type="button" onclick="removeFile('${fileId}')" class="file-delete hidden text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    `;
    
    // Add to top (prepend) so newest files appear first
    fileList.insertBefore(fileItem, fileList.firstChild);
    
    // Store file reference
    window.fileUploadState.set(fileId, {
        file: file,
        element: fileItem,
        status: 'pending',
        progress: 0,
        error: null,
        retryCount: 0
    });
    
    // Simulate upload (in real implementation, this would be actual upload)
    simulateUpload(fileId);
}

// Simulate file upload (with error simulation for testing)
function simulateUpload(fileId) {
    const fileData = window.fileUploadState.get(fileId);
    if (!fileData) return;
    
    const fileItem = fileData.element;
    const statusEl = fileItem.querySelector('.file-status');
    const progressBar = fileItem.querySelector('.file-progress');
    const progressBarFill = fileItem.querySelector('.file-progress-bar');
    const sizeEl = fileItem.querySelector('.file-size');
    const cancelBtn = fileItem.querySelector('.file-cancel');
    const deleteBtn = fileItem.querySelector('.file-delete');
    const retryBtn = fileItem.querySelector('.file-retry');
    const errorEl = fileItem.querySelector('.file-error');
    const errorMsgEl = fileItem.querySelector('.file-error-message');
    
    // Clear any previous errors
    if (errorEl) errorEl.classList.add('hidden');
    if (errorMsgEl) errorMsgEl.textContent = '';
    
    // Show uploading state
    fileData.status = 'uploading';
    fileData.error = null;
    statusEl.innerHTML = '<span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Uploading...</span>';
    progressBar.classList.remove('hidden');
    cancelBtn.classList.remove('hidden');
    if (retryBtn) retryBtn.classList.add('hidden');
    if (deleteBtn) deleteBtn.classList.add('hidden');
    
    // Simulate progress with occasional failures for testing
    let progress = 0;
    const shouldFail = Math.random() < 0.1 && fileData.retryCount === 0; // 10% chance of failure on first attempt
    
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 100) progress = 100;
        
        fileData.progress = progress;
        progressBarFill.style.width = progress + '%';
        const uploadedSize = Math.round((fileData.file.size * progress) / 100);
        sizeEl.textContent = `${formatFileSize(uploadedSize)} of ${formatFileSize(fileData.file.size)}`;
        
        // Simulate failure at 50% progress
        if (shouldFail && progress >= 50 && progress < 60) {
            clearInterval(interval);
            fileData.status = 'error';
            fileData.error = 'Network error: Connection timeout. Please check your internet connection and try again.';
            
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Upload Failed</span>';
            progressBar.classList.add('hidden');
            cancelBtn.classList.add('hidden');
            if (retryBtn) retryBtn.classList.remove('hidden');
            if (errorEl) {
                errorEl.classList.remove('hidden');
                if (errorMsgEl) errorMsgEl.textContent = fileData.error;
            }
            return;
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            // Mark as completed
            fileData.status = 'completed';
            fileData.error = null;
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Completed</span>';
            sizeEl.textContent = `${formatFileSize(fileData.file.size)} of ${formatFileSize(fileData.file.size)}`;
            progressBar.classList.add('hidden');
            cancelBtn.classList.add('hidden');
            if (retryBtn) retryBtn.classList.add('hidden');
            if (deleteBtn) deleteBtn.classList.remove('hidden');
            
            // Auto-upload if this is in an auto-upload container
            const container = fileItem.closest('.file-upload-container');
            const parentSection = container ? container.closest('[data-auto-upload="true"]') : null;
            if (parentSection && parentSection.dataset.autoUpload === 'true') {
                // Check if all files are completed, then auto-upload
                setTimeout(() => {
                    checkAndAutoUpload(parentSection);
                }, 500);
            }
        }
    }, 200);
    
    // Store interval for cancellation
    fileData.uploadInterval = interval;
}

// Retry upload
window.retryUpload = function(fileId) {
    const fileData = window.fileUploadState.get(fileId);
    if (!fileData) return;
    
    fileData.retryCount = (fileData.retryCount || 0) + 1;
    
    // Clear error state
    const fileItem = fileData.element;
    const errorEl = fileItem.querySelector('.file-error');
    if (errorEl) errorEl.classList.add('hidden');
    
    // Retry upload
    simulateUpload(fileId);
}

// Preview file from upload
window.previewFileFromUpload = function(fileId) {
    const fileData = window.fileUploadState.get(fileId);
    if (!fileData || !fileData.file) return;
    
    const file = fileData.file;
    const fileUrl = URL.createObjectURL(file);
    const isImage = file.type.startsWith('image/');
    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    
    // Create preview modal if it doesn't exist
    let modal = document.getElementById('file-upload-preview-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'file-upload-preview-modal';
        modal.className = 'fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4';
        modal.onclick = function(e) {
            if (e.target === modal) closeFileUploadPreview();
        };
        document.body.appendChild(modal);
    }
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-5xl max-h-[95vh] h-[95vh] flex flex-col shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
                <div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">${escapeHtml(file.name)}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">${formatFileSize(file.size)}</p>
                </div>
                <button type="button" onclick="closeFileUploadPreview()" class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 rounded-b-lg sm:rounded-b-xl" id="file-upload-preview-content" oncontextmenu="return false;" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                ${isPdf ? `
                    <iframe src="${fileUrl}#toolbar=0&navpanes=0&scrollbar=1" class="w-full h-full min-h-[600px] rounded-lg border border-gray-200 dark:border-gray-700" frameborder="0" oncontextmenu="return false;"></iframe>
                ` : isImage ? `
                    <div class="flex items-center justify-center h-full" oncontextmenu="return false;" style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                        <img src="${fileUrl}" alt="${escapeHtml(file.name)}" class="max-w-full max-h-full object-contain rounded-lg shadow-lg pointer-events-none" draggable="false" oncontextmenu="return false;" style="user-select: none; -webkit-user-drag: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none;">
                    </div>
                ` : `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-24 h-24 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>Preview not available for this file type</p>
                        </div>
                    </div>
                `}
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// Close file upload preview
window.closeFileUploadPreview = function() {
    const modal = document.getElementById('file-upload-preview-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        
        // Revoke object URL to free memory
        const iframe = modal.querySelector('iframe');
        const img = modal.querySelector('img');
        if (iframe) {
            const src = iframe.src;
            if (src.startsWith('blob:')) {
                URL.revokeObjectURL(src);
            }
        }
        if (img) {
            const src = img.src;
            if (src.startsWith('blob:')) {
                URL.revokeObjectURL(src);
            }
        }
    }
}

// Close preview on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeFileUploadPreview();
    }
    // Prevent common download shortcuts
    if ((event.ctrlKey || event.metaKey) && (event.key === 's' || event.key === 'S')) {
        event.preventDefault();
        return false;
    }
});

// Prevent right-click context menu on preview modal
document.addEventListener('contextmenu', function(event) {
    const modal = document.getElementById('file-upload-preview-modal');
    if (modal && !modal.classList.contains('hidden')) {
        const content = document.getElementById('file-upload-preview-content');
        if (content && content.contains(event.target)) {
            event.preventDefault();
            return false;
        }
    }
}, false);

// Check if all files are completed and auto-upload
function checkAndAutoUpload(uploadSection) {
    const fileUploadContainer = uploadSection.querySelector('.file-upload-container');
    if (!fileUploadContainer) return;
    
    const fileList = fileUploadContainer.querySelector('.file-list');
    const fileItems = fileList.querySelectorAll('.file-item');
    
    if (fileItems.length === 0) return;
    
    // Check if all files are completed
    let allCompleted = true;
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (!fileData || fileData.status !== 'completed') {
            allCompleted = false;
        }
    });
    
    // Auto-upload if all files are completed and we haven't uploaded yet
    if (allCompleted && !uploadSection.dataset.uploading) {
        uploadSection.dataset.uploading = 'true';
        autoUploadAttachments(uploadSection);
    }
}

// Auto-upload attachments
async function autoUploadAttachments(uploadSection) {
    const fileUploadContainer = uploadSection.querySelector('.file-upload-container');
    if (!fileUploadContainer) return;
    
    const documentId = uploadSection.dataset.documentId;
    if (!documentId) return;
    
    const fileList = fileUploadContainer.querySelector('.file-list');
    const fileItems = fileList.querySelectorAll('.file-item');
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    
    // Add files to FormData
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            formData.append('files[]', fileData.file);
        }
    });
    
    try {
        const response = await fetch(`/documents/${documentId}/attachments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear file upload state
            fileItems.forEach(item => {
                const fileId = item.dataset.fileId;
                window.fileUploadState && window.fileUploadState.delete(fileId);
            });
            fileList.innerHTML = '';
            uploadSection.dataset.uploading = 'false';
            
            // Refresh attachments list dynamically if function exists
            if (typeof refreshAttachmentsList === 'function') {
                await refreshAttachmentsList();
            } else {
                // Fallback: reload only if refresh function doesn't exist
                window.location.reload();
            }
            
        } else {
            alert(result.message || 'Failed to upload attachments');
            uploadSection.dataset.uploading = 'false';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while uploading attachments');
        uploadSection.dataset.uploading = 'false';
    }
}

// Remove file
window.removeFile = function(fileId) {
    const fileData = window.fileUploadState.get(fileId);
    if (fileData) {
        // Cancel upload if in progress
        if (fileData.uploadInterval) {
            clearInterval(fileData.uploadInterval);
        }
        // Remove from DOM
        if (fileData.element && fileData.element.parentNode) {
            fileData.element.remove();
        }
        // Remove from state
        window.fileUploadState.delete(fileId);
    }
}

