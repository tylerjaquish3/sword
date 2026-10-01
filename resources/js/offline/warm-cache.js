// resources/js/offline/warm-cache.js — proactively loads each offline page in a hidden
// iframe so the service worker caches it and the JS/CSS it needs. A real navigation, not a
// bare fetch, so sub-resource requests (each page's build assets) go through the same
// runtime-caching the service worker already does for any normal page visit. Called both
// right after enabling Offline Mode and on normal page loads (see app.js) so a service
// worker update that changes the offline page structure self-heals on the next online
// visit, rather than requiring the user to notice and re-toggle the setting.
const OFFLINE_PAGES = [
    '/offline-reader',
    '/offline-reader/prayers',
    '/offline-reader/digest',
    '/offline-reader/unavailable',
];

function warmOfflinePages() {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    OFFLINE_PAGES.forEach((url) => {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        iframe.onload = () => {
            setTimeout(() => iframe.remove(), 1000);
        };
        document.body.appendChild(iframe);
    });
}

export default { warmOfflinePages };
