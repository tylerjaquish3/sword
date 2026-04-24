@extends('base.layout')

@section('title', 'Translation')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Translations</h3>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                <button type="button" id="btn-single-col" class="btn btn-primary btn-icon" title="Single column">
                    <i class="mdi mdi-rectangle-outline"></i>
                </button>
                <button type="button" id="btn-double-col" class="btn bg-white btn-icon ms-2" title="Compare columns">
                    <i class="mdi mdi-view-split-vertical"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div id="reading-col" class="col-sm-12 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-4">
                        <select class="form-select" id="translation_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-select select2-books" id="book_select">
                            <option value="">Select a Book</option>
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
                    <div class="col-4">
                        <select class="form-select" id="chapter_select">
                            <option value=1>1</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter_content"></div>

                <div class="reading-section-divider my-4"></div>

                <div id="book-info" class="reading-book-info mb-4">
                    <div class="reading-book-meta">
                        <div class="reading-meta-row">
                            <span class="reading-meta-label">Author</span>
                            <span id="book-author" class="reading-meta-value"></span>
                        </div>
                        <div class="reading-meta-row">
                            <span class="reading-meta-label">About</span>
                            <span id="book-description" class="reading-meta-value"></span>
                        </div>
                    </div>
                    <button type="button" id="btn-edit-book" class="reading-edit-btn btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                </div>

                <div class="reading-notes-section mb-3">
                    <div class="reading-notes-header">
                        <span class="notes-icon"><i class="mdi mdi-note-text-outline"></i></span>
                        <span class="notes-title">Chapter Notes</span>
                    </div>
                    <div id="chapter_comments_display" class="reading-notes-body">
                        <p class="reading-notes-empty mb-0">No chapter notes yet.</p>
                    </div>
                </div>

                <div class="reading-actions">
                    <a href="#" id="chapter_comment_link" class="reading-action-link"><i class="mdi mdi-plus-circle-outline"></i> Add Chapter Note</a>
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
            <div class="card-header">
                <div class="row">
                    <div class="col-4 d-flex align-items-center">
                        <span class="fw-semibold text-nowrap">Compare with</span>
                    </div>
                    <div class="col-8">
                        <select class="form-select" id="translation2_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ $translation->name == 'NIV' ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookEditModalLabel">Edit Book Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="book-edit-title" class="fw-bold mb-3"></h6>
                <div class="mb-3">
                    <label for="book-edit-author" class="form-label">Author</label>
                    <input type="text" class="form-control" id="book-edit-author" placeholder="e.g. Moses">
                </div>
                <div class="mb-3">
                    <label for="book-edit-description" class="form-label">Description</label>
                    <textarea class="form-control" id="book-edit-description" rows="4" placeholder="Brief overview of the book..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-book">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection


@push('js')
<script>

const defaultTranslationId = {{ $defaultTranslationId ?? 'null' }};

$(document).ready(function() {

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

    $('#btn-single-col').on('click', function() {
        $('#compare-col').addClass('d-none');
        $('#reading-col').removeClass('col-sm-6').addClass('col-sm-12');
        $('#btn-single-col').addClass('btn-primary').removeClass('bg-white');
        $('#btn-double-col').addClass('bg-white').removeClass('btn-primary');
    });

    $('#btn-double-col').on('click', function() {
        $('#compare-col').removeClass('d-none');
        $('#reading-col').removeClass('col-sm-12').addClass('col-sm-6');
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
        $('#book_select').val(paramBook);
    }

    // When translation changes, update chapter options
    $('#translation_select').change(function() {
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
                    response.comments.forEach(function(comment) {
                        let date = new Date(comment.created_at);
                        let formattedDate = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
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
            $('#book-description').text(desc.length > 120 ? desc.substring(0, 120) + '…' : (desc || '—'));
            // Pre-populate modal fields
            $('#book-edit-title').text(book.name);
            $('#book-edit-author').val(book.author || '');
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
                description: $('#book-edit-description').val(),
            },
            success: function() {
                var savedDesc = $('#book-edit-description').val();
                $('#book-author').text($('#book-edit-author').val() || '—');
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
                let html = '<p>';
                response.forEach(function(verse) {
                    // Add prefix (contains HTML like <br> or <h5>Header</h5>)
                    if (verse.prefix) {
                        html += verse.prefix;
                    }
                    let highlightStyle = verse.has_commentary ? 'background-color: #e0f7fa; padding: 2px 4px; border-radius: 3px;' : '';
                    html += '<span class="verse-clickable" data-verse-id="' + verse.id + '" style="cursor: pointer; ' + highlightStyle + '">';
                    html += '<sup class="text-muted">' + verse.number + '</sup> ' + verse.text;
                    html += '</span> ';
                });
                html += '</p>';
                $('#chapter'+side+'_content').html(html);
                if (!side) updateChapterNavLabel();
            }
        });
    }
</script>
@endpush