// public/sw.js — static app-shell cache + offline navigation fallback.
// Precaches only stable, non-hashed assets (manifest, icons). Everything else —
// the Vite build output and vendor scripts — is cached opportunistically at runtime
// the first time this browser actually fetches it while online, and served cache-first
// after that. This avoids needing to know Vite's hashed filenames in advance, and avoids
// ever permanently caching a redirected or unauthenticated response as the offline page.
const SHELL_CACHE = 'sword-shell-v2';
const PRECACHE_URLS = [
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== SHELL_CACHE).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Runtime cache-first for the Vite build output and vendor scripts — these are the
    // assets the Offline Reader actually needs to render, and this works regardless of
    // Vite's hashed filenames since we cache whatever this browser actually requests.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/js/vendor/')) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(SHELL_CACHE).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    if (event.request.mode !== 'navigate') {
        return; // Only the asset types above and page navigations are handled — API/data requests pass through untouched.
    }

    // Network-first for navigations: whenever a page load succeeds while online, refresh
    // the cached copy (so the Offline Reader cache always reflects the current,
    // authenticated version of the page, not a stale or unauthenticated one). Never cache
    // a redirected response (e.g. an auth redirect to /login) as the offline fallback.
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok && !response.redirected) {
                    const clone = response.clone();
                    caches.open(SHELL_CACHE).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() =>
                caches.match(event.request).then((cached) => cached || caches.match('/offline-reader'))
            )
    );
});
