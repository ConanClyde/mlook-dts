/**
 * Digital Signature Pad
 * Canvas-based signature capture
 */

class SignaturePad {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        
        this.ctx = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.lastX = 0;
        this.lastY = 0;
        
        this.setupCanvas();
        this.attachEvents();
    }
    
    setupCanvas() {
        // Set canvas size
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width;
        this.canvas.height = rect.height;
        
        // Set drawing style
        this.ctx.strokeStyle = '#000';
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
    }
    
    attachEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
        this.canvas.addEventListener('mouseout', () => this.stopDrawing());
        
        // Touch events
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.startDrawing(e.touches[0]);
        });
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            this.draw(e.touches[0]);
        });
        this.canvas.addEventListener('touchend', () => this.stopDrawing());
    }
    
    startDrawing(e) {
        this.isDrawing = true;
        const rect = this.canvas.getBoundingClientRect();
        this.lastX = e.clientX - rect.left;
        this.lastY = e.clientY - rect.top;
    }
    
    draw(e) {
        if (!this.isDrawing) return;
        
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        this.ctx.beginPath();
        this.ctx.moveTo(this.lastX, this.lastY);
        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        
        this.lastX = x;
        this.lastY = y;
    }
    
    stopDrawing() {
        this.isDrawing = false;
    }
    
    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }
    
    isEmpty() {
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        return !imageData.data.some(channel => channel !== 0);
    }
    
    toDataURL() {
        return this.canvas.toDataURL('image/png');
    }
}

// Global signature pad instance
let signaturePad = null;

/**
 * Open signature modal
 */
window.openSignatureModal = function(documentId) {
    const modal = document.createElement('div');
    modal.id = 'signature-modal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-2xl mx-4">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Sign Document
                </h3>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Please sign in the box below. Your signature will be securely stored and verified.
                </p>
                
                <!-- Signature Canvas -->
                <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white mb-4">
                    <canvas id="signature-canvas" class="w-full" style="height: 300px; touch-action: none;"></canvas>
                </div>
                
                <!-- Signature Type -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Signature Type
                    </label>
                    <select id="signature-type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                        <option value="digital">Digital Signature</option>
                        <option value="electronic">Electronic Signature</option>
                    </select>
                </div>
                
                <!-- Terms -->
                <div class="flex items-start mb-4">
                    <input type="checkbox" id="signature-consent" class="mt-1 w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                    <label for="signature-consent" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        I agree that this electronic signature is legally binding and has the same effect as a handwritten signature.
                    </label>
                </div>
                
                <div id="signature-error" class="hidden text-red-500 text-sm mb-4"></div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                <button onclick="clearSignature()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                    Clear
                </button>
                <div class="space-x-2">
                    <button onclick="closeSignatureModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                        Cancel
                    </button>
                    <button onclick="submitSignature(${documentId})" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors">
                        Sign Document
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Initialize signature pad
    setTimeout(() => {
        signaturePad = new SignaturePad('signature-canvas');
    }, 100);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeSignatureModal();
        }
    });
};

window.closeSignatureModal = function() {
    const modal = document.getElementById('signature-modal');
    if (modal) {
        modal.remove();
        signaturePad = null;
    }
};

window.clearSignature = function() {
    if (signaturePad) {
        signaturePad.clear();
    }
};

window.submitSignature = async function(documentId) {
    const errorDiv = document.getElementById('signature-error');
    errorDiv.classList.add('hidden');
    
    // Validate
    if (!signaturePad || signaturePad.isEmpty()) {
        errorDiv.textContent = 'Please provide a signature';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    const consent = document.getElementById('signature-consent');
    if (!consent.checked) {
        errorDiv.textContent = 'Please agree to the terms';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    const signatureType = document.getElementById('signature-type').value;
    const signatureData = signaturePad.toDataURL();
    
    try {
        const response = await fetch(`/documents/${documentId}/sign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                signature_type: signatureType,
                signature_data: signatureData,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeSignatureModal();
            location.reload();
        } else {
            errorDiv.textContent = data.message || 'Failed to save signature';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Signature error:', error);
        errorDiv.textContent = 'An error occurred while saving the signature';
        errorDiv.classList.remove('hidden');
    }
};

/**
 * Verify signature
 */
window.verifySignature = async function(signatureId) {
    try {
        const response = await fetch(`/signatures/${signatureId}/verify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.verified ? 'Signature is valid ✓' : 'Signature verification failed ✗');
        }
    } catch (error) {
        console.error('Verification error:', error);
        alert('Failed to verify signature');
    }
};

// Digital signature module loaded
