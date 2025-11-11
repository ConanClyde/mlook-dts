/**
 * Documents Real-time Broadcasting Listeners
 * Handles real-time updates for document-related events
 */

// Check if Echo is properly initialized (not dummy)
function isEchoInitialized() {
    // Check for dummy flag first
    if (window.Echo && window.Echo._isDummy) {
        return false;
    }
    
    // Check if Echo and connector exist
    if (!window.Echo || !window.Echo.connector) {
        return false;
    }
    
    // For Reverb/Pusher, check pusher connection
    if (window.Echo.connector.pusher) {
        return window.Echo.connector.pusher.connection !== undefined;
    }
    
    // Fallback: check if connector has connection property
    return window.Echo.connector.connection !== undefined;
}

// Global documents broadcasting setup used by multiple documents pages
window.setupDocumentsBroadcastingListeners = function() {
    if (!isEchoInitialized()) {
        // Try again shortly until Echo is ready
        if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('connecting', 'Connecting...');
        setTimeout(window.setupDocumentsBroadcastingListeners, 100);
        return;
    }

    if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('connecting', 'Setting up...');

    try {
        // Listen for document events on private 'documents' channel
        const channel = window.Echo.private('documents')
            // mark success only when actually subscribed
            .subscribed(() => {
                window.__documentsSubscribed = 'private';
                window.__documentsSubscriptionChecked = true;
                if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('connected', 'Live Updates');
            })
            // handle subscription/auth errors
            .error((status) => {
                window.__documentsSubscriptionChecked = true;
                if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('error', 'Realtime unavailable');
                // Fallback to public for minimal updates (created/updated/deleted/expired)
                try {
                    window.Echo.channel('documents')
                        .listen('.document.created', () => {
                            if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                        })
                        .listen('.document.updated', () => {
                            if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                        })
                        .listen('.document.deleted', () => {
                            if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                        })
                        .listen('.document.expired', (e) => {
                            window.dispatchAlpineEvent('document-expired', e);
                        });
                    if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('connected', 'Live Updates');
                    window.__documentsSubscribed = 'public';
                } catch (err) {
                    // ignore
                }
            })
            .listen('.document.created', (e) => {
                // Delay to ensure DB transaction is committed and visible
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 500);
            })
            .listen('.document.updated', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.forwarded', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.received', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.completed', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.approved', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.rejected', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.returned', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            })
            .listen('.document.deleted', (e) => {
                setTimeout(() => {
                    if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                }, 200);
            });

        // Note: we now set __documentsSubscribed only on 'subscription_succeeded'

        // Safety fallback: if no events handled within a short window, try public channel
        if (!window.__documentsSubscriptionChecked) {
            window.__documentsSubscriptionChecked = true;
            setTimeout(() => {
                // If Echo exists but we haven't seen any subscription status, fallback
                if (!window.__documentsSubscribed) {
                    try {
                        window.Echo.channel('documents')
                            .listen('.document.created', () => {
                                if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                            })
                            .listen('.document.updated', () => {
                                if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                            })
                            .listen('.document.deleted', () => {
                                if (typeof window.refreshDocumentsTable === 'function') window.refreshDocumentsTable();
                            })
                            .listen('.document.expired', (e) => {
                                window.dispatchAlpineEvent('document-expired', e);
                            });
                        if (typeof window.updateBroadcastStatus === 'function') window.updateBroadcastStatus('connected', 'Live Updates');
                        window.__documentsSubscribed = 'public';
                        // fallback subscribed
                    } catch (err) {
                        // ignore
                    }
                }
            }, 800);
        }
    } catch (error) {
        // ignore
    }
};

// Document Version Channel - Listen for new versions (private channel)
// This will be subscribed to dynamically when viewing a document
window.subscribeToDocumentVersions = function(documentId) {
    if (!isEchoInitialized()) {
        return;
    }
    
    try {
        window.Echo.private(`document.${documentId}`)
            .listen('.version.created', (e) => {
                // Update version list if on document page
                if (document.getElementById('version-list')) {
                    window.reloadAlpineComponent('version-list', 'loadVersions');
                }
                
                // Show badge notification
                const versionBadge = document.getElementById('new-version-badge');
                if (versionBadge) {
                    versionBadge.classList.remove('hidden');
                    versionBadge.textContent = 'New version available!';
                }
            })
            .listen('.signature.created', (e) => {
                // Signature component will handle the update via its own listener
            })
            .listen('.signature.deleted', (e) => {
                // Signature component will handle the update via its own listener
                window.dispatchAlpineEvent('signature-deleted', e);
            })
            .listen('.document.encrypted', (e) => {
                // Update document status
                window.dispatchAlpineEvent('document-encrypted', e);
            })
            .listen('.document.watermarked', (e) => {
                // Update document status
                window.dispatchAlpineEvent('document-watermarked', e);
            })
            .listen('.document.expired', (e) => {
                window.dispatchAlpineEvent('document-expired', e);
            });

        // Also listen on public channel for expired events
        try {
            window.Echo.channel(`document.${documentId}`)
                .listen('.document.expired', (e) => {
                    window.dispatchAlpineEvent('document-expired', e);
                });
        } catch (err) {
            // ignore
        }
    } catch (error) {
        console.error('Failed to subscribe to document channel:', error);
    }
};

// Listen for document expiration on public channel (for all users)
window.setupDocumentExpirationListener = function() {
    if (!isEchoInitialized()) {
        setTimeout(window.setupDocumentExpirationListener, 100);
        return;
    }

    try {
        window.Echo.channel('documents')
            .listen('.document.expired', (e) => {
                // Update documents list if on documents page
                if (window.location.href.includes('/documents') && !window.location.href.includes('/documents/')) {
                    if (typeof window.refreshDocumentsTable === 'function') {
                        window.refreshDocumentsTable();
                    }
                }
                window.dispatchAlpineEvent('document-expired', e);
            });
        
        // listener ready
    } catch (error) {
        // ignore
    }
};

// Auto-initialize document expiration listener on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.setupDocumentExpirationListener);
} else {
    window.setupDocumentExpirationListener();
}
