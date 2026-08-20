/**
 * WarmUp Gym Management — Service Worker
 * Phase 14: Offline-First Support
 *
 * Strategy:
 *  - App Shell (HTML, CSS, JS): Cache-first, fallback to network
 *  - API requests (/api/*): Network-first, no caching (data must be fresh)
 *  - Offline page: Served when network is unavailable and no cache hit
 */

const CACHE_NAME   = 'warmup-shell-v1';
const OFFLINE_URL  = '/offline.html';

// Resources to pre-cache on install (app shell)
const SHELL_ASSETS = [
    OFFLINE_URL,
];

// ─── Install ───────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(SHELL_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ─── Activate ─────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET requests and API calls (let them fail naturally / be queued by client)
    if (event.request.method !== 'GET') return;
    if (url.pathname.startsWith('/api/')) return;

    // For navigation requests, try network first, fallback to offline page
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // For other GET requests: network-first with cache fallback
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cache successful responses for static assets
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});
