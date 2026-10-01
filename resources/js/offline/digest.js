// resources/js/offline/digest.js — entry point for the Offline Reader's Digest page
// (/offline-reader/digest): queue a weekly digest reflection while offline.
import db from './db.js';
import syncManager from './sync-manager.js';

// Format a Date as YYYY-MM-DD using LOCAL date components, not UTC — see the server's
// 'week_start' => 'nullable|date' validation and Carbon::parse() in SharedDigestController.
function toLocalDateString(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function currentWeekStartISO() {
    const now = new Date();
    const day = now.getDay(); // 0 (Sun) .. 6 (Sat)
    const diffToMonday = day === 0 ? -6 : 1 - day;
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMonday);
    return toLocalDateString(monday);
}

async function submitDigest(submitAction) {
    const fruits = Array.from(document.querySelectorAll('#or-digest-fruits input:checked')).map((el) => el.value);
    const idols = Array.from(document.querySelectorAll('#or-digest-idols input:checked')).map((el) => el.value);

    const payload = {
        week_start: currentWeekStartISO(),
        submit_action: submitAction,
        show_chapters: document.getElementById('or-digest-show-chapters').checked,
        show_prayers: document.getElementById('or-digest-show-prayers').checked,
        fruits_needing_prayer: fruits,
        fruits_description: document.getElementById('or-digest-fruits-description').value,
        idols: idols,
        idols_other: document.getElementById('or-digest-idols-other').value,
        idols_description: document.getElementById('or-digest-idols-description').value,
        impactful_scripture: document.getElementById('or-digest-impactful-scripture').value,
        additional_content: document.getElementById('or-digest-additional-content').value,
        sermon_notes: document.getElementById('or-digest-sermon-notes').value,
    };

    await syncManager.queue('digest', payload);
    document.getElementById('or-sync-status').textContent = 'Digest reflection queued — your weekly summary will be attached once this syncs.';
}

async function init() {
    const translations = await db.getAll('translations');

    if (translations.length === 0) {
        document.getElementById('offline-reader-empty-state').classList.remove('d-none');
        return;
    }

    document.getElementById('offline-reader-content').classList.remove('d-none');

    document.getElementById('or-save-digest-draft').addEventListener('click', () => submitDigest('save'));
    document.getElementById('or-save-digest-share').addEventListener('click', () => submitDigest('share'));
}

document.addEventListener('DOMContentLoaded', init);
