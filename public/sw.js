// public/sw.js — static app-shell cache + offline navigation fallback.
// Precaches only stable, non-hashed assets (manifest, icons). Everything else —
// the Vite build output and vendor scripts — is cached opportunistically at runtime
// the first time this browser actually fetches it while online, and served cache-first
// after that. This avoids needing to know Vite's hashed filenames in advance, and avoids
// ever permanently caching a redirected or unauthenticated response as the offline page.
const SHELL_CACHE = 'sword-shell-v3';
const PRECACHE_URLS = [
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

// Only the Offline Reader's own pages are cached on navigation (never arbitrary
// authenticated pages). Each failed navigation maps to whichever of these pages covers
// the same content — e.g. a failed /prayers load falls back to the offline Prayers page,
// not a generic one-size-fits-all page — so clicking the normal nav while offline lands
// you on the matching offline equivalent instead of a single catch-all screen.
const OFFLINE_PAGES = [
    '/offline-reader',
    '/offline-reader/prayers',
    '/offline-reader/digest',
    '/offline-reader/unavailable',
];
const OFFLINE_FALLBACK_MAP = [
    { prefix: '/translations', fallback: '/offline-reader' },
    { prefix: '/prayers', fallback: '/offline-reader/prayers' },
    { prefix: '/digest', fallback: '/offline-reader/digest' },
];

function offlineFallbackFor(pathname) {
    // A failed re-visit to one of the offline pages themselves falls back to itself.
    if (OFFLINE_PAGES.includes(pathname)) {
        return pathname;
    }
    const match = OFFLINE_FALLBACK_MAP.find((entry) => pathname.startsWith(entry.prefix));
    return match ? match.fallback : '/offline-reader/unavailable';
}

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

    // Network-first for navigations. Only the Offline Reader's own pages are cached
    // (refreshed every time one is visited successfully while online, so none ever go
    // stale, and never cached as a redirected/unauthenticated response). Any other page's
    // navigation is never cached — that would otherwise serve a stale, partially
    // non-functional copy of whatever page you last visited instead of the matching
    // offline equivalent. A failed navigation always falls back to the right offline page.
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok && !response.redirected && OFFLINE_PAGES.includes(url.pathname)) {
                    const clone = response.clone();
                    caches.open(SHELL_CACHE).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() =>
                caches.match(offlineFallbackFor(url.pathname)).then((cached) => {
                    // Nothing cached (e.g. Offline Mode was never enabled on this browser, so
                    // these pages were never warmed) — surface a plain error instead of
                    // resolving with `undefined`, which breaks the navigation outright and
                    // looks like the page is just hung.
                    return (
                        cached ||
                        new Response(
                            '<!doctype html><title>Connection problem</title>' +
                                '<p>Sword couldn\'t reach the server and no offline copy of this page is saved. ' +
                                'Check your connection and try again.</p>',
                            { status: 503, headers: { 'Content-Type': 'text/html' } }
                        )
                    );
                })
            )
    );
});
