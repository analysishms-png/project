/*
 * Analysis HMS — Service Worker
 * Provides offline caching, push notifications, and background sync
 */

const CACHE_NAME = 'analysis-hms-v1';
const CACHE_VERSION = '1.0.0';

// Assets to pre-cache (critical app shell)
const PRECACHE_ASSETS = [
    '/',
    '/admin/css/style.css',
    '/admin/css/hms.css',
    '/admin/plugins/jquery/jquery.min.js',
    '/admin/plugins/bootstrap/js/bootstrap.bundle.min.js',
    '/admin/images/favicon.png',
    '/admin/images/pwa-192.png',
    '/admin/images/pwa-512.png',
    '/offline.html',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'
];

// API endpoints to cache dynamically
const API_CACHE_PATTERNS = [
    /\/api\/dashboard/,
    /\/api\/roomstatus/,
    /\/api\/availability/,
    /\/fetchdatewiseemptyroomcat/,
    /\/get\-/,
];

// ═══════════════════════════════════════════════════════════════
// INSTALL — Pre-cache critical assets
// ═══════════════════════════════════════════════════════════════

self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Pre-caching app shell');
                return cache.addAll(PRECACHE_ASSETS.map(url => {
                    return new Request(url, { mode: 'cors' });
                })).catch(err => {
                    console.log('[SW] Some assets failed to cache:', err);
                });
            })
            .then(() => self.skipWaiting())
    );
});

// ═══════════════════════════════════════════════════════════════
// ACTIVATE — Clean old caches
// ═══════════════════════════════════════════════════════════════

self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// ═══════════════════════════════════════════════════════════════
// FETCH — Network-first for API, Cache-first for assets
// ═══════════════════════════════════════════════════════════════

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip chrome-extension and other non-http
    if (!url.protocol.startsWith('http')) return;

    // Strategy: API calls → Network-first (with cache fallback)
    if (isApiRequest(url)) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Strategy: Static assets → Cache-first
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Strategy: Navigation → Network-first (with offline fallback)
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstWithOffline(request));
        return;
    }

    // Strategy: Everything else → Stale-while-revalidate
    event.respondWith(staleWhileRevalidate(request));
});

// ═══════════════════════════════════════════════════════════════
// CACHING STRATEGIES
// ═══════════════════════════════════════════════════════════════

// Cache-first: Best for static assets that don't change
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        return new Response('Offline', { status: 503 });
    }
}

// Network-first: Best for API calls that need fresh data
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        return new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Network-first with offline page: Best for navigation
async function networkFirstWithOffline(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        return caches.match('/offline.html');
    }
}

// Stale-while-revalidate: Serve cached, update in background
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);
    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            const cache = caches.open(CACHE_NAME);
            cache.then(c => c.put(request, response.clone()));
        }
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}

// ═══════════════════════════════════════════════════════════════
// URL CLASSIFIERS
// ═══════════════════════════════════════════════════════════════

function isApiRequest(url) {
    return url.pathname.startsWith('/api/') ||
           url.pathname.startsWith('/fetch') ||
           url.pathname.startsWith('/get-') ||
           url.search.has('ajax');
}

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/.test(url.pathname) ||
           url.pathname.includes('/admin/') ||
           url.pathname.includes('/plugins/') ||
           url.pathname.includes('/fonts/');
}

// ═══════════════════════════════════════════════════════════════
// PUSH NOTIFICATIONS
// ═══════════════════════════════════════════════════════════════

self.addEventListener('push', (event) => {
    console.log('[SW] Push received');

    let data = {
        title: 'Analysis HMS',
        body: 'New notification',
        icon: '/admin/images/pwa-192.png',
        badge: '/admin/images/pwa-192.png',
        data: { url: '/' }
    };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/admin/images/pwa-192.png',
        badge: data.badge || '/admin/images/pwa-192.png',
        vibrate: [200, 100, 200],
        tag: data.tag || 'analysis-hms-notification',
        renotify: true,
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Open', icon: '/admin/images/pwa-192.png' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// ═══════════════════════════════════════════════════════════════
// NOTIFICATION CLICK
// ═══════════════════════════════════════════════════════════════

self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked');
    event.notification.close();

    if (event.action === 'dismiss') return;

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Focus existing window if open
                for (const client of clientList) {
                    if (client.url.includes(url) && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// ═══════════════════════════════════════════════════════════════
// BACKGROUND SYNC (for offline form submissions)
// ═══════════════════════════════════════════════════════════════

self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);

    if (event.tag === 'sync-offline-data') {
        event.waitUntil(syncOfflineData());
    }
});

async function syncOfflineData() {
    // Read from IndexedDB and sync when online
    try {
        const db = await openDB();
        const tx = db.transaction('offline-queue', 'readonly');
        const store = tx.objectStore('offline-queue');
        const requests = await getAllFromStore(store);

        for (const req of requests) {
            try {
                await fetch(req.url, {
                    method: req.method,
                    headers: req.headers,
                    body: req.body
                });
                // Remove from queue after successful sync
                await removeFromStore('offline-queue', req.id);
            } catch (e) {
                console.log('[SW] Sync failed for:', req.url);
            }
        }
    } catch (e) {
        console.log('[SW] Background sync error:', e);
    }
}

// Simple IndexedDB helpers
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('analysis-hms-offline', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('offline-queue')) {
                db.createObjectStore('offline-queue', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getAllFromStore(store) {
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function removeFromStore(storeName, id) {
    return new Promise((resolve, reject) => {
        const dbReq = indexedDB.open('analysis-hms-offline', 1);
        dbReq.onsuccess = (event) => {
            const db = event.target.result;
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            store.delete(id);
            tx.oncomplete = () => resolve();
        };
        dbReq.onerror = () => reject(dbReq.error);
    });
}
