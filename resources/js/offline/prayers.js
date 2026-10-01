// resources/js/offline/prayers.js — entry point for the Offline Reader's Prayers page
// (/offline-reader/prayers): view past prayers and add new ones offline.
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
        list.innerHTML = prayers
            .slice()
            .sort((a, b) => (a.date < b.date ? 1 : -1))
            .map((p) => `<div class="small mb-2"><strong>${p.date}</strong> — ${prayerTypeById.get(p.prayer_type_id) || 'Prayer'}: ${p.content}</div>`)
            .join('') || '<div class="small text-muted">No prayers yet.</div>';
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
