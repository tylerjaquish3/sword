@extends('base.layout')

@section('title', 'Translation')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Translations</h3>
                <h6 class="font-weight-normal mb-2">Last login was 23 hours ago. View details</h6>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                    <button type="button" class="btn bg-white btn-icon">
                        <i class="mdi mdi-view-grid text-success"></i>
                </button>
                    <button type="button" class="btn bg-white btn-icon ms-2">
                        <i class="mdi mdi-format-list-bulleted font-weight-bold text-primary"></i>
                    </button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Feedback
                    <i class="mdi mdi-message-outline btn-icon-append"></i>                          
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Help
                    <i class="mdi mdi-help-circle-outline btn-icon-append"></i>                          
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Print
                    <i class="mdi mdi-printer btn-icon-append"></i>                          
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">List of Translations</h4>
                </div>
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
            </div>
        </div>
    </div>
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
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

@endsection


@push('js')
<script>

$(document).ready(function() {

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
        });
    });
    
    // When book changes, update chapter options and both sides
    $('#book_select').change(function() {
        book_id = $(this).val();
        loadChapters(book_id, function() {
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
        });
    });

    // When chapter changes, update verses in both sides
    $('#chapter_select').change(function() {
        lookupVerses('');
        lookupVerses(2);
        loadChapterComments();
    });

    // When translation2 changes, update the compare side
    $('#translation2_select').change(function() {
        lookupVerses(2);
    });

    // Load chapters for the initially selected book on page load
    loadChapters($('#book_select').val(), function() {
        // Set chapter from query param after chapters are loaded
        if (paramChapter) {
            $('#chapter_select').val(paramChapter);
        }
        lookupVerses('');
        lookupVerses(2);
        loadChapterComments();
    });

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