import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'datatables.net-dt';
import select2 from 'select2';
import Swal from 'sweetalert2';

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import moment from 'moment';

import offlineDb from './offline/db.js';
import bundleSync from './offline/bundle-sync.js';
import syncManager from './offline/sync-manager.js';

window.swordOffline = window.swordOffline || {};
window.swordOffline.db = offlineDb;
window.swordOffline.bundleSync = bundleSync;
window.swordOffline.syncManager = syncManager;

const BUNDLE_SYNC_THROTTLE_MS = 60 * 60 * 1000; // 1 hour

function shouldAutoSyncBundle() {
    try {
        const last = localStorage.getItem('sword_last_bundle_sync');
        return !last || (Date.now() - parseInt(last, 10)) > BUNDLE_SYNC_THROTTLE_MS;
    } catch (e) {
        return true;
    }
}

function markBundleSynced() {
    try {
        localStorage.setItem('sword_last_bundle_sync', String(Date.now()));
    } catch (e) {
        // ignore
    }
}

// If Offline Mode is already on, keep the bundle fresh while online, throttled to once an
// hour so normal multi-page navigation doesn't re-download/rewrite the whole ~19MB bundle
// (and the ~155k-row IndexedDB rewrite) on every click, and doesn't race the outbox drain.
function autoSyncBundleIfDue() {
    if (document.body.dataset.offlineEnabled === 'true' && navigator.onLine && shouldAutoSyncBundle()) {
        bundleSync.sync().then(markBundleSynced).catch((err) => console.error('Offline bundle sync failed', err));
    }
}

function drainOutboxIfOnline() {
    if (navigator.onLine) {
        syncManager.drain().catch((err) => console.error('Outbox drain failed', err));
    }
}

// Guard against one device carrying offline content across different logged-in accounts:
// if this browser's IndexedDB/outbox belong to a different user than the one now logged
// in, clear them first. This must complete before any sync/drain below touches IndexedDB,
// since those should never read or write stale cross-account data.
(function guardAgainstAccountSwitch() {
    const currentUserId = document.body.dataset.userId;
    let storedUserId = null;
    try {
        storedUserId = localStorage.getItem('sword_offline_user_id');
    } catch (e) {
        // localStorage unavailable (private mode, etc.) — nothing to guard against.
    }

    if (currentUserId && storedUserId && storedUserId !== currentUserId) {
        offlineDb.clearAll().then(() => {
            try { localStorage.setItem('sword_offline_user_id', currentUserId); } catch (e) {}
            autoSyncBundleIfDue();
            drainOutboxIfOnline();
        });
    } else {
        if (currentUserId) {
            try { localStorage.setItem('sword_offline_user_id', currentUserId); } catch (e) {}
        }
        autoSyncBundleIfDue();
        drainOutboxIfOnline();
    }
})();

window.addEventListener('online', () => {
    syncManager.drain().catch((err) => console.error('Outbox drain failed', err));
});

// Make libraries globally available for inline Blade scripts.
// window.$ override ensures dev-mode module jQuery and prod-mode vendor jQuery are the same instance.
window.$ = window.jQuery = $;
select2(window, $); // attach $.fn.select2 to this jQuery instance
window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;
window.moment = moment;

Chart.register(ChartDataLabels);

// Register service worker for offline support
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}

// Initialise select2 immediately — module scripts run after DOM is parsed but before
// DOMContentLoaded fires, so elements exist and this runs before any jQuery ready callbacks.
function initSelect2() {
    $('.select2-books').each(function () {
        const $sel = $(this);
        const placeholder = $sel.find('option[value=""]').first().text() || 'Select a Book';
        $sel.select2({ placeholder, allowClear: true, width: '100%' });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelect2);
} else {
    initSelect2();
}

// CSRF header for all jQuery AJAX requests
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfMeta.getAttribute('content') }
    });
}

import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'select2/dist/css/select2.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/app.css'; 