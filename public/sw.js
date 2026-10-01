// public/sw.js — static app-shell cache + offline navigation fallback only.
// Deliberately does NOT intercept or cache any /api/* or data requests — Bible text and
// personal content caching is handled explicitly via IndexedDB (see resources/js/offline/),
// not opportunistic HTTP caching, to avoid ever serving stale API data silently.
const SHELL_CACHE = 'sword-shell-v1';
const SHELL_URLS = [
    '/',
    '/offline-reader',
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => cache.addAll(SHELL_URLS))
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
    if (event.request.mode !== 'navigate') {
        return; // Only handle page navigations — API/data requests pass through untouched.
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match('/offline-reader'))
    );
});
