@extends('base.layout')

@section('title', 'Memory')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Memory Goals</h3>
                <p class="page-subtitle mb-0">{{ count($activeMemories) }} active &middot; {{ count($completedMemories) }} completed</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text" data-bs-toggle="modal" data-bs-target="#createMemoryModal">
                    Create New                        
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Active Memory Goals Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="sword-section-header">
            <span class="section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
            <span class="section-title">Active Goals</span>
        </div>
    </div>
</div>

@php
$formatVerseRange = function($numbers) {
    $numbers = collect($numbers)->map(fn($n) => (int)$n)->sort()->values()->all();
    if (empty($numbers)) return '';
    $ranges = [];
    $start = $end = $numbers[0];
    for ($i = 1; $i < count($numbers); $i++) {
        if ($numbers[$i] === $end + 1) {
            $end = $numbers[$i];
        } else {
            $ranges[] = $start === $end ? "$start" : "$start-$end";
            $start = $end = $numbers[$i];
        }
    }
    $ranges[] = $start === $end ? "$start" : "$start-$end";
    return implode(', ', $ranges);
};
@endphp

@if($activeMemories->count() > 0)
<div class="row">
    @foreach($activeMemories as $memory)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100" style="border-top: 3px solid var(--sword-gold); background: linear-gradient(160deg, #fff 70%, rgba(201,168,76,0.05) 100%);">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    {{ $memory->title ?? 'Memory Goal #' . $memory->id }}
                </h5>
                
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="mdi mdi-calendar me-1"></i>
                        Started: {{ $memory->start_date->format('M d, Y') }}
                        @if($memory->end_date)
                            <br><i class="mdi mdi-calendar-check me-1"></i>
                            Target: {{ $memory->end_date->format('M d, Y') }}
                        @endif
                    </small>
                </div>

                <div class="verses-list">
                    @foreach($memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $reference => $groupedVerses)
                        <div class="mb-3">
                            <p class="mb-1 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: var(--sword-gold);">
                                {{ $reference }}:{{ $formatVerseRange($groupedVerses->pluck('number')->all()) }}
                            </p>
                            <p class="mb-0" style="font-size: 0.82rem; line-height: 1.5; color: #4b5563; font-style: italic;">
                                @foreach($groupedVerses->sortBy('number') as $verse)
                                    <sup class="fw-bold me-1" style="font-style: normal;">{{ $verse->number }}</sup>{{ $verse->text }}
                                @endforeach
                            </p>
                        </div>
                    @endforeach
                </div>

                @if($memory->notes)
                <div class="mt-2">
                    <small class="text-muted">{{ Str::limit($memory->notes, 100) }}</small>
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary edit-memory-btn"
                            title="Edit"
                            data-memory-id="{{ $memory->id }}"
                            data-memory-title="{{ $memory->title }}"
                            data-memory-start-date="{{ $memory->start_date->format('Y-m-d') }}"
                            data-memory-end-date="{{ $memory->end_date ? $memory->end_date->format('Y-m-d') : '' }}"
                            data-memory-notes="{{ $memory->notes }}"
                            data-memory-verses='@json($memory->verses->map(fn($v) => ["id" => $v->id, "reference" => $v->chapter->book->name . " " . $v->chapter->number . ":" . $v->number]))'>
                        <i class="mdi mdi-pencil"></i>
                    </button>
                    <form action="{{ route('memory.destroy', $memory) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this memory goal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </form>
                </div>
                <form action="{{ route('memory.complete', $memory) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">
                        <i class="mdi mdi-check me-1"></i>Mark Complete
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-book-open-page-variant text-muted" style="font-size: 48px;"></i>
                <p class="text-muted mt-3 mb-0">No active memory goals. Create one to get started!</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Completed Memory Goals Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="sword-section-header">
            <span class="section-icon"><i class="mdi mdi-check-circle"></i></span>
            <span class="section-title">Completed Goals</span>
        </div>
    </div>
</div>

@if($completedMemories->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="completedTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Verses</th>
                                <th>Started</th>
                                <th>Completed</th>
                                <th>Days</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedMemories as $memory)
                            <tr>
                                <td>{{ $memory->title ?? 'Memory Goal #' . $memory->id }}</td>
                                <td>
                                    @foreach($memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $reference => $verses)
                                        <span class="badge bg-success-subtle text-success border mb-1">
                                            {{ $reference }}:{{ $verses->pluck('number')->sort()->values()->join(', ') }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>{{ $memory->start_date->format('M d, Y') }}</td>
                                <td>{{ $memory->completed_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $memory->start_date->diffInDays($memory->completed_at) }} days
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary edit-memory-btn" title="Edit"
                                                data-memory-id="{{ $memory->id }}"
                                                data-memory-title="{{ $memory->title }}"
                                                data-memory-start-date="{{ $memory->start_date->format('Y-m-d') }}"
                                                data-memory-end-date="{{ $memory->end_date ? $memory->end_date->format('Y-m-d') : '' }}"
                                                data-memory-notes="{{ $memory->notes }}"
                                                data-memory-verses='@json($memory->verses->map(fn($v) => ["id" => $v->id, "reference" => $v->chapter->book->name . " " . $v->chapter->number . ":" . $v->number]))'>
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <form action="{{ route('memory.uncomplete', $memory) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="Reopen">
                                                <i class="mdi mdi-refresh"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('memory.destroy', $memory) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                    </div>
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
@else
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-4">
                <p class="text-muted mb-0">No completed memory goals yet.</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Create Memory Modal -->
<div class="modal fade" id="createMemoryModal" tabindex="-1" aria-labelledby="createMemoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('memory.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createMemoryModalLabel">Create Memory Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Title (optional)</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Romans 8 - Freedom in Christ">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Target End Date (optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="translation_select" class="form-label">Translation</label>
                            <select class="form-select" id="translation_select">
                                <option value="">Select Translation</option>
                                @foreach($translations as $translation)
                                    <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="book_select" class="form-label">Book</label>
                            <select class="form-select select2-books" id="book_select">
                                <option value="">Select Book</option>
                                <optgroup label="Old Testament">
                                    @foreach($books->where('new_testament', 0) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ $book->chapters->count() }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="New Testament">
                                    @foreach($books->where('new_testament', 1) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ $book->chapters->count() }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="chapter_select" class="form-label">Chapter</label>
                            <select class="form-select" id="chapter_select" disabled>
                                <option value="">Select Chapter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="verse_select" class="form-label">Verse(s)</label>
                            <select class="form-select" id="verse_select" multiple disabled>
                                <option value="">Select Book & Chapter first</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3" id="verse-preview-row" style="display: none;">
                        <div class="col-12">
                            <div id="verse-preview" class="border rounded p-3" style="background: #f8f9fc; font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary" id="addVersesBtn" disabled>
                                <i class="mdi mdi-plus me-1"></i>Add Selected Verses
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Selected Verses</label>
                            <div id="selectedVerses" class="border rounded p-3 min-height-100">
                                <p class="text-muted mb-0" id="noVersesText">No verses selected yet</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any notes about this memory goal..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitMemoryBtn" disabled>Create Memory Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Memory Modal -->
<div class="modal fade" id="editMemoryModal" tabindex="-1" aria-labelledby="editMemoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editMemoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemoryModalLabel">Edit Memory Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_title" class="form-label">Title (optional)</label>
                            <input type="text" class="form-control" id="edit_title" name="title" placeholder="e.g., Romans 8 - Freedom in Christ">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_end_date" class="form-label">Target End Date (optional)</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_translation_select" class="form-label">Translation</label>
                            <select class="form-select" id="edit_translation_select">
                                <option value="">Select Translation</option>
                                @foreach($translations as $translation)
                                    <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_book_select" class="form-label">Book</label>
                            <select class="form-select" id="edit_book_select">
                                <option value="">Select Book</option>
                                <optgroup label="Old Testament">
                                    @foreach($books->where('new_testament', 0) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ $book->chapters->count() }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="New Testament">
                                    @foreach($books->where('new_testament', 1) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ $book->chapters->count() }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_chapter_select" class="form-label">Chapter</label>
                            <select class="form-select" id="edit_chapter_select" disabled>
                                <option value="">Select Chapter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_verse_select" class="form-label">Verse(s)</label>
                            <select class="form-select" id="edit_verse_select" multiple disabled>
                                <option value="">Select Book & Chapter first</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3" id="edit-verse-preview-row" style="display: none;">
                        <div class="col-12">
                            <div id="edit-verse-preview" class="border rounded p-3" style="background: #f8f9fc; font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary" id="editAddVersesBtn" disabled>
                                <i class="mdi mdi-plus me-1"></i>Add Selected Verses
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Selected Verses</label>
                            <div id="editSelectedVerses" class="border rounded p-3 min-height-100">
                                <p class="text-muted mb-0" id="editNoVersesText">No verses selected yet</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_notes" class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2" placeholder="Any notes about this memory goal..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editSubmitMemoryBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('css')
<style>
    .min-height-100 { min-height: 100px; }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    let selectedVerseIds = [];
    let versesData = {};

    // Initialize DataTable for completed memories
    if ($('#completedTable').length && $('#completedTable tbody tr').length > 0) {
        $('#completedTable').DataTable({
            order: [[3, 'desc']],
            pageLength: 10
        });
    }

    // Translation selection change — reload verses for current chapter if one is selected
    $('#translation_select').change(function() {
        $('#verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#addVersesBtn').prop('disabled', true);
        $('#verse-preview-row').hide();
        // Re-trigger chapter change to reload verses in the new translation
        if ($('#chapter_select').val()) {
            $('#chapter_select').trigger('change');
        }
    });

    // Book selection change
    $('#book_select').change(function() {
        const bookId = $(this).val();
        const chapters = $(this).find(':selected').data('chapters') || 0;
        
        $('#chapter_select').empty().append('<option value="">Select Chapter</option>');
        
        if (bookId) {
            for (let i = 1; i <= chapters; i++) {
                $('#chapter_select').append(`<option value="${i}">${i}</option>`);
            }
            $('#chapter_select').prop('disabled', false);
        } else {
            $('#chapter_select').prop('disabled', true);
        }
        
        $('#verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#addVersesBtn').prop('disabled', true);
    });

    // Chapter selection change
    $('#chapter_select').change(function() {
        const bookId = $('#book_select').val();
        const chapterNumber = $(this).val();
        
        $('#verse-preview-row').hide();
        if (bookId && chapterNumber) {
            $.get('{{ route("memory.verses") }}', {
                book_id: bookId,
                chapter_number: chapterNumber,
                translation_id: $('#translation_select').val(),
            }, function(verses) {
                $('#verse_select').empty();
                versesData = {};
                
                verses.forEach(function(verse) {
                    versesData[verse.id] = verse;
                    $('#verse_select').append(`<option value="${verse.id}">${verse.number}</option>`);
                });
                
                $('#verse_select').prop('disabled', false);
                $('#addVersesBtn').prop('disabled', false);
            });
        }
    });

    // Preview selected verse text
    $('#verse_select').on('change', function() {
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            $('#verse-preview-row').hide();
            return;
        }
        const bookName = $('#book_select option:selected').text();
        const chapterNum = $('#chapter_select').val();
        let html = '';
        selected.forEach(function(id) {
            const verse = versesData[id];
            if (verse) {
                html += `<div class="mb-2"><sup class="text-muted fw-bold me-1">${bookName} ${chapterNum}:${verse.number}</sup>${verse.text}</div>`;
            }
        });
        $('#verse-preview').html(html);
        $('#verse-preview-row').show();
    });

    // Add selected verses
    $('#addVersesBtn').click(function() {
        const bookName = $('#book_select option:selected').text();
        const chapterNum = $('#chapter_select').val();
        const selectedOptions = $('#verse_select option:selected');
        
        selectedOptions.each(function() {
            const verseId = $(this).val();
            const verseNum = $(this).text();
            
            if (!selectedVerseIds.includes(verseId)) {
                selectedVerseIds.push(verseId);
                
                const badge = `
                    <span class="badge bg-primary me-2 mb-2 selected-verse-badge" data-verse-id="${verseId}">
                        ${bookName} ${chapterNum}:${verseNum}
                        <i class="mdi mdi-close remove-verse"></i>
                        <input type="hidden" name="verse_ids[]" value="${verseId}">
                    </span>
                `;
                
                $('#selectedVerses').append(badge);
                $('#noVersesText').hide();
            }
        });
        
        updateSubmitButton();
    });

    // Remove verse from selection
    $(document).on('click', '.remove-verse', function() {
        const badge = $(this).closest('.selected-verse-badge');
        const verseId = badge.data('verse-id');
        
        selectedVerseIds = selectedVerseIds.filter(id => id != verseId);
        badge.remove();
        
        if (selectedVerseIds.length === 0) {
            $('#noVersesText').show();
        }
        
        updateSubmitButton();
    });

    function updateSubmitButton() {
        $('#submitMemoryBtn').prop('disabled', selectedVerseIds.length === 0);
    }

    // Edit memory modal
    let editSelectedVerseIds = [];
    let editVersesData = {};

    $(document).on('click', '.edit-memory-btn', function() {
        const btn = $(this);
        const memoryId = btn.data('memory-id');
        const verses = btn.data('memory-verses');

        // Set form action
        $('#editMemoryForm').attr('action', '/memory/' + memoryId);

        // Populate text fields
        $('#edit_title').val(btn.data('memory-title') || '');
        $('#edit_start_date').val(btn.data('memory-start-date') || '');
        $('#edit_end_date').val(btn.data('memory-end-date') || '');
        $('#edit_notes').val(btn.data('memory-notes') || '');

        // Reset verse selection state
        editSelectedVerseIds = [];
        $('#editSelectedVerses .edit-selected-verse-badge').remove();
        $('#editNoVersesText').show();

        // Pre-populate existing verses
        verses.forEach(function(verse) {
            const id = String(verse.id);
            if (!editSelectedVerseIds.includes(id)) {
                editSelectedVerseIds.push(id);
                const badge = `
                    <span class="badge bg-primary me-2 mb-2 edit-selected-verse-badge" data-verse-id="${id}">
                        ${verse.reference}
                        <i class="mdi mdi-close edit-remove-verse" style="cursor:pointer;"></i>
                        <input type="hidden" name="verse_ids[]" value="${id}">
                    </span>
                `;
                $('#editSelectedVerses').append(badge);
                $('#editNoVersesText').hide();
            }
        });

        updateEditSubmitButton();
        $('#editMemoryModal').modal('show');
    });

    // Edit translation change
    $('#edit_translation_select').change(function() {
        $('#edit_verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#editAddVersesBtn').prop('disabled', true);
        $('#edit-verse-preview-row').hide();
        if ($('#edit_chapter_select').val()) {
            $('#edit_chapter_select').trigger('change');
        }
    });

    // Edit book change
    $('#edit_book_select').change(function() {
        const bookId = $(this).val();
        const chapters = $(this).find(':selected').data('chapters') || 0;

        $('#edit_chapter_select').empty().append('<option value="">Select Chapter</option>');

        if (bookId) {
            for (let i = 1; i <= chapters; i++) {
                $('#edit_chapter_select').append(`<option value="${i}">${i}</option>`);
            }
            $('#edit_chapter_select').prop('disabled', false);
        } else {
            $('#edit_chapter_select').prop('disabled', true);
        }

        $('#edit_verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#editAddVersesBtn').prop('disabled', true);
    });

    // Edit chapter change
    $('#edit_chapter_select').change(function() {
        const bookId = $('#edit_book_select').val();
        const chapterNumber = $(this).val();

        $('#edit-verse-preview-row').hide();
        if (bookId && chapterNumber) {
            $.get('{{ route("memory.verses") }}', {
                book_id: bookId,
                chapter_number: chapterNumber,
                translation_id: $('#edit_translation_select').val(),
            }, function(verses) {
                $('#edit_verse_select').empty();
                editVersesData = {};

                verses.forEach(function(verse) {
                    editVersesData[verse.id] = verse;
                    $('#edit_verse_select').append(`<option value="${verse.id}">${verse.number}</option>`);
                });

                $('#edit_verse_select').prop('disabled', false);
                $('#editAddVersesBtn').prop('disabled', false);
            });
        }
    });

    // Edit verse preview
    $('#edit_verse_select').on('change', function() {
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            $('#edit-verse-preview-row').hide();
            return;
        }
        const bookName = $('#edit_book_select option:selected').text();
        const chapterNum = $('#edit_chapter_select').val();
        let html = '';
        selected.forEach(function(id) {
            const verse = editVersesData[id];
            if (verse) {
                html += `<div class="mb-2"><sup class="text-muted fw-bold me-1">${bookName} ${chapterNum}:${verse.number}</sup>${verse.text}</div>`;
            }
        });
        $('#edit-verse-preview').html(html);
        $('#edit-verse-preview-row').show();
    });

    // Edit add verses
    $('#editAddVersesBtn').click(function() {
        const bookName = $('#edit_book_select option:selected').text();
        const chapterNum = $('#edit_chapter_select').val();
        const selectedOptions = $('#edit_verse_select option:selected');

        selectedOptions.each(function() {
            const verseId = String($(this).val());
            const verseNum = $(this).text();

            if (!editSelectedVerseIds.includes(verseId)) {
                editSelectedVerseIds.push(verseId);

                const badge = `
                    <span class="badge bg-primary me-2 mb-2 edit-selected-verse-badge" data-verse-id="${verseId}">
                        ${bookName} ${chapterNum}:${verseNum}
                        <i class="mdi mdi-close edit-remove-verse" style="cursor:pointer;"></i>
                        <input type="hidden" name="verse_ids[]" value="${verseId}">
                    </span>
                `;
                $('#editSelectedVerses').append(badge);
                $('#editNoVersesText').hide();
            }
        });

        updateEditSubmitButton();
    });

    // Edit remove verse
    $(document).on('click', '.edit-remove-verse', function() {
        const badge = $(this).closest('.edit-selected-verse-badge');
        const verseId = String(badge.data('verse-id'));

        editSelectedVerseIds = editSelectedVerseIds.filter(id => id !== verseId);
        badge.remove();

        if (editSelectedVerseIds.length === 0) {
            $('#editNoVersesText').show();
        }

        updateEditSubmitButton();
    });

    function updateEditSubmitButton() {
        $('#editSubmitMemoryBtn').prop('disabled', editSelectedVerseIds.length === 0);
    }

    // Reset edit modal on close
    $('#editMemoryModal').on('hidden.bs.modal', function() {
        editSelectedVerseIds = [];
        editVersesData = {};
        $('#editSelectedVerses .edit-selected-verse-badge').remove();
        $('#editNoVersesText').show();
        $('#edit_book_select').val('');
        $('#edit_chapter_select').empty().append('<option value="">Select Chapter</option>').prop('disabled', true);
        $('#edit_verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#editAddVersesBtn').prop('disabled', true);
        $('#edit-verse-preview-row').hide();
        $('#edit-verse-preview').html('');
    });

    // Initialize Select2 inside the modal with proper dropdownParent
    $('#createMemoryModal').on('shown.bs.modal', function() {
        if ($('#book_select').hasClass('select2-hidden-accessible')) {
            $('#book_select').select2('destroy');
        }
        $('#book_select').select2({
            dropdownParent: $('#createMemoryModal'),
            placeholder: 'Select Book',
            allowClear: true,
            width: '100%',
        });
    });

    // Reset modal on close
    $('#createMemoryModal').on('hidden.bs.modal', function() {
        selectedVerseIds = [];
        $('#selectedVerses .selected-verse-badge').remove();
        $('#noVersesText').show();
        $('#translation_select').val('');
        if ($('#book_select').hasClass('select2-hidden-accessible')) {
            $('#book_select').val(null).trigger('change');
        } else {
            $('#book_select').val('');
        }
        $('#chapter_select').empty().append('<option value="">Select Chapter</option>').prop('disabled', true);
        $('#verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#addVersesBtn').prop('disabled', true);
        $('#submitMemoryBtn').prop('disabled', true);
        $('#verse-preview-row').hide();
        $('#verse-preview').html('');
    });
});
</script>
@endpush
