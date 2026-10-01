// resources/js/offline/offline-reader.js — entry point for the Offline Reader page only.
import db from './db.js';

async function init() {
    const [translations, chapters, verses, highlights, verseComments] = await Promise.all([
        db.getAll('translations'),
        db.getAll('chapters'),
        db.getAll('verses'),
        db.getAll('highlights'),
        db.getAll('verseComments'),
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
