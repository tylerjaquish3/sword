<!-- Verse Edit Modal -->
<div class="modal fade" id="verseModal" tabindex="-1" aria-labelledby="verseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verseModalLabel">Edit Verse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_verse_id">
                
                <!-- Verse Text Section -->
                <div class="card mb-4">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h6 class="mb-0"><i class="mdi mdi-book-open-variant me-2"></i>Verse Text</h6>
                    </div>
                    <div class="card-body">
                        <p id="modal_verse_text" class="mb-0 fst-italic"></p>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card mb-4">
                    <div class="card-header bg-success bg-opacity-10">
                        <h6 class="mb-0"><i class="mdi mdi-comment-text-multiple me-2"></i>Comments</h6>
                    </div>
                    <div class="card-body">
                        <div id="modal_comments_list" class="mb-3" style="max-height: 180px; overflow-y: auto;">
                            <p class="text-muted mb-0">No comments yet.</p>
                        </div>
                        <hr>
                        <label for="modal_commentary" class="form-label text-muted small">Add New Comment</label>
                        <textarea class="form-control" id="modal_commentary" rows="2" placeholder="Add a new comment..."></textarea>
                    </div>
                </div>

                <!-- Formatting Section -->
                <div class="card">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="mb-0"><i class="mdi mdi-format-text me-2"></i>Formatting</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input mt-0 me-2" type="checkbox" id="modal_line_break">
                                <label class="form-check-label" for="modal_line_break">
                                    Start new paragraph (line break before this verse)
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="modal_section_title" class="form-label text-muted small">Section Title (optional)</label>
                            <input type="text" class="form-control" id="modal_section_title" placeholder="e.g., The Beatitudes">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveVerseBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Handle verse click to open modal
        $(document).on('click', '.verse-clickable', function() {
            let verseId = $(this).data('verse-id');
            
            // Fetch verse data
            $.ajax({
                url: '/translations/verse/' + verseId,
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
                            commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger delete-verse-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                            commentsHtml += '</div>';
                        });
                    } else {
                        commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                    }
                    $('#modal_comments_list').html(commentsHtml);
                    
                    $('#verseModal').modal('show');
                }
            });
        });

        // Handle save button click
        $('#saveVerseBtn').click(function() {
            let verseId = $('#modal_verse_id').val();
            let lineBreak = $('#modal_line_break').is(':checked');
            let sectionTitle = $('#modal_section_title').val().trim();
            let commentary = $('#modal_commentary').val();
            
            $.ajax({
                url: '/translations/verse/' + verseId,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    line_break: lineBreak,
                    section_title: sectionTitle,
                    commentary: commentary
                },
                success: function(response) {
                    if (response.success) {
                        $('#verseModal').modal('hide');
                        // Refresh the verses if the function exists (translations page)
                        if (typeof lookupVerses === 'function') {
                            lookupVerses('');
                            lookupVerses(2);
                        }
                    }
                }
            });
        });

        // Handle delete verse comment
        $(document).on('click', '.delete-verse-comment', function() {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            let commentId = $(this).data('comment-id');
            let verseId = $('#modal_verse_id').val();
            
            $.ajax({
                url: '/commentary/verse/' + commentId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Refresh the modal by re-fetching verse data
                    $.ajax({
                        url: '/translations/verse/' + verseId,
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
                                    commentsHtml += '<button type="button" class="btn btn-sm btn-outline-danger delete-verse-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
                                    commentsHtml += '</div>';
                                });
                            } else {
                                commentsHtml = '<p class="text-muted mb-0">No comments yet.</p>';
                            }
                            $('#modal_comments_list').html(commentsHtml);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    alert('Error deleting comment');
                }
            });
        });
    });
</script>
@endpush
