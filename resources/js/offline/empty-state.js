// resources/js/offline/empty-state.js — shared "nothing synced yet" message for the three
// offline pages (Read, Prayers, Digest). Distinguishes "never turned on" from "turned on,
// but the initial sync (which can take 15-20+ seconds with a full Bible dataset) hasn't
// finished yet" — these look identical from IndexedDB's point of view (empty stores), but
// mean very different things to the person seeing the message.
function showEmptyState() {
    const message = document.getElementById('offline-reader-empty-message');
    if (message) {
        message.textContent = document.body.dataset.offlineEnabled === 'true'
            ? 'Offline Mode is on, but your data hasn\'t finished downloading yet. Stay online for a few more moments, then reload this page.'
            : 'Offline Mode isn\'t set up on this device yet.';
    }
    document.getElementById('offline-reader-empty-state').classList.remove('d-none');
}

export default { showEmptyState };
