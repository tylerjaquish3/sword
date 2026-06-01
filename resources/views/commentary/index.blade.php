@extends('base.layout')

@section('title', 'Commentary')

@section('content')  

<div class="d-flex align-items-center justify-content-between mb-4 mb-xl-0">
    <div>
        <h3 class="text-dark font-weight-bold mb-2">Commentary</h3>
        <p class="page-subtitle mb-0">{{ count($chapterComments) }} chapter &middot; {{ count($verseComments) }} verse</p>
    </div>
    <div class="flex-shrink-0">
        <a href="{{ route('commentary.create') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
            <i class="mdi mdi-plus"></i> New Commentary
        </a>
    </div>
</div>

<!-- Chapter Comments Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="sword-section-header mb-0">
                    <span class="section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                    <span class="section-title">Chapter Comments</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-chapter-comments" class="table table-bordered table-hover table-compact">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Comment</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chapterComments as $comment)
                                <tr>
                                    <td>
                                        <a class="sword-link" href="{{ route('translations.index', ['book' => $comment->chapter->book_id ?? '', 'chapter' => $comment->chapter->number ?? '']) }}">
                                            {{ $comment->chapter->book->name ?? 'N/A' }} {{ $comment->chapter->number ?? '' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if(strlen($comment->comment) > 160)
                                            <span class="snip-short">{{ Str::limit($comment->comment, 160) }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.72rem; margin-left: 4px;">More</a></span>
                                            <span class="snip-full" style="display:none">{{ $comment->comment }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.72rem; margin-left: 4px;">Less</a></span>
                                        @else
                                            {{ $comment->comment }}
                                        @endif
                                    </td>
                                    <td data-order="{{ $comment->created_at->format('Y-m-d H:i:s') }}">{{ $comment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary open-chapter-modal" style="padding: 1px 6px; font-size: 0.75rem; line-height: 1.4;" 
                                            data-chapter-id="{{ $comment->chapter_id }}"
                                            data-book-id="{{ $comment->chapter->book_id ?? '' }}"
                                            data-chapter-number="{{ $comment->chapter->number ?? '' }}">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verse Comments Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="sword-section-header mb-0">
                    <span class="section-icon"><i class="mdi mdi-comment-text-outline"></i></span>
                    <span class="section-title">Verse Comments</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-verse-comments" class="table table-bordered table-hover table-compact">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Comment</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($verseComments as $comment)
                                <tr>
                                    <td>
                                        <a class="sword-link" href="{{ route('translations.index', ['book' => $comment->chapter->book_id ?? '', 'chapter' => $comment->chapter->number ?? '']) }}">
                                            {{ $comment->chapter->book->name ?? 'N/A' }} {{ $comment->chapter->number ?? '' }}:{{ $comment->verse_number ?? '' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if(strlen($comment->comment) > 120)
                                            <span class="snip-short">{{ Str::limit($comment->comment, 120) }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.72rem; margin-left: 4px;">More</a></span>
                                            <span class="snip-full" style="display:none">{{ $comment->comment }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.72rem; margin-left: 4px;">Less</a></span>
                                        @else
                                            {{ $comment->comment }}
                                        @endif
                                    </td>
                                    <td data-order="{{ $comment->created_at->format('Y-m-d H:i:s') }}">{{ $comment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary open-verse-modal" style="padding: 1px 6px; font-size: 0.75rem; line-height: 1.4;" 
                                            data-chapter-id="{{ $comment->chapter_id }}"
                                            data-verse-number="{{ $comment->verse_number }}">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('commentary.modals.verse')
@include('commentary.modals.chapter')

@endsection

@push('css')
<style>
.table-compact th,
.table-compact td {
    padding: 0.28rem 0.55rem !important;
    font-size: 0.82rem !important;
    vertical-align: middle !important;
}
.table-compact thead th {
    background: #0e1628 !important;
    color: #fff !important;
    font-size: 0.72rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.06em !important;
    font-weight: 700 !important;
    border-color: #1e2d44 !important;
}
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        $('#datatable-chapter-comments').DataTable({
            "order": [[2, "desc"]],
            "pageLength": 10,
            "lengthChange": false,
            "info": false
        });
        $('#datatable-verse-comments').DataTable({
            "order": [[2, "desc"]],
            "pageLength": 10,
            "lengthChange": false,
            "info": false
        });

        // Open verse modal when clicking Edit button
        $(document).on('click', '.open-verse-modal', function() {
            let verseId = $(this).data('verse-id');
            let chapterId = $(this).data('chapter-id');
            let verseNumber = $(this).data('verse-number');
            
            // Determine which endpoint to use
            let url = verseId 
                ? '/translations/verse/' + verseId 
                : '/translations/verse-by-location?chapter_id=' + chapterId + '&verse_number=' + verseNumber;
            
            // Fetch verse data
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#modal_verse_id').val(response.verse.id);
                    $('#verseModalLabel').text(response.reference);
                    $('#modal_verse_text').text(response.verse.text);
                    $('#modal_commentary').val('');
                    
                    // Parse the prefix to set checkbox and section title
                    let prefix = response.verse.prefix || '';
                    let hasLineBreak = prefix.includes('</p><p>') || prefix.includes('<br>');
                    let sectionTitle = '';
                    
                    // Extract section title if present (look for <h5>...</h5>)
                    let titleMatch = prefix.match(/<h5[^>]*>([^<]*)<\/h5>/);
                    if (titleMatch) {
                        sectionTitle = titleMatch[1];
                    }
                    
                    $('#modal_line_break').prop('checked', hasLineBreak || sectionTitle);
                    $('#modal_section_title').val(sectionTitle);
                    
                    // Build comments list
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
                            commentsHtml += '<div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-start">';
                            commentsHtml += '<div>';
                            commentsHtml += '<small class="text-muted">' + formattedDate + '</small>';
                            commentsHtml += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
                            commentsHtml += '</div>';
                            commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-verse-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                            commentsHtml += '</div>';
                        });
                    } else {
                        commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                    }
                    $('#modal_comments_list').html(commentsHtml);
                    
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('verseModal')).show();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching verse:', error);
                    Swal.fire({ icon: 'error', text: 'Error loading verse data' });
                }
            });
        });

        // Open chapter modal when clicking Edit button
        $(document).on('click', '.open-chapter-modal', function() {
            let bookId = $(this).data('book-id');
            let chapterNumber = $(this).data('chapter-number');
            let chapterId = $(this).data('chapter-id');
            
            // Fetch chapter data
            $.ajax({
                url: '/chapters/comments?book_id=' + bookId + '&chapter_number=' + chapterNumber,
                type: 'GET',
                success: function(response) {
                    $('#modal_chapter_id').val(response.chapter.id);
                    $('#chapterModalLabel').text(response.reference + ' - Chapter Notes');
                    $('#modal_chapter_commentary').val('');
                    
                    // Build comments list
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
                            commentsHtml += '<div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-start">';
                            commentsHtml += '<div>';
                            commentsHtml += '<small class="text-muted">' + formattedDate + '</small>';
                            commentsHtml += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
                            commentsHtml += '</div>';
                            commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-chapter-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                            commentsHtml += '</div>';
                        });
                    } else {
                        commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                    }
                    $('#modal_chapter_comments_list').html(commentsHtml);
                    
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('chapterModal')).show();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching chapter:', error);
                    Swal.fire({ icon: 'error', text: 'Error loading chapter data' });
                }
            });
        });

        // Handle delete verse comment
        $(document).on('click', '.delete-verse-comment', function() {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }

            let commentId = $(this).data('comment-id');
            let btn = $(this);

            $.ajax({
                url: '/commentary/verse/' + commentId,
                type: 'DELETE',
                complete: function(xhr) {
                    if (xhr.status === 200 || xhr.status === 204) {
                        btn.closest('.d-flex').remove();
                        if ($('#modal_comments_list .d-flex').length === 0) {
                            $('#modal_comments_list').html('<p class="text-muted mb-0">No comments yet.</p>');
                        }
                    } else {
                        Swal.fire({ icon: 'error', text: 'Error deleting comment' });
                    }
                }
            });
        });

        // Snip toggle for More/Less in comment cells
        $(document).on('click', '.snip-toggle', function(e) {
            e.preventDefault();
            var td = $(this).closest('td');
            td.find('.snip-short').toggle();
            td.find('.snip-full').toggle();
        });

        // Handle delete chapter comment
        $(document).on('click', '.delete-chapter-comment', function() {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }

            let commentId = $(this).data('comment-id');
            let btn = $(this);

            $.ajax({
                url: '/commentary/chapter/' + commentId,
                type: 'DELETE',
                complete: function(xhr) {
                    if (xhr.status === 200 || xhr.status === 204) {
                        btn.closest('.d-flex').remove();
                        if ($('#modal_chapter_comments_list .d-flex').length === 0) {
                            $('#modal_chapter_comments_list').html('<p class="text-muted mb-0">No comments yet.</p>');
                        }
                    } else {
                        Swal.fire({ icon: 'error', text: 'Error deleting comment' });
                    }
                }
            });
        });
    });
</script>
@endpush
