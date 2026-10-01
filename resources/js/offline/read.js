// resources/js/offline/read.js — entry point for the Offline Reader's Read page
// (/offline-reader): Bible text, verse/chapter comments, and highlights. Styled to match
// the online reading page (resources/views/translations/index.blade.php): a flowing verse
// paragraph with inline highlight tints + an underline for verses with comments, and the
// shared .sword-modal design for the comment/highlight panel.
import db from './db.js';
import syncManager from './sync-manager.js';
import emptyState from './empty-state.js';

// Matches the online reading page's inline highlight tint colors exactly (translations/index.blade.php).
const HIGHLIGHT_BG = { yellow: '#fef9c3', blue: '#dbeafe', green: '#dcfce7', red: '#fee2e2' };
// Matches the comment/highlight modal's swatch border colors when a color is active.
const HIGHLIGHT_BORDER = { yellow: '#ca8a04', blue: '#2563eb', green: '#16a34a', red: '#dc2626' };

async function init() {
    const [translations, chapters, verses, highlights, verseComments, chapterComments] = await Promise.all([
        db.getAll('translations'),
        db.getAll('chapters'),
        db.getAll('verses'),
        db.getAll('highlights'),
        db.getAll('verseComments'),
        db.getAll('chapterComments'),
    ]);

    if (translations.length === 0 || verses.length === 0) {
        emptyState.showEmptyState();
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
    chapterComments.forEach((c) => {
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

        const matching = verses
            .filter((v) => v.translation_id === translationId && v.chapter_id === chapterId)
            .sort((a, b) => a.number - b.number);

        let html = '<p>';
        matching.forEach((v) => {
            const key = `${v.chapter_id}:${v.number}`;
            const color = highlightByKey.get(key);
            const hasComments = (commentsByVerseKey.get(key) || []).length > 0;

            let style = 'cursor:pointer;';
            if (color && HIGHLIGHT_BG[color]) {
                style += `background-color:${HIGHLIGHT_BG[color]};padding:0px 4px 2px;border-radius:3px;`;
            }
            if (hasComments) {
                style += 'text-decoration:underline dotted #94a3b8;text-underline-offset:3px;';
            }

            html += `<span class="or-verse" data-chapter-id="${v.chapter_id}" data-verse-number="${v.number}" data-verse-id="${v.id}" style="${style}">`;
            html += `<sup class="text-muted">${v.number}</sup> ${v.text}`;
            html += '</span> ';
        });
        html += '</p>';
        list.innerHTML = html;

        renderChapterComments(chapterId);
    }

    function renderChapterComments(chapterId) {
        const list = document.getElementById('or-chapter-comments-list');
        if (!list) return;
        const entries = chapterCommentsById.get(chapterId) || [];
        list.innerHTML = entries.length
            ? entries.map((c) => `<p class="mb-2">${c.comment}</p>`).join('')
            : '<p class="reading-notes-empty mb-0">No chapter notes yet.</p>';
    }

    function renderPanelComments(chapterId, verseNumber) {
        const list = document.getElementById('or-panel-comments-list');
        const entries = commentsByVerseKey.get(`${chapterId}:${verseNumber}`) || [];
        list.innerHTML = entries.length
            ? entries.map((c) => `<p class="mb-2">${c.comment}</p>`).join('')
            : '<p class="text-muted mb-0">No comments yet.</p>';
    }

    function setHighlightButtons(activeColor) {
        document.querySelectorAll('.or-color-btn').forEach((btn) => {
            const color = btn.dataset.color;
            btn.style.borderColor = color === activeColor ? HIGHLIGHT_BORDER[color] : 'transparent';
        });
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
        renderPanelComments(activeVerseKey.chapterId, activeVerseKey.verseNumber);
        setHighlightButtons(currentHighlightColor(activeVerseKey.chapterId, activeVerseKey.verseNumber));
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
            setHighlightButtons(newColor);
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

        document.getElementById('or-comment-input').value = '';
        renderPanelComments(activeVerseKey.chapterId, activeVerseKey.verseNumber);
        renderVerses();
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

    bookSelect.addEventListener('change', () => {
        renderChapterOptions(bookSelect.value);
        renderVerses();
    });
    chapterSelect.addEventListener('change', renderVerses);
    translationSelect.addEventListener('change', renderVerses);

    renderChapterOptions(bookSelect.value);
    renderVerses();
}

document.addEventListener('DOMContentLoaded', init);
