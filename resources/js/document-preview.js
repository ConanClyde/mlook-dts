/**
 * Document Preview with PDF.js
 * In-browser document preview without downloading
 */

// Initialize PDF.js viewer
window.initDocumentPreview = function() {
    // Add preview buttons to document cards
    const documentCards = document.querySelectorAll('[data-document-id]');
    
    documentCards.forEach(card => {
        const documentId = card.dataset.documentId;
        const filePath = card.dataset.filePath;
        
        if (!filePath) return;
        
        // Add preview button if file is previewable
        if (isPreviewable(filePath)) {
            addPreviewButton(card, documentId, filePath);
        }
    });
};

function isPreviewable(filePath) {
    const ext = filePath.split('.').pop().toLowerCase();
    return ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
}

function addPreviewButton(card, documentId, filePath) {
    const actionsDiv = card.querySelector('.document-actions');
    if (!actionsDiv) return;
    
    const previewBtn = document.createElement('button');
    previewBtn.className = 'px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm transition-colors flex items-center gap-2';
    previewBtn.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        Preview
    `;
    
    previewBtn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openPreviewModal(documentId, filePath);
    };
    
    actionsDiv.appendChild(previewBtn);
}

function openPreviewModal(documentId, filePath) {
    const modal = document.createElement('div');
    modal.id = 'preview-modal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75';
    
    const ext = filePath.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
    const isPDF = ext === 'pdf';
    
    modal.innerHTML = `
        <div class="relative w-full h-full max-w-7xl max-h-screen p-4 flex flex-col">
            <!-- Header -->
            <div class="bg-gray-900 text-white px-6 py-4 rounded-t-lg flex items-center justify-between">
                <h3 class="text-lg font-semibold">Document Preview</h3>
                <div class="flex items-center gap-3">
                    ${isPDF ? `
                        <button onclick="zoomOut()" class="p-2 hover:bg-gray-700 rounded">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                            </svg>
                        </button>
                        <button onclick="zoomIn()" class="p-2 hover:bg-gray-700 rounded">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </button>
                        <span id="zoom-level" class="text-sm">100%</span>
                    ` : ''}
                    <a href="/storage/${filePath}" download class="p-2 hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </a>
                    <button onclick="closePreviewModal()" class="p-2 hover:bg-gray-700 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-b-lg overflow-auto">
                <div id="preview-content" class="w-full h-full flex items-center justify-center p-4">
                    ${isImage ? `
                        <img src="/storage/${filePath}" 
                             alt="Document preview" 
                             class="max-w-full max-h-full object-contain shadow-2xl"
                             style="image-rendering: -webkit-optimize-contrast;">
                    ` : isPDF ? `
                        <div class="w-full h-full bg-white">
                            <iframe src="/storage/${filePath}" 
                                    class="w-full h-full border-0"
                                    type="application/pdf">
                            </iframe>
                        </div>
                    ` : `
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg mb-2">Preview not available</p>
                            <p class="text-sm">This file type cannot be previewed in the browser</p>
                            <a href="/storage/${filePath}" download class="mt-4 inline-block px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg">
                                Download File
                            </a>
                        </div>
                    `}
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closePreviewModal();
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            closePreviewModal();
            document.removeEventListener('keydown', escHandler);
        }
    });
}

window.closePreviewModal = function() {
    const modal = document.getElementById('preview-modal');
    if (modal) {
        modal.remove();
    }
};

// Zoom controls for PDF
let currentZoom = 100;

window.zoomIn = function() {
    currentZoom = Math.min(currentZoom + 25, 200);
    updateZoom();
};

window.zoomOut = function() {
    currentZoom = Math.max(currentZoom - 25, 50);
    updateZoom();
};

function updateZoom() {
    const iframe = document.querySelector('#preview-content iframe');
    const zoomLevel = document.getElementById('zoom-level');
    
    if (iframe) {
        iframe.style.transform = `scale(${currentZoom / 100})`;
        iframe.style.transformOrigin = 'top left';
    }
    
    if (zoomLevel) {
        zoomLevel.textContent = currentZoom + '%';
    }
}

// Quick preview on hover (optional)
window.enableQuickPreview = function() {
    let previewTimeout;
    
    document.querySelectorAll('[data-document-id]').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const filePath = this.dataset.filePath;
            if (!filePath || !isPreviewable(filePath)) return;
            
            previewTimeout = setTimeout(() => {
                showQuickPreview(this, filePath);
            }, 1000); // Show after 1 second hover
        });
        
        card.addEventListener('mouseleave', function() {
            clearTimeout(previewTimeout);
            hideQuickPreview();
        });
    });
};

function showQuickPreview(element, filePath) {
    const ext = filePath.split('.').pop().toLowerCase();
    if (!['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return;
    
    const preview = document.createElement('div');
    preview.id = 'quick-preview';
    preview.className = 'fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-2 border-2 border-orange-500';
    preview.style.maxWidth = '400px';
    preview.style.maxHeight = '400px';
    
    preview.innerHTML = `
        <img src="/storage/${filePath}" 
             alt="Quick preview" 
             class="max-w-full max-h-full object-contain rounded">
    `;
    
    document.body.appendChild(preview);
    
    // Position near cursor
    const rect = element.getBoundingClientRect();
    preview.style.left = (rect.right + 10) + 'px';
    preview.style.top = rect.top + 'px';
}

function hideQuickPreview() {
    const preview = document.getElementById('quick-preview');
    if (preview) {
        preview.remove();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initDocumentPreview();
});

// Document preview initialized
