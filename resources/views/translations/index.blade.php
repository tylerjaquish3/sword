@extends('base.layout')

@section('title', 'Translation')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Translations</h3>
                <h6 class="font-weight-normal mb-2">
                    @if($lastLogin)
                        Last login: {{ $lastLogin->logged_in_at->diffForHumans() }}
                    @else
                        Welcome!
                    @endif
                </h6>
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
                                <option value="{{ $translation->id }}">{{ $translation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-select" id="book_select">
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}">{{ $book->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-select" id="chapter_select">
                            <option value=1>1</option>
                        </select>
                    </div>
                </div>
                <div id="book-info" class="mt-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div id="book-author" class="text-muted small mb-1"></div>
                            <div id="book-description" class="small"></div>
                        </div>
                        <button type="button" id="btn-edit-book" class="btn btn-sm btn-secondary ms-3 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                            <i class="mdi mdi-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter_content"></div>
                
                <hr class="my-3">
                
                <div class="mb-3">
                    <h6 class="fw-bold"><i class="mdi mdi-comment-text-outline"></i> Chapter Notes</h6>
                    <div id="chapter_comments_display" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                        <p class="text-muted mb-0">No chapter notes yet.</p>
                    </div>
                </div>
                <a href="#" id="chapter_comment_link" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-plus"></i> Add Chapter Note</a>
                <button type="button" id="btn-mark-read" class="btn btn-outline-success btn-sm ms-2"><i class="mdi mdi-check"></i> Mark as Read</button>
                <small id="read-status-display" class="text-muted ms-2"></small>
            </div>
        </div>
    </div>
    <div id="compare-col" class="col-sm-6 grid-margin grid-margin-md-0 stretch-card d-none">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Compare</h4>
                </div>
                <div class="row">
                    <div class="col-12">
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
                    $('#book_select').val(last.book_id);
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
            $('#book-author').text('Author: ' + (book.author || ''));
            $('#book-description').text('Description: ' + (desc.length > 50 ? desc.substring(0, 50) + '…' : desc));
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
                $('#book-author').text('Author: ' + $('#book-edit-author').val());
                $('#book-description').text('Description: ' + (savedDesc.length > 50 ? savedDesc.substring(0, 50) + '…' : savedDesc));
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
            }
        });
    }
</script>
@endpush