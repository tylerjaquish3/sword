const DB_NAME = 'sword-offline';
const DB_VERSION = 1;
const STORES = ['translations', 'chapters', 'verses', 'verseComments', 'chapterComments', 'highlights', 'prayers', 'prayerTypes', 'outbox'];

function open() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('verses')) {
                db.createObjectStore('verses', { keyPath: ['translation_id', 'chapter_id', 'number'] });
            }
            if (!db.objectStoreNames.contains('chapters')) {
                db.createObjectStore('chapters', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('translations')) {
                db.createObjectStore('translations', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('verseComments')) {
                // Keyed by the comment's own id (not chapter_id+verse_number) — a single verse
                // can have several distinct comments, and a compound key would silently drop
                // all but the last one on every bundle sync.
                db.createObjectStore('verseComments', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('chapterComments')) {
                // Same reasoning as verseComments above — a chapter can have several comments.
                db.createObjectStore('chapterComments', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('highlights')) {
                db.createObjectStore('highlights', { keyPath: ['chapter_id', 'verse_number'] });
            }
            if (!db.objectStoreNames.contains('prayers')) {
                db.createObjectStore('prayers', { keyPath: 'id', autoIncrement: true });
            }
            if (!db.objectStoreNames.contains('prayerTypes')) {
                db.createObjectStore('prayerTypes', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('outbox')) {
                db.createObjectStore('outbox', { keyPath: 'uuid' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function clearStore(storeName) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        tx.objectStore(storeName).clear();
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function putAll(storeName, records) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        records.forEach((record) => store.put(record));
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function getAll(storeName) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readonly');
        const request = tx.objectStore(storeName).getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function deleteRecord(storeName, key) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        tx.objectStore(storeName).delete(key);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function clearAll() {
    for (const storeName of STORES) {
        await clearStore(storeName);
    }
}

export default { open, putAll, getAll, deleteRecord, clearStore, clearAll, STORES };
