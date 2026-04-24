<!-- Chapter Comment Modal -->
<div class="modal fade" id="chapterModal" tabindex="-1" aria-labelledby="chapterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="modal-header sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-note-text-outline"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="chapterModalLabel">Chapter Notes</h5>
                        <p class="sword-modal-subtitle mb-0">Commentary on this chapter</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">
                <input type="hidden" id="modal_chapter_id">

                <div class="sword-modal-section">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-comment-text-multiple"></i></span>
                        <span class="sword-modal-section-title">Comments</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <div id="modal_chapter_comments_list" class="mb-3" style="max-height:250px;overflow-y:auto;">
                            <p class="text-muted mb-0">No comments yet.</p>
                        </div>
                        <label class="sword-modal-label">Add New Comment</label>
                        <textarea class="sword-modal-textarea" id="modal_chapter_commentary" rows="2" placeholder="Add a new comment about this chapter…" style="border-top:1px solid #f0ebe2 !important;"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn sword-modal-btn-save" id="saveChapterBtn">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save Comment
                </button>
            </div>

        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    // Handle chapter comment link click
    $(document).on('click', '#chapter_comment_link', function(e) {
        e.preventDefault();
        let bookId = $('#book_select').val();
        let chapterNumber = $('#chapter_select').val();

        // Fetch chapter data
        $.ajax({
            url: '/chapters/comments?book_id=' + bookId + '&chapter_number=' + chapterNumber,
            type: 'GET',
            success: function(response) {
                $('#modal_chapter_id').val(response.chapter.id);
                $('#chapterModalLabel').text(response.reference + ' — Chapter Notes');
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
                        commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger delete-chapter-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                        commentsHtml += '</div>';
                    });
                } else {
                    commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                }
                $('#modal_chapter_comments_list').html(commentsHtml);

                $('#chapterModal').modal('show');
            }
        });
    });

    // Handle save chapter comment button click
    $('#saveChapterBtn').click(function() {
        let chapterId = $('#modal_chapter_id').val();
        let commentary = $('#modal_chapter_commentary').val();

        if (!commentary.trim()) {
            return;
        }

        $.ajax({
            url: '/chapters/' + chapterId + '/comment',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                comment: commentary
            },
            success: function(response) {
                if (response.success) {
                    // Refresh the modal comments list
                    $('#chapter_comment_link').click();
                    $('#modal_chapter_commentary').val('');
                    // Also refresh the displayed comments on the page if function exists
                    if (typeof loadChapterComments === 'function') {
                        loadChapterComments();
                    }
                }
            }
        });
    });

    // Handle delete chapter comment
    $(document).on('click', '.delete-chapter-comment', function() {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }

        let commentId = $(this).data('comment-id');
        let chapterId = $('#modal_chapter_id').val();

        $.ajax({
            url: '/commentary/chapter/' + commentId,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Refresh the modal by re-fetching chapter data
                $.ajax({
                    url: '/chapters/comments?chapter_id=' + chapterId,
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
                                commentsHtml += '<div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-start">';
                                commentsHtml += '<div>';
                                commentsHtml += '<small class="text-muted">' + formattedDate + '</small>';
                                commentsHtml += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
                                commentsHtml += '</div>';
                                commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger delete-chapter-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                                commentsHtml += '</div>';
                            });
                        } else {
                            commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                        }
                        $('#modal_chapter_comments_list').html(commentsHtml);
                    }
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({ icon: 'error', text: 'Error deleting comment' });
            }
        });
    });
});
</script>
@endpush
