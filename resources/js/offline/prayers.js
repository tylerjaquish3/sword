// resources/js/offline/prayers.js — entry point for the Offline Reader's Prayers page
// (/offline-reader/prayers): view past prayers and add new ones offline. Styled to match
// the online Prayers page (resources/views/prayers/partials/card-view.blade.php): one card
// per day, grouping that day's entries under a gold dot + uppercase type label.
import db from './db.js';
import syncManager from './sync-manager.js';
import emptyState from './empty-state.js';

// Format a Date as MM/DD/YYYY using LOCAL date components — matches the online prayer
// form's PHP `Carbon::now()->format('m/d/Y')`, since prayers.date is stored verbatim and
// grouped/ordered as a string elsewhere (PrayerController, HomeController).
function toOnlineDateFormat(date) {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
}

// "MM/DD/YYYY" -> "Thursday, Oct 1, 2026", matching the online card-view's
// Carbon::parse($date)->format('l, M j, Y').
function formatDateHeader(mdy) {
    const [month, day, year] = mdy.split('/').map(Number);
    const date = new Date(year, month - 1, day);
    const weekday = date.toLocaleDateString('en-US', { weekday: 'long' });
    const monthName = date.toLocaleDateString('en-US', { month: 'short' });
    return `${weekday}, ${monthName} ${day}, ${year}`;
}

// For sorting MM/DD/YYYY strings chronologically (string comparison alone sorts them
// lexically wrong, e.g. "12/01/2026" before "02/01/2026").
function toSortableDate(mdy) {
    const [month, day, year] = mdy.split('/').map(Number);
    return year * 10000 + month * 100 + day;
}

async function init() {
    const [translations, prayers, prayerTypes] = await Promise.all([
        db.getAll('translations'),
        db.getAll('prayers'),
        db.getAll('prayerTypes'),
    ]);

    // Same "has Offline Mode ever synced" signal the Read page uses, so the empty state is
    // consistent across all offline pages regardless of which one you land on first.
    if (translations.length === 0) {
        emptyState.showEmptyState();
        return;
    }

    document.getElementById('offline-reader-content').classList.remove('d-none');

    const prayerTypeById = new Map(prayerTypes.map((pt) => [pt.id, pt.name]));
    const prayerTypeSelect = document.getElementById('or-prayer-type');
    prayerTypes.forEach((pt) => {
        const option = document.createElement('option');
        option.value = pt.id;
        option.textContent = pt.name;
        prayerTypeSelect.appendChild(option);
    });

    function renderPrayers() {
        const list = document.getElementById('or-prayer-list');
        const subtitle = document.getElementById('or-prayer-subtitle');

        if (prayers.length === 0) {
            subtitle.textContent = 'No entries yet';
            list.innerHTML = `
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="mdi mdi-heart-outline mdi-48px mb-3 d-block" style="color: var(--sword-gold);"></i>
                            <h5 class="text-muted mb-1">No prayers recorded yet</h5>
                            <p class="text-muted small">Add one below.</p>
                        </div>
                    </div>
                </div>`;
            return;
        }

        subtitle.textContent = `${prayers.length} ${prayers.length === 1 ? 'prayer' : 'prayers'} recorded`;

        const byDate = new Map();
        prayers.forEach((p) => {
            if (!byDate.has(p.date)) byDate.set(p.date, []);
            byDate.get(p.date).push(p);
        });

        const dates = Array.from(byDate.keys()).sort((a, b) => toSortableDate(b) - toSortableDate(a));

        list.innerHTML = dates.map((date) => {
            const entries = byDate.get(date)
                .map((p) => `
                    <div class="mb-3">
                        <div class="prayer-type-header">
                            <span class="prayer-type-dot"></span>
                            <span class="prayer-type-label">${prayerTypeById.get(p.prayer_type_id) || 'Prayer'}</span>
                        </div>
                        <p class="text-muted mb-0 ps-3" style="font-size: 0.875rem; line-height: 1.6;">${p.content}</p>
                    </div>`)
                .join('');

            return `
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="card-title mb-0">${formatDateHeader(date)}</h4>
                            </div>
                            <div class="reading-section-divider mt-0 mb-3"></div>
                            ${entries}
                        </div>
                    </div>
                </div>`;
        }).join('');
    }
    renderPrayers();

    document.getElementById('or-save-prayer').addEventListener('click', async () => {
        const content = document.getElementById('or-prayer-content').value.trim();
        if (!content) return;
        const typeId = Number(prayerTypeSelect.value);
        const date = toOnlineDateFormat(new Date());

        await syncManager.queue('prayer', { date, [`type${typeId}`]: content });

        const localRecord = { id: `local-${crypto.randomUUID()}`, date, content, prayer_type_id: typeId };
        prayers.push(localRecord);
        await db.putAll('prayers', [localRecord]);
        document.getElementById('or-prayer-content').value = '';
        document.getElementById('or-sync-status').textContent = 'Prayer queued — will sync once you\'re back online.';
        renderPrayers();
    });
}

document.addEventListener('DOMContentLoaded', init);
