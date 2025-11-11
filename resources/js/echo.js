import Echo from 'laravel-echo';
import axios from 'axios';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Configure axios to send credentials (cookies) with requests
axios.defaults.withCredentials = true;

// Get CSRF token dynamically - this function is called on each auth request
const getCsrfToken = () => {
    // Try meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Fallback: try to get from form input
    const tokenInput = document.querySelector('input[name="_token"]');
    if (tokenInput) {
        return tokenInput.value;
    }
    
    return null;
};

// Only initialize Echo if broadcasting is enabled
function initializeEcho() {
    const key = import.meta.env.VITE_REVERB_APP_KEY || window.REVERB_APP_KEY;
    const wsHost = import.meta.env.VITE_REVERB_HOST || window.REVERB_HOST || '127.0.0.1';
    const wsPort = import.meta.env.VITE_REVERB_PORT || window.REVERB_PORT || 8080;
    const wssPort = import.meta.env.VITE_REVERB_PORT || window.REVERB_PORT || 8080;
    const scheme = import.meta.env.VITE_REVERB_SCHEME || window.REVERB_SCHEME || 'http';
    
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: key,
        wsHost: wsHost,
        wsPort: parseInt(wsPort) || 80,
        wssPort: parseInt(wssPort) || 443,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        disabledTransports: [],
        enableStats: false,
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    const csrfToken = getCsrfToken();
                    
                    axios.post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel.name
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        withCredentials: true
                    })
                    .then(response => {
                        callback(false, response.data);
                    })
                    .catch(error => {
                        console.error('Broadcasting auth error:', error);
                        callback(true, error);
                    });
                }
            };
        },
    });
    
    window.Echo._subscribedChannels = new Set();
    
    const originalPrivate = window.Echo.private.bind(window.Echo);
    const originalChannel = window.Echo.channel.bind(window.Echo);
    
    window.Echo.private = function(channelName) {
        window.Echo._subscribedChannels.add({ type: 'private', name: channelName });
        return originalPrivate(channelName);
    };
    
    window.Echo.channel = function(channelName) {
        window.Echo._subscribedChannels.add({ type: 'channel', name: channelName });
        return originalChannel(channelName);
    };
}

// Check for Reverb key and initialize
let reverbKey = import.meta.env.VITE_REVERB_APP_KEY || window.REVERB_APP_KEY;

if (reverbKey && reverbKey !== 'null' && reverbKey !== '') {
    initializeEcho();
} else if (!import.meta.env.VITE_REVERB_APP_KEY) {
    // Wait for window.REVERB_APP_KEY to be set by Blade template
    const checkInterval = setInterval(() => {
        reverbKey = window.REVERB_APP_KEY;
        if (reverbKey && reverbKey !== 'null' && reverbKey !== '') {
            clearInterval(checkInterval);
            initializeEcho();
        }
    }, 50);
    
    setTimeout(() => {
        clearInterval(checkInterval);
        if (!window.Echo || window.Echo._isDummy) {
            window.Echo = {
                channel: () => ({ listen: () => {} }),
                private: () => ({ listen: () => {} }),
                join: () => ({ listen: () => {} }),
                leave: () => {},
                disconnect: () => {},
                _isDummy: true,
            };
        }
    }, 2000);
} else {
    // Create a dummy Echo instance to prevent errors
    window.Echo = {
        channel: () => ({ listen: () => {} }),
        private: () => ({ listen: () => {} }),
        join: () => ({ listen: () => {} }),
        leave: () => {},
        disconnect: () => {},
        _isDummy: true,
    };
}
