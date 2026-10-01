// resources/js/offline/sync-manager.js
import db from './db.js';

const ENDPOINTS = {
    verse_comment: { url: '/commentary', method: 'POST' },
    chapter_comment: { url: '/commentary', method: 'POST' },
    prayer: { url: '/prayers', method: 'POST' },
    highlight: { url: '/verse-highlights/toggle', method: 'POST' },
    accountability_checkin: { url: '/accountability', method: 'POST' },
    digest: { url: '/digest/complete', method: 'POST' },
};

function queue(type, payload) {
    const uuid = crypto.randomUUID();
    const record = { uuid, type, payload, created_at: Date.now() };
    return db.putAll('outbox', [record]).then(() => record);
}

async function sendOne(record) {
    const endpoint = ENDPOINTS[record.type];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const body = { ...record.payload, client_uuid: record.uuid };

    const response = await fetch(endpoint.url, {
        method: endpoint.method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    });

    return response;
}

async function drain() {
    const items = (await db.getAll('outbox')).sort((a, b) => a.created_at - b.created_at);
    const failed = [];

    for (const record of items) {
        let response;
        try {
            response = await sendOne(record);
        } catch (networkError) {
            // Network failure (not a server response at all) — stop draining; the rest
            // stay queued for the next online event. Retrying now would likely just fail again.
            break;
        }

        if (response.ok) {
            await db.deleteRecord('outbox', record.uuid);
        } else {
            // A real validation error (e.g. 422) — this specific item won't succeed by
            // itself retrying, so leave it queued and marked failed, but keep draining
            // the rest of the queue (unlike a network failure).
            failed.push(record.uuid);
        }
    }

    return { remaining: (await db.getAll('outbox')).length, failed: failed.length };
}

export default { queue, drain };
