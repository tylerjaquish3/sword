@extends('base.layout')

@section('title', 'Read & Compare')

@section('content')  

<div class="row">
    <div class="col-12 mb-4 mb-xl-0">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="text-dark font-weight-bold mb-0">Read &amp; Compare</h3>
            <div class="d-flex">
                <button type="button" id="btn-edit-book-info" class="btn bg-white btn-icon me-2" title="Edit book info" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                    <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" id="btn-chapter-note" class="btn bg-white btn-icon me-2" title="Add chapter note">
                    +<i class="mdi mdi-note-text"></i>
                </button>
                <button type="button" id="btn-single-col" class="btn btn-primary btn-icon" title="Single column">
                    <i class="mdi mdi-rectangle-outline"></i>
                </button>
                <button type="button" id="btn-double-col" class="btn bg-white btn-icon ms-2" title="Compare columns">
                    <i class="mdi mdi-view-split-vertical"></i>
                </button>
                <button type="button" id="btn-read-aloud" class="btn bg-white btn-icon ms-2" title="Read aloud">
                    <i class="mdi mdi-volume-high"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div id="reading-col" class="col-sm-12 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header p-0">
                <div class="reader-selector-bar">
                    {{-- Translation pill --}}
                    <div class="rsel-group rsel-translation">
                        <span class="rsel-label">Version</span>
                        <select class="rsel-native" id="translation_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>

                    <div class="rsel-divider"></div>

                    {{-- Book (select2) --}}
                    <div class="rsel-group rsel-book">
                        <span class="rsel-label">Book</span>
                        <select class="form-select select2-books rsel-native" id="book_select">
                            <option value="">— choose —</option>
                            <optgroup label="Old Testament">
                                @foreach ($books->where('new_testament', 0) as $book)
                                    <option value="{{ $book->id }}">{{ $book->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="New Testament">
                                @foreach ($books->where('new_testament', 1) as $book)
                                    <option value="{{ $book->id }}">{{ $book->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="rsel-divider"></div>

                    {{-- Chapter --}}
                    <div class="rsel-group rsel-chapter">
                        <span class="rsel-label">Ch.</span>
                        <select class="rsel-native" id="chapter_select">
                            <option value=1>1</option>
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter_content"></div>

                <div class="reading-section-divider my-4"></div>

                <div id="book-info" class="reading-book-info mb-4">
                    <div class="reading-book-meta">
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-account-outline me-1"></i>Author</span>
                            <span id="book-author" class="reading-meta-value"></span>
                        </div>
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-calendar-range-outline me-1"></i>Timeframe</span>
                            <span id="book-timeframe" class="reading-meta-value"></span>
                        </div>
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-text-subject me-1"></i>About</span>
                            <span id="book-description" class="reading-meta-value"></span>
                        </div>
                    </div>
                    <button type="button" id="btn-edit-book" class="reading-edit-btn btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                </div>

                <div class="reading-notes-section mb-3">
                    <div class="reading-notes-header">
                        <span class="notes-icon"><i class="mdi mdi-note-text"></i></span>
                        <span class="notes-title">Chapter Notes</span>
                        <a href="#" id="chapter_comment_link" class="ms-auto btn btn-sm btn-secondary reading-edit-btn" title="Add chapter note"><i class="mdi mdi-plus"></i></a>
                    </div>
                    <div id="chapter_comments_display" class="reading-notes-body">
                        <p class="reading-notes-empty mb-0">No chapter notes yet.</p>
                    </div>
                </div>

                <div class="reading-actions">
                    <button type="button" id="btn-mark-read" class="btn btn-outline-success btn-sm"><i class="mdi mdi-check"></i> Mark as Read</button>
                    <small id="read-status-display" class="reading-read-status"></small>
                </div>

                <div class="reading-nav mt-4">
                    <button type="button" id="btn-prev-chapter" class="btn btn-outline-secondary reading-nav-btn">
                        <i class="mdi mdi-chevron-left"></i> Prev
                    </button>
                    <span id="chapter-nav-label" class="reading-nav-label"></span>
                    <button type="button" id="btn-next-chapter" class="btn btn-outline-secondary reading-nav-btn">
                        Next <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="compare-col" class="col-sm-6 grid-margin grid-margin-md-0 stretch-card d-none">
        <div class="card">
            <div class="card-header p-0">
                <div class="reader-selector-bar">
                    <div class="rsel-group" style="flex:1;">
                        <span class="rsel-label">Compare with</span>
                        <select class="rsel-native" id="translation2_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ $translation->name == 'NIV' ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter2_content"></div>
            </div>
        </div>
    </div>
</div>

@include('commentary.modals.verse')
@include('commentary.modals.chapter')

<div class="modal fade" id="bookEditModal" tabindex="-1" aria-labelledby="bookEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-book-open-page-variant"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="bookEditModalLabel">Edit Book Info</h5>
                        <p class="sword-modal-subtitle mb-0" id="book-edit-title"></p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-account"></i></span>
                        <span class="sword-modal-section-title">Author</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="book-edit-author" placeholder="e.g. Moses">
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-subject"></i></span>
                        <span class="sword-modal-section-title">Description</span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="book-edit-description" rows="3" placeholder="Brief overview of the book…"></textarea>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-calendar-range-outline"></i></span>
                        <span class="sword-modal-section-title">Timeframe</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="book-edit-timeframe" placeholder="e.g. ~1446–1406 BC">
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="btn-save-book">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('css')
<style>
/* ── Reader selector bar ─────────────────────────────────────── */
.reader-selector-bar {
    display: flex;
    align-items: stretch;
    background: #0e1628;
    border-radius: 0.375rem 0.375rem 0 0;
    overflow: hidden;
    min-height: 56px;
}

.rsel-group {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 26px 16px 8px;
    position: relative;
    cursor: pointer;
    transition: background 0.18s;
    min-width: 0;
}
.rsel-group:hover { background: rgba(201,168,76,0.08); }

.rsel-translation { flex: 0 0 auto; min-width: 80px; }
.rsel-book        { flex: 1 1 auto; }
.rsel-chapter     { flex: 0 0 auto; min-width: 64px; }

.rsel-label {
    position: absolute;
    top: 10px;
    left: 16px;
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(201,168,76,0.6);
    pointer-events: none;
}

/* Native selects (translation + chapter) */
.rsel-native {
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
}
.rsel-native option,
.rsel-native optgroup { background: #0e1628; color: #e2e8f0; }

.rsel-chevron {
    position: absolute;
    right: 10px;
    bottom: 12px;
    font-size: 0.85rem;
    color: rgba(201,168,76,0.5);
    pointer-events: none;
}

/* Dividers */
.rsel-divider {
    width: 1px;
    background: rgba(201,168,76,0.15);
    align-self: stretch;
    flex-shrink: 0;
}

/* ── select2 inside the bar ──────────────────────────────────── */
.rsel-book .select2-container {
    width: 100% !important;
}
.rsel-book .select2-container--default .select2-selection--single {
    background: transparent !important;
    border: none !important;
    height: auto !important;
    min-height: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    line-height: 1 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    line-height: normal !important;
    padding: 0 20px 0 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: rgba(255,255,255,0.35) !important;
    font-weight: 400 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: rgba(201,168,76,0.5) transparent transparent transparent !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent rgba(201,168,76,0.8) transparent !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__clear {
    color: rgba(201,168,76,0.6) !important;
    font-size: 1rem !important;
    margin-right: 18px !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single {
    border: none !important;
    box-shadow: none !important;
}

/* ── Bottom gold accent line on active group ─────────────────── */
.reader-selector-bar::after {
    content: '';
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(201,168,76,0.4), transparent);
    pointer-events: none;
}
.reader-selector-bar { position: relative; }
</style>
@endpush


@push('js')
<script>

const defaultTranslationId = {{ $defaultTranslationId ?? 'null' }};

var ttsActive = false;

function stopSpeech() {
    if (window.speechSynthesis) window.speechSynthesis.cancel();
    ttsActive = false;
    $('#btn-read-aloud').removeClass('btn-warning').addClass('bg-white')
        .attr('title', 'Read aloud')
        .find('i').removeClass('mdi-stop-circle-outline').addClass('mdi-volume-high');
}

$(document).ready(function() {

    // ── Read Aloud (Web Speech API) ───────────────────────────────
    $('#btn-read-aloud').on('click', function() {
        if (!window.speechSynthesis) {
            alert('Your browser does not support text-to-speech.');
            return;
        }
        if (ttsActive) {
            stopSpeech();
            return;
        }
        var text = '';
        $('#chapter_content .verse-clickable').each(function() {
            var $clone = $(this).clone();
            $clone.find('sup').remove();
            text += $clone.text().trim() + ' ';
        });
        text = text.trim();
        if (!text) return;

        var utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.95;
        utterance.onend = function() { stopSpeech(); };
        utterance.onerror = function() { stopSpeech(); };

        ttsActive = true;
        $('#btn-read-aloud').removeClass('bg-white').addClass('btn-warning')
            .attr('title', 'Stop reading')
            .find('i').removeClass('mdi-volume-high').addClass('mdi-stop-circle-outline');
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
    });

    $('#btn-mark-read').on('click', function() {
        const btn = $(this);
        $.ajax({
            url: '/chapters/mark-read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                book_id: $('#book_select').val(),
                chapter_number: $('#chapter_select').val(),
                translation_id: $('#translation_select').val(),
            },
            success: function() {
                btn.removeClass('btn-outline-success').addClass('btn-success');
                setTimeout(function() {
                    btn.removeClass('btn-success').addClass('btn-outline-success');
                }, 2000);
                const now = new Date();
                const formatted = now.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                $('#read-status-display').text('Last read: ' + formatted);
            }
        });
    });

    $('#btn-chapter-note').on('click', function() {
        $('#chapter_comment_link').trigger('click');
    });

    $('#btn-single-col').on('click', function() {
        $('#compare-col').addClass('d-none');
        $('#reading-col').removeClass('col-sm-6 col-md-6').addClass('col-12');
        $('#btn-single-col').addClass('btn-primary').removeClass('bg-white');
        $('#btn-double-col').addClass('bg-white').removeClass('btn-primary');
    });

    $('#btn-double-col').on('click', function() {
        $('#compare-col').removeClass('d-none');
        // Side-by-side on sm+, stacked on xs
        $('#reading-col').removeClass('col-12').addClass('col-sm-6');
        $('#compare-col').addClass('col-sm-6').removeClass('col-12');
        $('#btn-double-col').addClass('btn-primary').removeClass('bg-white');
        $('#btn-single-col').addClass('bg-white').removeClass('btn-primary');
        lookupVerses(2);
    });


    // Read query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramTranslation = urlParams.get('translation');
    const paramBook = urlParams.get('book');
    const paramChapter = urlParams.get('chapter');

    // Set initial values from query params if present
    if (paramTranslation) {
        $('#translation_select').val(paramTranslation);
    }
    if (paramBook) {
        $('#book_select').val(paramBook).trigger('change.select2');
    }

    // Keep compare dropdown in sync — remove whichever version is selected in the main picker
    const allTranslations = @json($translations->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));
    function syncCompareOptions() {
        const selectedId = parseInt($('#translation_select').val());
        const currentCompare = parseInt($('#translation2_select').val());
        const $compare = $('#translation2_select');
        $compare.empty();
        allTranslations.forEach(function(t) {
            if (t.id !== selectedId) {
                $compare.append(new Option(t.name, t.id));
            }
        });
        // Restore previous selection if still available, otherwise pick first
        if (currentCompare && currentCompare !== selectedId) {
            $compare.val(currentCompare);
        }
    }
    syncCompareOptions();

    // When translation changes, update chapter options
    $('#translation_select').change(function() {
        syncCompareOptions();
        book_id = $('#book_select').val();
        loadChapters(book_id, function() {
            lookupVerses('');
            lookupVerses(2);
            loadReadStatus();
        });
    });

    // When book changes, update chapter options and both sides
    $('#book_select').change(function() {
        book_id = $(this).val();
        loadChapters(book_id, function() {
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo(book_id);
    });

    // When chapter changes, update verses in both sides
    $('#chapter_select').change(function() {
        lookupVerses('');
        lookupVerses(2);
        loadChapterComments();
        loadReadStatus();
    });

    // When translation2 changes, update the compare side
    $('#translation2_select').change(function() {
        lookupVerses(2);
    });

    function initWithDefaults() {
        var bookId = $('#book_select').val();
        if (!bookId) return;
        if (defaultTranslationId) {
            $('#translation_select').val(defaultTranslationId);
        }
        loadChapters(bookId, function() {
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo(bookId);
    }

    // Load chapters for the initially selected book on page load,
    // defaulting to last-read position unless query params override.
    if (paramTranslation || paramBook || paramChapter) {
        loadChapters($('#book_select').val(), function() {
            if (paramChapter) {
                $('#chapter_select').val(paramChapter);
            }
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo($('#book_select').val());
    } else {
        $.get('/chapters/last-read')
            .done(function(last) {
                if (last && last.book_id) {
                    $('#translation_select').val(last.translation_id);
                    $('#book_select').val(last.book_id).trigger('change.select2');
                    loadChapters(last.book_id, function() {
                        $('#chapter_select').val(last.chapter_number);
                        lookupVerses('');
                        lookupVerses(2);
                        loadChapterComments();
                        loadReadStatus();
                    });
                    loadBookInfo(last.book_id);
                } else {
                    initWithDefaults();
                }
            })
            .fail(function() {
                initWithDefaults();
            });
    }

});

    function loadChapters(book_id, callback) {
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
                if (callback) callback();
            }
        });
    }

    function loadChapterComments() {
        let bookId = $('#book_select').val();
        let chapterNumber = $('#chapter_select').val();
        
        $.ajax({
            url: '/chapters/comments?book_id=' + bookId + '&chapter_number=' + chapterNumber,
            type: 'GET',
            success: function(response) {
                let commentsHtml = '';
                if (response.comments && response.comments.length > 0) {
                    const sorted = response.comments.slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    sorted.forEach(function(comment) {
                        let date = new Date(comment.created_at);
                        let formattedDate = date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        commentsHtml += '<div class="mb-2 pb-2 border-bottom">';
                        commentsHtml += '<small class="text-muted">' + formattedDate + '</small>';
                        commentsHtml += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
                        commentsHtml += '</div>';
                    });
                } else {
                    commentsHtml = '<p class="text-muted mb-0">No chapter notes yet.</p>';
                }
                $('#chapter_comments_display').html(commentsHtml);
            }
        });
    }

    function loadBookInfo(bookId) {
        if (!bookId) return;
        $.get('/books/' + bookId, function(book) {
            var desc = book.description || '';
            $('#book-author').text(book.author || '—');
            $('#book-timeframe').text(book.timeframe || '—');
            $('#book-description').text(desc.length > 120 ? desc.substring(0, 120) + '…' : (desc || '—'));
            // Pre-populate modal fields
            $('#book-edit-title').text(book.name);
            $('#book-edit-author').val(book.author || '');
            $('#book-edit-timeframe').val(book.timeframe || '');
            $('#book-edit-description').val(book.description || '');
        });
    }

    $('#btn-save-book').on('click', function() {
        var bookId = $('#book_select').val();
        if (!bookId) return;
        $.ajax({
            url: '/books/' + bookId,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                author: $('#book-edit-author').val(),
                timeframe: $('#book-edit-timeframe').val(),
                description: $('#book-edit-description').val(),
            },
            success: function() {
                var savedDesc = $('#book-edit-description').val();
                $('#book-author').text($('#book-edit-author').val() || '—');
                $('#book-timeframe').text($('#book-edit-timeframe').val() || '—');
                $('#book-description').text(savedDesc.length > 120 ? savedDesc.substring(0, 120) + '…' : (savedDesc || '—'));
                bootstrap.Modal.getInstance(document.getElementById('bookEditModal')).hide();
            }
        });
    });

    function loadReadStatus() {
        var bookId = $('#book_select').val();
        var chapterNumber = $('#chapter_select').val();
        var translationId = $('#translation_select').val();
        if (!bookId || !chapterNumber || !translationId) return;
        $.get('/chapters/read-status', {
            book_id: bookId,
            chapter_number: chapterNumber,
            translation_id: translationId,
        }, function(response) {
            if (response && response.read_at) {
                var date = new Date(response.read_at);
                var formatted = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                $('#read-status-display').text('Last read: ' + formatted);
            } else {
                $('#read-status-display').text('');
            }
        });
    }

    function updateChapterNavLabel() {
        var bookName = $('#book_select option:selected').text();
        var chapter  = $('#chapter_select').val();
        $('#chapter-nav-label').text(bookName + ' ' + chapter);

        var isFirstBook    = $('#book_select option:selected').is(':first-child');
        var isLastBook     = $('#book_select option:selected').is(':last-child');
        var isFirstChapter = $('#chapter_select option:selected').is(':first-child');
        var isLastChapter  = $('#chapter_select option:selected').is(':last-child');

        $('#btn-prev-chapter').prop('disabled', isFirstBook && isFirstChapter);
        $('#btn-next-chapter').prop('disabled', isLastBook  && isLastChapter);
    }

    $('#btn-prev-chapter').on('click', function() {
        var $chapterSelect = $('#chapter_select');
        var $bookSelect    = $('#book_select');

        if (!$chapterSelect.find('option:selected').is(':first-child')) {
            // Stay in book, go to previous chapter
            $chapterSelect.find('option:selected').prev().prop('selected', true);
            $chapterSelect.trigger('change');
        } else {
            // Cross book boundary — go to last chapter of previous book
            var $prevBook = $bookSelect.find('option:selected').prev();
            if (!$prevBook.length) return;
            $bookSelect.val($prevBook.val());
            loadBookInfo($prevBook.val());
            loadChapters($prevBook.val(), function() {
                $chapterSelect.find('option:last-child').prop('selected', true);
                lookupVerses('');
                lookupVerses(2);
                loadChapterComments();
                loadReadStatus();
                updateChapterNavLabel();
            });
        }
    });

    $('#btn-next-chapter').on('click', function() {
        var $chapterSelect = $('#chapter_select');
        var $bookSelect    = $('#book_select');

        if (!$chapterSelect.find('option:selected').is(':last-child')) {
            // Stay in book, go to next chapter
            $chapterSelect.find('option:selected').next().prop('selected', true);
            $chapterSelect.trigger('change');
        } else {
            // Cross book boundary — go to chapter 1 of next book
            var $nextBook = $bookSelect.find('option:selected').next();
            if (!$nextBook.length) return;
            $bookSelect.val($nextBook.val());
            loadBookInfo($nextBook.val());
            loadChapters($nextBook.val(), function() {
                $chapterSelect.find('option:first-child').prop('selected', true);
                lookupVerses('');
                lookupVerses(2);
                loadChapterComments();
                loadReadStatus();
                updateChapterNavLabel();
            });
        }
    });

    function lookupVerses(side)
    {
        if (!side) stopSpeech();
        translation_id = $('#translation'+side+'_select').val();
        // Always use the main book/chapter selectors
        book_id = $('#book_select').val();
        chapter_id = $('#chapter_select').val();
        
        // Show loading spinner
        $('#chapter'+side+'_content').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        $.ajax({
            url: '/translations/verses?translation_id='+translation_id+'&book_id='+book_id+'&chapter_id='+chapter_id,
            type: 'GET',
            success: function(response) {
                $('#chapter'+side+'_content').empty();
                const hlBg = { yellow: '#fef9c3', blue: '#dbeafe', green: '#dcfce7', red: '#fee2e2' };
                let html = '<p>';
                response.forEach(function(verse) {
                    // Add prefix (contains HTML like <br> or <h5>Header</h5>)
                    if (verse.prefix) {
                        html += verse.prefix;
                    }
                    let highlightStyle = '';
                    if (verse.highlight_color && hlBg[verse.highlight_color]) {
                        highlightStyle = 'background-color:' + hlBg[verse.highlight_color] + ';padding:2px 4px;border-radius:3px;';
                    } else if (verse.has_commentary) {
                        highlightStyle = 'border-bottom:2px dotted #94a3b8;';
                    }
                    html += '<span class="verse-clickable" data-verse-id="' + verse.id + '" style="cursor:pointer;' + highlightStyle + '">';
                    html += '<sup class="text-muted">' + verse.number + '</sup> ' + verse.text;
                    html += '</span>';
                    if (verse.is_favorite) {
                        html += '<sup style="color:#f59e0b;font-size:0.6rem;margin-left:1px;">★</sup>';
                    }
                    html += ' ';
                });
                html += '</p>';
                $('#chapter'+side+'_content').html(html);
                if (!side) updateChapterNavLabel();
            }
        });
    }
</script>
@endpush