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
                        <div id="modal_verse_text" class="mb-2 sword-modal-preview" style="font-style:italic;"></div>
                        <div class="d-flex gap-2 mt-1">
                            <button type="button" id="verse_range_add" class="btn btn-sm btn-outline-secondary" title="Add next verse to range" style="font-size:0.75rem;padding:2px 8px;">
                                <i class="mdi mdi-plus"></i>
                            </button>
                            <button type="button" id="verse_range_remove" class="btn btn-sm btn-outline-secondary d-none" title="Remove last verse from range" style="font-size:0.75rem;padding:2px 8px;">
                                <i class="mdi mdi-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="modal_verse_number">
                <input type="hidden" id="modal_end_verse_number">
                <input type="hidden" id="modal_book_name">
                <input type="hidden" id="modal_book_id">
                <input type="hidden" id="modal_chapter_number">

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
                        <div class="d-flex gap-2 ms-auto">
                            <button type="button" id="verse_memory_btn" class="btn btn-sm btn-outline-secondary" style="font-size:0.78rem;" title="Add to Memory">
                                <i class="mdi mdi-brain me-1"></i><span>Memory</span>
                            </button>
                            <button type="button" id="modal_favorite_btn" class="btn btn-sm" style="font-size:0.78rem;">
                                <i class="mdi mdi-star-outline me-1"></i><span id="modal_favorite_label">Favorite</span>
                            </button>
                        </div>
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
    let chapterVersesList = [];  // [{id, number, text}, ...]

    function buildVerseReference(bookName, chapterNum, startNum, endNum) {
        let ref = bookName + ' ' + chapterNum + ':' + startNum;
        if (endNum && endNum > startNum) ref += '-' + endNum;
        return ref;
    }

    function renderVerseTexts(startNum, endNum) {
        let html = '';
        chapterVersesList.forEach(function(v) {
            if (v.number >= startNum && v.number <= (endNum || startNum)) {
                html += '<sup style="font-size:0.7em;margin-right:2px;">' + v.number + '</sup>' + v.text + ' ';
            }
        });
        $('#modal_verse_text').html(html);
    }

    function buildCommentsHtml(comments) {
        if (!comments || comments.length === 0) {
            return '<p class="text-muted mb-0">No comments yet.</p>';
        }
        let html = '';
        comments.forEach(function(comment) {
            let date = new Date(comment.created_at);
            let formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            let rangeLabel = '';
            if (comment.end_verse_number && comment.end_verse_number > comment.verse_number) {
                rangeLabel = '<span class="badge bg-secondary ms-2" style="font-size:0.65rem;font-weight:500;">vv. ' + comment.verse_number + '–' + comment.end_verse_number + '</span>';
            }
            html += '<div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-start">';
            html += '<div>';
            html += '<small class="text-muted">' + formattedDate + '</small>' + rangeLabel;
            html += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
            html += '</div>';
            html += '<button type="button" class="btn btn-sm btn-outline-danger delete-verse-comment" data-comment-id="' + comment.id + '" title="Delete comment"><i class="mdi mdi-delete"></i></button>';
            html += '</div>';
        });
        return html;
    }

    function updateVerseRangeUI() {
        let startNum = parseInt($('#modal_verse_number').val());
        let endNum   = parseInt($('#modal_end_verse_number').val());
        let maxVerse = chapterVersesList.length > 0 ? chapterVersesList[chapterVersesList.length - 1].number : startNum;

        renderVerseTexts(startNum, endNum);

        let bookName     = $('#modal_book_name').val();
        let chapterNum   = $('#modal_chapter_number').val();
        $('#verseModalLabel').text(buildVerseReference(bookName, chapterNum, startNum, endNum));

        $('#verse_range_add').toggleClass('d-none', endNum >= maxVerse);
        $('#verse_range_remove').toggleClass('d-none', endNum <= startNum);
    }

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
            $('#modal_favorite_btn').removeClass('btn-outline-warning').addClass('btn-warning');
            $('#modal_favorite_btn i').removeClass('mdi-star-outline').addClass('mdi-star');
            $('#modal_favorite_label').text('Favorited');
        } else {
            $('#modal_favorite_btn').removeClass('btn-warning').addClass('btn-outline-warning');
            $('#modal_favorite_btn i').removeClass('mdi-star').addClass('mdi-star-outline');
            $('#modal_favorite_label').text('Favorite');
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
                    chapterVersesList = response.chapter_verses || [];
                    let verseNum = response.verse.number;

                    $('#modal_verse_id').val(response.verse.id);
                    $('#modal_verse_number').val(verseNum);
                    $('#modal_end_verse_number').val(verseNum);
                    $('#modal_book_name').val(response.book_name);
                    $('#modal_book_id').val(response.verse.chapter.book.id);
                    $('#modal_chapter_number').val(response.chapter_number);
                    $('#modal_commentary').val('');

                    updateVerseRangeUI();

                    // Parse the prefix to set checkbox and section title
                    let prefix = response.verse.prefix || '';
                    let hasLineBreak = prefix.includes('</p><p>') || prefix.includes('<br>');
                    let sectionTitle = '';

                    // Extract section title if present (look for <h5>...</h5>)
                    let titleMatch = prefix.match(/<h5[^>]*>([^<]*)<\/h5>/);
                    if (titleMatch) {
                        var ta = document.createElement('textarea');
                        ta.innerHTML = titleMatch[1];
                        sectionTitle = ta.value;
                    }

                    $('#modal_line_break').prop('checked', hasLineBreak || sectionTitle);
                    $('#modal_section_title').val(sectionTitle);

                    // Highlight buttons
                    setHighlightButtons(response.highlight_color || null);

                    // Favorite button
                    setFavoriteBtn(response.is_favorite || false);

                    // Build comments list
                    $('#modal_comments_list').html(buildCommentsHtml(response.comments));

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('verseModal')).show();
                }
            });
        });

        // Verse range: add next verse
        $('#verse_range_add').on('click', function() {
            let endNum = parseInt($('#modal_end_verse_number').val());
            let maxVerse = chapterVersesList.length > 0 ? chapterVersesList[chapterVersesList.length - 1].number : endNum;
            if (endNum < maxVerse) {
                $('#modal_end_verse_number').val(endNum + 1);
                updateVerseRangeUI();
            }
        });

        // Verse range: remove last verse
        $('#verse_range_remove').on('click', function() {
            let startNum = parseInt($('#modal_verse_number').val());
            let endNum   = parseInt($('#modal_end_verse_number').val());
            if (endNum > startNum) {
                $('#modal_end_verse_number').val(endNum - 1);
                updateVerseRangeUI();
            }
        });

        // Highlight color toggle
        $(document).on('click', '.highlight-btn', function() {
            const verseId = $('#modal_verse_id').val();
            const color   = $(this).data('color');
            const startNum = parseInt($('#modal_verse_number').val());
            const endNum   = parseInt($('#modal_end_verse_number').val());
            $.ajax({
                url: '{{ route("verse-highlights.toggle") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    verse_id: verseId,
                    color: color,
                    end_verse_number: endNum > startNum ? endNum : null
                },
                success: function(response) {
                    setHighlightButtons(response.color);
                    if (typeof lookupVerses === 'function') { lookupVerses(''); lookupVerses(2); }
                }
            });
        });

        // Memory button — close modal and navigate to /memory with verse prefilled
        $(document).on('click', '#verse_memory_btn', function() {
            const verseId = $('#modal_verse_id').val();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('verseModal')).hide();
            window.location.href = '/memory?verse_id=' + verseId;
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

        // Auto title-case the Section Title input as the user types
        $('#modal_section_title').on('input', function() {
            var pos = this.selectionStart;
            var titled = $(this).val().replace(/\w\S*/g, function(w) {
                return w.charAt(0).toUpperCase() + w.slice(1);
            });
            $(this).val(titled);
            this.setSelectionRange(pos, pos);
        });

        // Handle save button click
        $('#saveVerseBtn').click(function() {
            let verseId      = $('#modal_verse_id').val();
            let lineBreak    = $('#modal_line_break').is(':checked');
            let sectionTitle = $('#modal_section_title').val().trim();
            let commentary   = $('#modal_commentary').val();
            let startNum     = parseInt($('#modal_verse_number').val());
            let endNum       = parseInt($('#modal_end_verse_number').val());

            $.ajax({
                url: '/translations/verse/' + verseId,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    line_break: lineBreak ? 1 : 0,
                    section_title: sectionTitle,
                    commentary: commentary,
                    end_verse_number: endNum > startNum ? endNum : null
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
                            $('#modal_comments_list').html(buildCommentsHtml(response.comments));
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
