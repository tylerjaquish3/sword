@extends('base.layout')

@section('title', 'Edit Topic: ' . $topic->name)

@section('content')  

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('topics.index') }}" style="font-size: 0.78rem; color: var(--sword-gold); text-decoration: none;">
                <i class="mdi mdi-arrow-left"></i> Study Hub
            </a>
        </div>
        <h3 class="topic-hero-title mb-0">{{ $topic->name }}</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="color: var(--sword-navy);">Topic Details</h4>
                <button class="btn btn-sm" id="edit-topic-btn" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
                    <i class="mdi mdi-pencil"></i>
                </button>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-2">Name</dt>
                    <dd class="col-sm-10 mb-3" id="display-name">{{ $topic->name }}</dd>

                    <dt class="col-sm-2">Description</dt>
                    <dd class="col-sm-10 mb-3" id="display-description">
                        @if($topic->description)
                            {{ $topic->description }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-2">Keywords</dt>
                    <dd class="col-sm-10 mb-0" id="display-keywords">
                        @if($topic->keywords)
                            @foreach(explode(',', $topic->keywords) as $keyword)
                                <span class="badge btn-success me-1 mb-1 keyword-filter" style="cursor:pointer;" data-keyword="{{ trim($keyword) }}">{{ trim($keyword) }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No keywords defined</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Notes Section -->
<div class="row mt-4" id="notes-section">
    <div class="col-12">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="color: var(--sword-navy);">Notes</h4>
                <button class="btn btn-sm" id="add-note-btn" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
                    <i class="mdi mdi-plus"></i> Add Note
                </button>
            </div>
            <div class="card-body" id="notes-list">
                @forelse($notes as $note)
                    <div class="note-item border-bottom pb-3 mb-3" id="note-{{ $note->id }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <p class="mb-2" style="white-space:pre-wrap;">{{ $note->note }}</p>
                            <button class="btn btn-sm btn-outline-danger delete-note-btn ms-3" data-note-id="{{ $note->id }}">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                        @if($note->verses->isNotEmpty())
                            <div class="mt-1">
                                @foreach($note->verses as $verse)
                                    <a class="sword-link badge btn-outline-secondary me-1"
                                       href="{{ route('translations.index') }}?translation={{ $verse->translation_id }}&book={{ $verse->chapter->book->id }}&chapter={{ $verse->chapter->number }}"
                                       style="font-size:0.8rem;">
                                        {{ $verse->reference }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <small class="text-muted">{{ $note->created_at->format('M j, Y g:i A') }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0" id="no-notes-msg">No notes yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Matching Verses Section -->
@php
    $groupedVerses = $matchingVerses->groupBy('reference');
@endphp
<div class="row mt-4">
    <div class="col-12">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-header">
                <h4 class="card-title mb-1" style="color: var(--sword-navy);">Related Verses</h4>
                <p class="text-muted mb-0">{{ $groupedVerses->count() }} references found ({{ $matchingVerses->count() }} total matches)</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-matching-verses" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width:32px;"></th>
                                <th>Keyword</th>
                                <th>Reference</th>
                                <th>Text</th>
                                <th>Translation</th>
                                <th>Testament</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedVerses as $reference => $verses)
                                @php
                                    $first = $verses->first();
                                    $hasMultiple = $verses->count() > 1;
                                    $allKeywords = $verses->pluck('matched_keyword')->unique()->values();
                                    $extraTranslations = $hasMultiple ? $verses->skip(1)->map(fn($v) => [
                                        'keyword'     => $v->matched_keyword,
                                        'translation' => $v->translation->name ?? 'N/A',
                                        'text'        => $v->text,
                                        'verse_id'    => $v->id,
                                        'url'         => route('translations.index') . '?translation=' . $v->translation_id . '&book=' . $v->chapter->book->id . '&chapter=' . $v->chapter->number,
                                    ])->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : '[]'
                                @endphp
                                <tr data-extra="{{ $extraTranslations }}">
                                    <td class="toggle-cell text-center" style="cursor:{{ $hasMultiple ? 'pointer' : 'default' }};">
                                        @if($hasMultiple)
                                            <span class="toggle-icon" style="color:#1a3a6b;font-size:1rem;line-height:1;user-select:none;">+</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($allKeywords as $kw)
                                            <span class="badge btn-success me-1">{{ $kw }}</span>
                                        @endforeach
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <a class="sword-link" href="{{ route('translations.index') }}?translation={{ $first->translation_id }}&book={{ $first->chapter->book->id }}&chapter={{ $first->chapter->number }}">{{ $reference }}</a>
                                        @if($hasMultiple)
                                            <small class="text-muted ms-1">({{ $verses->count() }})</small>
                                        @endif
                                    </td>
                                    <td><span class="verse-clickable" data-verse-id="{{ $first->id }}" style="cursor:pointer;">{{ $first->text }}</span></td>
                                    <td>{{ $first->translation->name ?? 'N/A' }}</td>
                                    <td><span class="badge {{ $first->chapter->book->new_testament ? 'btn-info' : 'btn-warning' }}">{{ $first->chapter->book->new_testament ? 'NT' : 'OT' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('commentary.modals.verse')

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-note-plus-outline"></i></div>
                    <div>
                        <h5 class="modal-title mb-0">Add Note</h5>
                        <p class="sword-modal-subtitle mb-0">{{ $topic->name }}</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-long"></i></span>
                        <span class="sword-modal-section-title">Note <span class="sword-modal-required">required</span></span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="note-text" rows="5" placeholder="Write your note…"></textarea>
                        <div class="invalid-feedback px-3 pb-2">Note cannot be empty.</div>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-link-variant"></i></span>
                        <span class="sword-modal-section-title">Link Verses <span class="sword-modal-optional">optional</span></span>
                    </div>
                    <div class="sword-modal-section-body">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control sword-modal-input" id="verse-search-input" placeholder="Search by reference or text (e.g. John 3, faith)">
                            <button class="btn sword-modal-btn-add" type="button" id="verse-search-btn" style="border-radius: 0 8px 8px 0;">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </div>
                        <div id="verse-search-results" class="list-group mb-2" style="max-height:200px;overflow-y:auto;display:none;"></div>
                        <div id="linked-verses" class="d-flex flex-wrap gap-1"></div>
                        <input type="hidden" id="linked-verse-ids">
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="save-note-btn">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save Note
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Edit Topic Modal -->
<div class="modal fade" id="editTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-tag-edit-outline"></i></div>
                    <div>
                        <h5 class="modal-title mb-0">Edit Topic</h5>
                        <p class="sword-modal-subtitle mb-0">Update name, description, or keywords</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-box-outline"></i></span>
                        <span class="sword-modal-section-title">Name <span class="sword-modal-required">required</span></span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="modal-name">
                        <div class="invalid-feedback">Name is required.</div>
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-long"></i></span>
                        <span class="sword-modal-section-title">Description <span class="sword-modal-optional">optional</span></span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="modal-description" rows="3"></textarea>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-key-variant"></i></span>
                        <span class="sword-modal-section-title">Keywords <span class="sword-modal-optional">optional</span></span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="modal-keywords" rows="2" placeholder="Comma-separated keywords"></textarea>
                        <p class="sword-modal-hint">Comma-separated — used to surface related verses</p>
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="save-topic-btn">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,700&display=swap" rel="stylesheet">
<style>
    .topic-hero-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(1.5rem, 5vw, 2.8rem);
        font-weight: 700;
        font-style: italic;
        letter-spacing: 0.02em;
        line-height: 1.15;
        color: var(--sword-navy);
        text-shadow:
            1px 2px 0 rgba(0, 0, 0, 0.4),
            2px 5px 12px rgba(0, 0, 0, 0.18);
    }
    /* child row sub-table */
    .child-translations {
        background: rgba(201, 168, 76, 0.04);
        border-top: 1px solid var(--sword-navy-border);
    }
    .child-translations table {
        margin-bottom: 0;
    }
    .child-translations td {
        border-top: 1px solid var(--sword-navy-border) !important;
        padding: 6px 12px;
        vertical-align: top;
    }
    tr.shown td.toggle-cell .toggle-icon {
        content: '−';
    }
    #datatable-matching-verses th:nth-child(4),
    #datatable-matching-verses td:nth-child(4) {
        min-width: 280px;
    }

    @media (max-width: 575px) {
        #datatable-matching-verses_wrapper .dt-length {
            display: none;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        var table = $('#datatable-matching-verses').DataTable({
            "order": [[2, "asc"]],
            "pageLength": 25,
            "columnDefs": [
                { "orderable": false, "targets": 0, "width": "32px" },
                { "width": "10%", "targets": 1 },
                { "width": "1%", "targets": 2 },
                { "width": "52%", "targets": 3 },
                { "width": "10%", "targets": 4 },
                { "width": "8%",  "targets": 5 }
            ]
        });

        // Keyword badge click — filter table
        $(document).on('click', '.keyword-filter', function() {
            table.search($(this).data('keyword')).draw();
        });

        // Toggle child rows for alternate translations
        $('#datatable-matching-verses').on('click', 'td.toggle-cell', function() {
            var tr  = $(this).closest('tr');
            var row = table.row(tr);

            if (!tr.data('extra') || tr.data('extra') === '[]') return;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                tr.find('.toggle-icon').text('+');
            } else {
                var extras = tr.data('extra');
                var html = '<div class="child-translations"><table class="table table-sm mb-0"><tbody>';
                $.each(extras, function(i, t) {
                    html += '<tr>'
                          + '<td style="width:32px;"></td>'
                          + '<td style="width:10%;"><span class="badge btn-success">' + $('<div>').text(t.keyword).html() + '</span></td>'
                          + '<td style="width:12%;"><a class="sword-link" href="' + t.url + '">(same ref)</a></td>'
                          + '<td style="width:52%;"><span class="verse-clickable" data-verse-id="' + t.verse_id + '" style="cursor:pointer;">' + $('<div>').text(t.text).html() + '</span></td>'
                          + '<td style="width:10%;">' + $('<div>').text(t.translation).html() + '</td>'
                          + '<td style="width:8%;"></td>'
                          + '</tr>';
                });
                html += '</tbody></table></div>';
                row.child(html).show();
                tr.addClass('shown');
                tr.find('.toggle-icon').text('−');
            }
        });

        // ── Notes ────────────────────────────────────────────────────
        var linkedVerses = {};

        $('#add-note-btn').on('click', function() {
            $('#note-text').val('').removeClass('is-invalid');
            $('#verse-search-input').val('');
            $('#verse-search-results').hide().empty();
            $('#linked-verses').empty();
            $('#linked-verse-ids').val('');
            linkedVerses = {};
            $('#addNoteModal').modal('show');
        });

        var searchTimer;
        $('#verse-search-input').on('input', function() {
            clearTimeout(searchTimer);
            var q = $(this).val().trim();
            if (q.length < 2) { $('#verse-search-results').hide().empty(); return; }
            searchTimer = setTimeout(function() { runVerseSearch(q); }, 300);
        });
        $('#verse-search-btn').on('click', function() {
            var q = $('#verse-search-input').val().trim();
            if (q.length >= 2) runVerseSearch(q);
        });

        function runVerseSearch(q) {
            $.get('{{ route('topics.verse-search') }}', { q: q }, function(results) {
                var $r = $('#verse-search-results').empty();
                if (!results.length) {
                    $r.html('<div class="list-group-item text-muted">No results</div>').show();
                    return;
                }
                results.forEach(function(v) {
                    $('<button type="button">')
                        .addClass('list-group-item list-group-item-action py-2')
                        .html('<strong>' + $('<span>').text(v.reference).html() + '</strong>'
                            + ' <small class="text-muted">' + $('<span>').text(v.translation).html() + '</small>'
                            + '<br><small class="text-muted">' + $('<span>').text(v.text).html() + '</small>')
                        .on('click', function() {
                            addLinkedVerse(v);
                            $('#verse-search-results').hide().empty();
                            $('#verse-search-input').val('');
                        })
                        .appendTo($r);
                });
                $r.show();
            });
        }

        function addLinkedVerse(v) {
            if (linkedVerses[v.id]) return;
            linkedVerses[v.id] = v;
            updateLinkedVerseIds();
            $('<span>')
                .addClass('badge me-1 mb-1 linked-verse-badge')
                .attr('data-id', v.id)
                .css({'background':'#e8edf5','color':'#1a3a6b','font-size':'0.85rem','cursor':'default'})
                .html($('<span>').text(v.reference).html()
                    + ' <span class="remove-verse" style="cursor:pointer;margin-left:4px;opacity:0.6;">×</span>')
                .appendTo('#linked-verses');
        }

        $(document).on('click', '.remove-verse', function() {
            var id = $(this).closest('.linked-verse-badge').data('id');
            delete linkedVerses[id];
            $(this).closest('.linked-verse-badge').remove();
            updateLinkedVerseIds();
        });

        function updateLinkedVerseIds() {
            $('#linked-verse-ids').val(Object.keys(linkedVerses).join(','));
        }

        $('#save-note-btn').on('click', function() {
            var note = $('#note-text').val().trim();
            if (!note) { $('#note-text').addClass('is-invalid'); return; }
            $('#note-text').removeClass('is-invalid');

            var $btn = $(this).prop('disabled', true).text('Saving…');

            $.ajax({
                url:  '{{ route('topics.notes.store', $topic->id) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', note: note, verse_ids: $('#linked-verse-ids').val() },
                success: function(data) {
                    $btn.prop('disabled', false).text('Save Note');
                    $('#addNoteModal').modal('hide');
                    $('#no-notes-msg').remove();

                    var versesHtml = '';
                    if (data.verses && data.verses.length) {
                        versesHtml = '<div class="mt-1">';
                        data.verses.forEach(function(v) {
                            versesHtml += '<a class="sword-link me-1" href="' + v.url + '" style="font-size:0.85rem;">'
                                + $('<span>').text(v.reference).html() + '</a>';
                        });
                        versesHtml += '</div>';
                    }

                    var html = '<div class="note-item border-bottom pb-3 mb-3" id="note-' + data.id + '">'
                        + '<div class="d-flex justify-content-between align-items-start">'
                        + '<p class="mb-2" style="white-space:pre-wrap;">' + $('<span>').text(data.note).html() + '</p>'
                        + '<button class="btn btn-sm btn-outline-danger delete-note-btn ms-3 flex-shrink-0" data-note-id="' + data.id + '">'
                        + '<i class="mdi mdi-delete"></i></button></div>'
                        + versesHtml
                        + '<small class="text-muted">' + $('<span>').text(data.created_at).html() + '</small></div>';

                    $('#notes-list').prepend(html);
                    Swal.fire({ icon: 'success', title: 'Saved!', timer: 1200, showConfirmButton: false });
                },
                error: function() {
                    $btn.prop('disabled', false).text('Save Note');
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save note.' });
                }
            });
        });

        $(document).on('click', '.delete-note-btn', function() {
            var $item = $(this).closest('.note-item');
            var id    = $(this).data('note-id');
            Swal.fire({
                title: 'Delete this note?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel',
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url:  '{{ url('topics/notes') }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        $item.fadeOut(200, function() {
                            $(this).remove();
                            if (!$('#notes-list .note-item').length) {
                                $('#notes-list').html('<p class="text-muted mb-0" id="no-notes-msg">No notes yet.</p>');
                            }
                        });
                        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1000, showConfirmButton: false });
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not delete note.' });
                    }
                });
            });
        });

        // ── Edit topic modal ─────────────────────────────────────────
        var currentName        = @json($topic->name);
        var currentDescription = @json($topic->description ?? '');
        var currentKeywords    = @json($topic->keywords ?? '');

        $('#edit-topic-btn').on('click', function() {
            $('#modal-name').val(currentName).removeClass('is-invalid');
            $('#modal-description').val(currentDescription);
            $('#modal-keywords').val(currentKeywords);
            $('#editTopicModal').modal('show');
        });

        $('#save-topic-btn').on('click', function() {
            var name = $('#modal-name').val().trim();
            if (!name) {
                $('#modal-name').addClass('is-invalid');
                return;
            }
            $('#modal-name').removeClass('is-invalid');

            var data = {
                name:        name,
                description: $('#modal-description').val().trim(),
                keywords:    $('#modal-keywords').val().trim(),
            };

            var $btn = $(this).prop('disabled', true).text('Saving…');

            fetch('{{ route('topics.update', $topic->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: new URLSearchParams({
                    _method:     'PUT',
                    name:        data.name,
                    description: data.description,
                    keywords:    data.keywords,
                })
            }).then(function(response) {
                $btn.prop('disabled', false).text('Save Changes');

                if (response.ok || response.redirected) {
                    currentName        = data.name;
                    currentDescription = data.description;
                    currentKeywords    = data.keywords;

                    $('#display-name').text(data.name);
                    $('#display-description').html(
                        data.description
                            ? $('<span>').text(data.description).html()
                            : '<span class="text-muted">—</span>'
                    );

                    var $kw = $('#display-keywords');
                    $kw.empty();
                    if (data.keywords) {
                        data.keywords.split(',').forEach(function(kw) {
                            kw = kw.trim();
                            if (kw) {
                                $kw.append(
                                    $('<span>')
                                        .addClass('badge btn-success me-1 mb-1 keyword-filter')
                                        .css('cursor', 'pointer')
                                        .attr('data-keyword', kw)
                                        .text(kw)
                                );
                            }
                        });
                    } else {
                        $kw.html('<span class="text-muted">No keywords defined</span>');
                    }

                    $('.topic-hero-title').text(data.name);

                    $('#editTopicModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Saved!', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save changes.' });
                }
            }).catch(function() {
                $btn.prop('disabled', false).text('Save Changes');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
            });
        });
    });
</script>
@endpush
