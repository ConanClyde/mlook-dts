@props(['document'])

<div x-data="documentSignatures({{ $document->id }})" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 mb-4">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Digital Signatures</h3>
        @if(!in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]) && (auth()->user()->isAdmin() || $document->current_holder_id === auth()->id() || $document->created_by === auth()->id()))
        <button @click="openSignModal" class="inline-flex items-center px-3 sm:px-4 py-2 min-h-[44px] bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors w-full sm:w-auto justify-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
            Sign Document
        </button>
        @endif
    </div>

    <!-- Signatures List -->
    <div class="space-y-3">
        <template x-if="loading">
            <div class="text-center py-4">
                <svg class="animate-spin h-8 w-8 mx-auto text-orange-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </template>

        <template x-if="!loading && signatures.length === 0">
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                <p class="mt-2">No signatures yet</p>
            </div>
        </template>

        <template x-for="signature in signatures" :key="signature.id">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="text-sm sm:text-base font-medium text-gray-900 dark:text-white" x-text="signature.user.first_name + ' ' + signature.user.last_name"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                              :class="{
                                  'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': signature.signature_type === 'digital',
                                  'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': signature.signature_type === 'electronic',
                                  'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': signature.signature_type === 'wet'
                              }"
                              x-text="signature.signature_type.charAt(0).toUpperCase() + signature.signature_type.slice(1)">
                        </span>
                        <span x-show="signature.is_verified" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    </div>
                    
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-1 break-words">
                        <span x-text="signature.user.email"></span>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-gray-500 dark:text-gray-500">
                        <span x-text="'Signed: ' + new Date(signature.signed_at).toLocaleString()"></span>
                        <span x-show="signature.verified_at" x-text="'Verified: ' + new Date(signature.verified_at).toLocaleString()"></span>
                    </div>
                    
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                        <span x-text="'IP: ' + signature.ip_address"></span>
                    </div>
                    
                    <!-- Signature Image -->
                    <div class="mt-3 p-3 sm:p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded w-full sm:w-64 h-48 sm:h-64">
                        <img :src="signature.signature_data" alt="Signature" class="w-full h-full object-contain">
                    </div>
                </div>
                
                @if(!in_array('{{ $document->status }}', ['{{ \App\Models\Document::STATUS_COMPLETED }}', '{{ \App\Models\Document::STATUS_APPROVED }}', '{{ \App\Models\Document::STATUS_REJECTED }}', '{{ \App\Models\Document::STATUS_ARCHIVED }}']))
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @if($document->current_holder_id === auth()->id() || $document->created_by === auth()->id() || auth()->user()->isAdmin())
                    <button x-show="!signature.is_verified" @click="verifySignature(signature.id)" class="inline-flex items-center justify-center px-3 py-1.5 min-h-[44px] bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-medium rounded-lg transition-colors flex-1 sm:flex-none">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verify
                    </button>
                    @endif
                    
                    @if(auth()->user()->isAdmin() || $document->created_by === auth()->id())
                    <button @click="openDeleteModal(signature.id)" class="inline-flex items-center justify-center px-3 py-1.5 min-h-[44px] min-w-[44px] bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </template>
    </div>

    <!-- Delete Signature Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-cloak 
         @click="closeDeleteModal"
         class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md shadow-xl">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl">
                <h3 class="text-lg sm:text-xl font-semibold text-red-600 dark:text-red-400">Delete Signature</h3>
                <button @click="closeDeleteModal" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="mx-auto flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Are you sure you want to delete this signature? This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700">
                <button @click="closeDeleteModal" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="confirmDelete" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-colors">
                    Delete Signature
                </button>
            </div>
        </div>
    </div>

    <!-- Sign Document Modal -->
    <div x-show="showSignModal" x-cloak @click="closeSignModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 dark:text-white">Sign Document</h3>
                <button @click="closeSignModal" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="signDocument" class="p-4 sm:p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Signature Type</label>
                        <select x-model="signatureType" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition min-h-[44px]">
                            <option value="digital">Digital Signature</option>
                            <option value="electronic">Electronic Signature</option>
                            <option value="wet">Wet Signature (Scanned)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Your Signature</label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 sm:p-4">
                            <canvas id="signatureCanvas" @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing" @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="stopDrawing" class="w-full aspect-square bg-white dark:bg-gray-700 rounded cursor-crosshair touch-none" style="min-height: 200px;"></canvas>
                        </div>
                        <button type="button" @click="clearSignature" class="mt-2 text-sm text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium min-h-[44px] px-3 py-2">Clear Signature</button>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                        <p class="text-xs sm:text-sm text-blue-800 dark:text-blue-200">
                            <strong>Note:</strong> By signing this document, you certify that you have reviewed and approve its contents. This signature is legally binding.
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-6">
                    <button type="submit" :disabled="signing" class="flex-1 px-4 py-2.5 min-h-[44px] bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg transition-colors">
                        <span x-show="!signing">Sign Document</span>
                        <span x-show="signing">Signing...</span>
                    </button>
                    <button type="button" @click="closeSignModal" class="flex-1 sm:flex-none px-4 py-2.5 min-h-[44px] bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function documentSignatures(documentId) {
    return {
        documentId: documentId,
        signatures: [],
        loading: true,
        showSignModal: false,
        showDeleteModal: false,
        signatureToDelete: null,
        signing: false,
        signatureType: 'digital',
        canvas: null,
        ctx: null,
        isDrawing: false,
        
        async init() {
            await this.loadSignatures();
            this.setupCanvas();
            this.setupBroadcasting();
        },
        
        setupBroadcasting() {
            // Listen for real-time signature updates
            if (window.Echo) {
                window.Echo.private(`document.${this.documentId}`)
                    .listen('.signature.created', (e) => {
                        console.log('✍️ Signature created via broadcast:', e);
                        this.loadSignatures();
                    })
                    .listen('.signature.verified', (e) => {
                        console.log('✅ Signature verified via broadcast:', e);
                        this.loadSignatures();
                    })
                    .listen('.signature.deleted', (e) => {
                        console.log('🗑️ Signature deleted via broadcast:', e);
                        this.removeSignatureFromUI(e.signature_id);
                    });
            }
            
            // Also listen for custom events dispatched from show.blade.php
            window.addEventListener('signature-created', (event) => {
                console.log('✍️ Signature created event received:', event.detail);
                this.loadSignatures();
            });
            
            window.addEventListener('signature-verified', (event) => {
                console.log('✅ Signature verified event received:', event.detail);
                this.loadSignatures();
            });
            
            window.addEventListener('signature-deleted', (event) => {
                console.log('🗑️ Signature deleted event received:', event.detail);
                this.removeSignatureFromUI(event.detail.signature_id);
            });
        },
        
        setupCanvas() {
            this.$watch('showSignModal', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.initCanvas();
                    });
                }
            });
        },
        
        async loadSignatures() {
            this.loading = true;
            try {
                const response = await fetch(`/documents/${this.documentId}/signatures`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.signatures = data.signatures;
                }
            } catch (error) {
                console.error('Error loading signatures:', error);
            } finally {
                this.loading = false;
            }
        },
        
        openSignModal() {
            this.showSignModal = true;
        },
        
        closeSignModal() {
            this.showSignModal = false;
        },
        
        initCanvas() {
            this.canvas = document.getElementById('signatureCanvas');
            if (this.canvas) {
                this.ctx = this.canvas.getContext('2d');
                this.canvas.width = this.canvas.offsetWidth;
                this.canvas.height = this.canvas.offsetHeight;
                this.ctx.strokeStyle = '#000';
                this.ctx.lineWidth = 2;
                this.ctx.lineCap = 'round';
            }
        },
        
        startDrawing(e) {
            this.isDrawing = true;
            const rect = this.canvas.getBoundingClientRect();
            const x = e.clientX ? e.clientX : e.touches[0].clientX;
            const y = e.clientY ? e.clientY : e.touches[0].clientY;
            this.ctx.beginPath();
            this.ctx.moveTo(x - rect.left, y - rect.top);
        },
        
        draw(e) {
            if (!this.isDrawing) return;
            const rect = this.canvas.getBoundingClientRect();
            const x = e.clientX ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : e.changedTouches[0].clientX);
            const y = e.clientY ? e.clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : e.changedTouches[0].clientY);
            this.ctx.lineTo(x - rect.left, y - rect.top);
            this.ctx.stroke();
        },
        
        stopDrawing() {
            this.isDrawing = false;
        },
        
        handleTouchStart(e) {
            e.preventDefault();
            this.startDrawing(e);
        },
        
        handleTouchMove(e) {
            e.preventDefault();
            this.draw(e);
        },
        
        clearSignature() {
            if (this.ctx && this.canvas) {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            }
        },
        
        async signDocument() {
            if (!this.canvas) return;
            
            const signatureData = this.canvas.toDataURL();
            
            this.signing = true;
            try {
                const response = await fetch(`/documents/${this.documentId}/signatures`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        signature_type: this.signatureType,
                        signature_data: signatureData
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.closeSignModal();
                    await this.loadSignatures();
                    window.showNotification('Document signed successfully!', 'success');
                } else {
                    window.showNotification('Error signing document', 'error');
                }
            } catch (error) {
                window.showNotification('Error signing document', 'error');
            } finally {
                this.signing = false;
            }
        },
        
        async verifySignature(signatureId) {
            try {
                const response = await fetch(`/signatures/${signatureId}/verify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await this.loadSignatures();
                    window.showNotification(
                        data.verified ? 'Signature verified successfully!' : 'Signature verification failed',
                        data.verified ? 'success' : 'error'
                    );
                }
            } catch (error) {
                window.showNotification('Error verifying signature', 'error');
            }
        },
        
        openDeleteModal(signatureId) {
            this.signatureToDelete = signatureId;
            this.showDeleteModal = true;
        },
        
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.signatureToDelete = null;
        },
        
        async confirmDelete() {
            if (!this.signatureToDelete) return;
            
            try{
                const response = await fetch(`/signatures/${this.signatureToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.closeDeleteModal();
                    // Remove from UI immediately (broadcast will update other users)
                    this.removeSignatureFromUI(this.signatureToDelete);
                } else {
                    window.showNotification(data.message || 'Error deleting signature', 'error');
                }
            } catch (error) {
                console.error('Error deleting signature:', error);
                window.showNotification('Error deleting signature', 'error');
            }
        },
        
        removeSignatureFromUI(signatureId) {
            // Remove signature from the array
            this.signatures = this.signatures.filter(sig => sig.id !== signatureId);
            
            // If no signatures left, show empty state
            if (this.signatures.length === 0) {
                // The template should handle empty state automatically
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
