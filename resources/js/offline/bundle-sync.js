import db from './db.js';

async function sync() {
    const response = await fetch('/api/offline/bundle', {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Bundle sync failed: ${response.status}`);
    }

    const bundle = await response.json();

    await db.clearStore('translations');
    await db.clearStore('chapters');
    await db.clearStore('verses');
    await db.clearStore('verseComments');
    await db.clearStore('chapterComments');
    await db.clearStore('highlights');
    await db.clearStore('prayers');
    await db.clearStore('prayerTypes');

    await db.putAll('translations', bundle.translations);
    await db.putAll('chapters', bundle.chapters);
    await db.putAll('verses', bundle.verses);
    await db.putAll('verseComments', bundle.verseComments);
    await db.putAll('chapterComments', bundle.chapterComments);
    await db.putAll('highlights', bundle.highlights);
    await db.putAll('prayers', bundle.prayers);
    await db.putAll('prayerTypes', bundle.prayerTypes);
}

export default { sync };
