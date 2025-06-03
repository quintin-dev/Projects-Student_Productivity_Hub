/**
 * Student Productivity Hub - Service Worker
 *
 * PWA service worker for offline functionality and caching strategies
 * Implements cache-first for static assets, network-first for dynamic data
 *
 * @version 1.0.0
 * @author Edwardking (Edd)
 */

const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE = `productivity-hub-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `productivity-hub-dynamic-${CACHE_VERSION}`;
const API_CACHE = `productivity-hub-api-${CACHE_VERSION}`;

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/src/css/main.css',
    '/src/js/app.js',
    '/src/js/components/task-manager.js',
    '/src/js/components/study-timer.js',
    '/src/js/services/api.js',
    '/src/js/utils/helpers.js',
    '/src/assets/icons/icon-192x192.png',
    '/src/assets/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
];

// API endpoints to cache with network-first strategy
const API_ENDPOINTS = [
    '/api/tasks',
    '/api/categories',
    '/api/study-sessions',
    '/api/habits',
    '/api/settings',
];

// Files that should never be cached
const CACHE_BLACKLIST = ['/api/auth', '/api/debug', '/admin'];

/**
 * Service Worker Installation Event
 * Pre-cache essential static assets
 */
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker version:', CACHE_VERSION);

    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                // Force activation of new service worker
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Error during installation:', error);
            })
    );
});

/**
 * Service Worker Activation Event
 * Clean up old caches and take control
 */
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker version:', CACHE_VERSION);

    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                const cleanupPromises = cacheNames
                    .filter((cacheName) => {
                        // Remove old versions of our caches
                        return (
                            cacheName.startsWith('productivity-hub-') &&
                            !cacheName.includes(CACHE_VERSION)
                        );
                    })
                    .map((cacheName) => {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    });

                return Promise.all(cleanupPromises);
            })
            .then(() => {
                // Take control of all clients immediately
                return self.clients.claim();
            })
            .catch((error) => {
                console.error('[SW] Error during activation:', error);
            })
    );
});

/**
 * Fetch Event Handler
 * Implement caching strategies based on request type
 */
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and blacklisted URLs
    if (request.method !== 'GET' || shouldSkipCache(url.pathname)) {
        return;
    }

    // Handle different types of requests with appropriate strategies
    if (isApiRequest(url.pathname)) {
        event.respondWith(handleApiRequest(request));
    } else if (isStaticAsset(url.pathname)) {
        event.respondWith(handleStaticAsset(request));
    } else {
        event.respondWith(handlePageRequest(request));
    }
});

/**
 * Handle API requests with network-first strategy
 * Falls back to cache if network fails
 */
async function handleApiRequest(request) {
    try {
        console.log('[SW] API request (network-first):', request.url);

        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cache successful API responses
            const cache = await caches.open(API_CACHE);
            cache.put(request.url, networkResponse.clone());
            return networkResponse;
        }

        throw new Error(`Network response not ok: ${networkResponse.status}`);
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', error.message);

        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page for API failures
        return new Response(
            JSON.stringify({
                error: 'Network unavailable',
                message: 'Please check your connection and try again',
                offline: true,
            }),
            {
                status: 503,
                headers: { 'Content-Type': 'application/json' },
            }
        );
    }
}

/**
 * Handle static assets with cache-first strategy
 * Fast loading for CSS, JS, images, fonts
 */
async function handleStaticAsset(request) {
    console.log('[SW] Static asset (cache-first):', request.url);

    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request.url, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.error('[SW] Failed to fetch static asset:', error);

        // Return a fallback for critical assets
        if (request.url.includes('.css')) {
            return new Response('/* Offline fallback CSS */', {
                headers: { 'Content-Type': 'text/css' },
            });
        }

        throw error;
    }
}

/**
 * Handle page requests with network-first, cache fallback
 * Ensures fresh content when online
 */
async function handlePageRequest(request) {
    console.log('[SW] Page request (network-first):', request.url);

    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request.url, networkResponse.clone());
            return networkResponse;
        }

        throw new Error(`Network response not ok: ${networkResponse.status}`);
    } catch (error) {
        console.log('[SW] Network failed for page, trying cache');

        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page as last resort
        return (
            caches.match('/') ||
            new Response('Offline', {
                status: 503,
                headers: { 'Content-Type': 'text/html' },
            })
        );
    }
}

/**
 * Background Sync Event
 * Handle queued operations when connection is restored
 */
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync event:', event.tag);

    if (event.tag === 'task-sync') {
        event.waitUntil(syncTasks());
    } else if (event.tag === 'study-session-sync') {
        event.waitUntil(syncStudySessions());
    }
});

/**
 * Push Notification Event
 * Handle push notifications for study reminders
 */
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');

    const options = {
        body: event.data
            ? event.data.text()
            : 'Time for your next study session!',
        icon: '/src/assets/icons/icon-192x192.png',
        badge: '/src/assets/icons/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: event.data ? JSON.parse(event.data.text()) : {},
        actions: [
            {
                action: 'start-session',
                title: 'Start Session',
                icon: '/src/assets/icons/action-start.png',
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/src/assets/icons/action-dismiss.png',
            },
        ],
    };

    event.waitUntil(
        self.registration.showNotification('Productivity Hub', options)
    );
});

/**
 * Notification Click Event
 * Handle user interactions with notifications
 */
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event.action);

    event.notification.close();

    if (event.action === 'start-session') {
        event.waitUntil(clients.openWindow('/?action=study-timer'));
    }
});

/**
 * Helper Functions
 */

function shouldSkipCache(pathname) {
    return CACHE_BLACKLIST.some((pattern) => pathname.includes(pattern));
}

function isApiRequest(pathname) {
    return pathname.startsWith('/api/');
}

function isStaticAsset(pathname) {
    return (
        pathname.includes('.css') ||
        pathname.includes('.js') ||
        pathname.includes('.png') ||
        pathname.includes('.jpg') ||
        pathname.includes('.svg') ||
        pathname.includes('.woff') ||
        pathname.includes('.woff2') ||
        pathname.includes('fonts.googleapis.com')
    );
}

async function syncTasks() {
    // Implement task synchronization logic
    console.log('[SW] Syncing tasks...');
    // This will be implemented in Phase 2
}

async function syncStudySessions() {
    // Implement study session synchronization logic
    console.log('[SW] Syncing study sessions...');
    // This will be implemented in Phase 2
}
