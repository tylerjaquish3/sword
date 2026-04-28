<!-- Verse Edit Modal -->
<div class="modal fade" id="verseModal" tabindex="-1" aria-labelledby="verseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="modal-header sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-book-open-variant"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="verseModalLabel">Verse</h5>
                        <p class="sword-modal-subtitle mb-0">Commentary &amp; formatting</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">
                <input type="hidden" id="modal_verse_id">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                        <span class="sword-modal-section-title">Verse Text</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <p id="modal_verse_text" class="mb-0 sword-modal-preview" style="font-style:italic;"></p>
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-comment-text-multiple"></i></span>
                        <span class="sword-modal-section-title">Comments</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <div id="modal_comments_list" class="mb-3" style="max-height:180px;overflow-y:auto;">
                            <p class="text-muted mb-0">No comments yet.</p>
                        </div>
                        <label class="sword-modal-label">Add New Comment</label>
                        <textarea class="sword-modal-textarea" id="modal_commentary" rows="2" placeholder="Add a new comment…" style="border-top:1px solid #f0ebe2 !important;"></textarea>
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-palette"></i></span>
                        <span class="sword-modal-section-title">Highlight</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="highlight-btn" data-color="yellow" title="Important" style="background:#fef08a;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                            <button type="button" class="highlight-btn" data-color="blue"   title="Prophecy"  style="background:#93c5fd;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                            <button type="button" class="highlight-btn" data-color="green"  title="Promise"   style="background:#86efac;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                            <button type="button" class="highlight-btn" data-color="red"    title="Command"   style="background:#fca5a5;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                            <span class="text-muted ms-1" style="font-size:0.75rem;">Click again to remove</span>
                        </div>
                        <div class="d-flex gap-3 mt-2" style="font-size:0.72rem;color:#9ca3af;">
                            <span><span style="display:inline-block;width:10px;height:10px;background:#fef08a;border-radius:2px;"></span> Important</span>
                            <span><span style="display:inline-block;width:10px;height:10px;background:#93c5fd;border-radius:2px;"></span> Prophecy</span>
                            <span><span style="display:inline-block;width:10px;height:10px;background:#86efac;border-radius:2px;"></span> Promise</span>
                            <span><span style="display:inline-block;width:10px;height:10px;background:#fca5a5;border-radius:2px;"></span> Command</span>
                        </div>
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-star-outline" id="modal_favorite_icon"></i></span>
                        <span class="sword-modal-section-title">Favorite</span>
                        <button type="button" id="modal_favorite_btn" class="btn btn-sm ms-auto" style="font-size:0.78rem;">
                            <i class="mdi mdi-star-outline me-1"></i><span id="modal_favorite_label">Mark as Favorite</span>
                        </button>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-format-text"></i></span>
                        <span class="sword-modal-section-title">Formatting</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <input class="form-check-input mt-0" type="checkbox" id="modal_line_break">
                                <label class="form-check-label" for="modal_line_break" style="font-size:0.88rem;color:#374151;">
                                    Start new paragraph before this verse
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="modal_section_title" class="sword-modal-label">Section Title <span class="sword-modal-optional">optional</span></label>
                            <input type="text" class="form-control sword-modal-input" id="modal_section_title" placeholder="e.g., The Beatitudes">
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn sword-modal-btn-save" id="saveVerseBtn">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@push('js')
<script>
    const highlightBgColors = {
        yellow: '#fef9c3',
        blue:   '#dbeafe',
        green:  '#dcfce7',
        red:    '#fee2e2',
    };

    const highlightBorderColors = {
        yellow: '#ca8a04',
        blue:   '#2563eb',
        green:  '#16a34a',
        red:    '#dc2626',
    };

    function setHighlightButtons(activeColor) {
        $('.highlight-btn').each(function() {
            const color = $(this).data('color');
            $(this).css('border-color', color === activeColor ? highlightBorderColors[color] : 'transparent');
        });
    }

    function setFavoriteBtn(isFavorite) {
        if (isFavorite) {
            $('#modal_favorite_btn').removeClass('btn-outline-secondary').addClass('btn-warning');
            $('#modal_favorite_btn i').removeClass('mdi-star-outline').addClass('mdi-star');
            $('#modal_favorite_label').text('Favorited');
        } else {
            $('#modal_favorite_btn').removeClass('btn-warning').addClass('btn-outline-secondary');
            $('#modal_favorite_btn i').removeClass('mdi-star').addClass('mdi-star-outline');
            $('#modal_favorite_label').text('Mark as Favorite');
        }
    }

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

                    // Highlight buttons
                    setHighlightButtons(response.highlight_color || null);

                    // Favorite button
                    setFavoriteBtn(response.is_favorite || false);

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

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('verseModal')).show();
                }
            });
        });

        // Highlight color toggle
        $(document).on('click', '.highlight-btn', function() {
            const verseId = $('#modal_verse_id').val();
            const color   = $(this).data('color');
            $.ajax({
                url: '{{ route("verse-highlights.toggle") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', verse_id: verseId, color: color },
                success: function(response) {
                    setHighlightButtons(response.color);
                    if (typeof lookupVerses === 'function') { lookupVerses(''); lookupVerses(2); }
                }
            });
        });

        // Favorite toggle
        $(document).on('click', '#modal_favorite_btn', function() {
            const verseId = $('#modal_verse_id').val();
            $.ajax({
                url: '{{ route("verse-favorites.toggle") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', verse_id: verseId },
                success: function(response) {
                    setFavoriteBtn(response.favorite);
                    if (typeof lookupVerses === 'function') { lookupVerses(''); lookupVerses(2); }
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
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('verseModal')).hide();
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
                    Swal.fire({ icon: 'error', text: 'Error deleting comment' });
                }
            });
        });
    });
</script>
@endpush
