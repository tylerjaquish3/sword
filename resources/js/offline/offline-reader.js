// resources/js/offline/offline-reader.js — entry point for the Offline Reader page only.
import db from './db.js';
import syncManager from './sync-manager.js';

// Format a Date as YYYY-MM-DD using LOCAL date components, not UTC.
function toLocalDateString(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

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
    const [translations, chapters, verses, highlights, verseComments, prayers, prayerTypes] = await Promise.all([
        db.getAll('translations'),
        db.getAll('chapters'),
        db.getAll('verses'),
        db.getAll('highlights'),
        db.getAll('verseComments'),
        db.getAll('prayers'),
        db.getAll('prayerTypes'),
    ]);

    if (translations.length === 0 || verses.length === 0) {
        document.getElementById('offline-reader-empty-state').classList.remove('d-none');
        return;
    }

    document.getElementById('offline-reader-content').classList.remove('d-none');

    const highlightByKey = new Map(highlights.map((h) => [`${h.chapter_id}:${h.verse_number}`, h.highlight_color]));

    // A verse can have several distinct comments, so group them into arrays keyed by
    // chapter+verse rather than keeping only the last one.
    const commentsByVerseKey = new Map();
    verseComments.forEach((c) => {
        const key = `${c.chapter_id}:${c.verse_number}`;
        if (!commentsByVerseKey.has(key)) commentsByVerseKey.set(key, []);
        commentsByVerseKey.get(key).push(c);
    });

    const chapterCommentsById = new Map(); // chapter_id -> array of comment records, same grouping reasoning
    const allChapterComments = await db.getAll('chapterComments');
    allChapterComments.forEach((c) => {
        if (!chapterCommentsById.has(c.chapter_id)) chapterCommentsById.set(c.chapter_id, []);
        chapterCommentsById.get(c.chapter_id).push(c);
    });

    let activeVerseKey = null;

    function currentHighlightColor(chapterId, verseNumber) {
        return highlightByKey.get(`${chapterId}:${verseNumber}`) || null;
    }

    const translationSelect = document.getElementById('or-translation');
    translations.forEach((t) => {
        const option = document.createElement('option');
        option.value = t.id;
        option.textContent = t.name;
        translationSelect.appendChild(option);
    });

    const bookSelect = document.getElementById('or-book');
    const booksById = new Map();
    chapters.forEach((c) => {
        if (!booksById.has(c.book.id)) {
            booksById.set(c.book.id, c.book);
            const option = document.createElement('option');
            option.value = c.book.id;
            option.textContent = c.book.name;
            bookSelect.appendChild(option);
        }
    });

    const chapterSelect = document.getElementById('or-chapter');

    function renderChapterOptions(bookId) {
        chapterSelect.innerHTML = '';
        chapters
            .filter((c) => c.book.id === Number(bookId))
            .sort((a, b) => a.number - b.number)
            .forEach((c) => {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = `Chapter ${c.number}`;
                chapterSelect.appendChild(option);
            });
    }

    function renderVerses() {
        const translationId = Number(translationSelect.value);
        const chapterId = Number(chapterSelect.value);
        const list = document.getElementById('or-verse-list');
        list.innerHTML = '';

        verses
            .filter((v) => v.translation_id === translationId && v.chapter_id === chapterId)
            .sort((a, b) => a.number - b.number)
            .forEach((v) => {
                const key = `${v.chapter_id}:${v.number}`;
                const color = highlightByKey.get(key);
                const comments = commentsByVerseKey.get(key) || [];

                const row = document.createElement('div');
                row.className = 'or-verse mb-2 ps-2';
                row.style.borderLeft = color ? `4px solid ${color}` : '4px solid transparent';
                row.dataset.chapterId = v.chapter_id;
                row.dataset.verseNumber = v.number;
                row.dataset.verseId = v.id; // the currently-selected translation's verse id — see Task 14
                row.innerHTML = `<strong>${v.number}</strong> ${v.text}` +
                    comments.map((c) => `<div class="text-muted small">${c.comment}</div>`).join('');
                list.appendChild(row);
            });

        renderChapterComments(chapterId);
    }

    function renderChapterComments(chapterId) {
        const list = document.getElementById('or-chapter-comments-list');
        if (!list) return;
        list.innerHTML = (chapterCommentsById.get(chapterId) || [])
            .map((c) => `<div class="small text-muted mb-1">${c.comment}</div>`)
            .join('') || '<div class="small text-muted">No chapter notes yet.</div>';
    }

    document.getElementById('or-verse-list').addEventListener('click', (event) => {
        const row = event.target.closest('.or-verse');
        if (!row) return;

        activeVerseKey = {
            chapterId: Number(row.dataset.chapterId),
            verseNumber: Number(row.dataset.verseNumber),
            verseId: Number(row.dataset.verseId),
        };
        document.getElementById('or-panel-reference').textContent = `Verse ${activeVerseKey.verseNumber}`;
        document.getElementById('or-comment-input').value = '';
        new window.bootstrap.Modal(document.getElementById('or-verse-panel')).show();
    });

    document.querySelectorAll('.or-color-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!activeVerseKey) return;
            const clickedColor = btn.dataset.color;
            const key = `${activeVerseKey.chapterId}:${activeVerseKey.verseNumber}`;
            const current = currentHighlightColor(activeVerseKey.chapterId, activeVerseKey.verseNumber);
            const newColor = current === clickedColor ? null : clickedColor;

            highlightByKey.set(key, newColor);
            await db.putAll('highlights', [{ chapter_id: activeVerseKey.chapterId, verse_number: activeVerseKey.verseNumber, highlight_color: newColor }]);
            await syncManager.queue('highlight', {
                chapter_id: activeVerseKey.chapterId,
                verse_number: activeVerseKey.verseNumber,
                color: newColor,
                explicit: true,
            });
            renderVerses();
        });
    });

    document.getElementById('or-save-comment').addEventListener('click', async () => {
        if (!activeVerseKey) return;
        const comment = document.getElementById('or-comment-input').value.trim();
        if (!comment) return;

        const key = `${activeVerseKey.chapterId}:${activeVerseKey.verseNumber}`;
        const queued = await syncManager.queue('verse_comment', {
            verse_id: activeVerseKey.verseId,
            comment,
        });

        // Add to the in-memory + IndexedDB comment list immediately (optimistic), using the
        // outbox item's uuid as a local id since the server hasn't assigned a real one yet.
        const localRecord = { id: `local-${queued.uuid}`, chapter_id: activeVerseKey.chapterId, verse_number: activeVerseKey.verseNumber, comment };
        if (!commentsByVerseKey.has(key)) commentsByVerseKey.set(key, []);
        commentsByVerseKey.get(key).push(localRecord);
        await db.putAll('verseComments', [localRecord]);

        renderVerses();
        window.bootstrap.Modal.getInstance(document.getElementById('or-verse-panel')).hide();
    });

    document.getElementById('or-save-chapter-comment').addEventListener('click', async () => {
        const chapterId = Number(chapterSelect.value);
        const comment = document.getElementById('or-chapter-comment-input').value.trim();
        if (!comment) return;

        const queued = await syncManager.queue('chapter_comment', {
            type: 'chapter',
            chapter_id: chapterId,
            comment,
        });

        const localRecord = { id: `local-${queued.uuid}`, chapter_id: chapterId, comment };
        if (!chapterCommentsById.has(chapterId)) chapterCommentsById.set(chapterId, []);
        chapterCommentsById.get(chapterId).push(localRecord);
        await db.putAll('chapterComments', [localRecord]);

        document.getElementById('or-chapter-comment-input').value = '';
        renderChapterComments(chapterId);
    });

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

    bookSelect.addEventListener('change', () => {
        renderChapterOptions(bookSelect.value);
        renderVerses();
    });
    chapterSelect.addEventListener('change', renderVerses);
    translationSelect.addEventListener('change', renderVerses);

    renderChapterOptions(bookSelect.value);
    renderVerses();
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

document.getElementById('or-save-digest-draft').addEventListener('click', () => submitDigest('save'));
document.getElementById('or-save-digest-share').addEventListener('click', () => submitDigest('share'));

document.addEventListener('DOMContentLoaded', init);
