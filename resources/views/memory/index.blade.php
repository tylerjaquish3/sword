@extends('base.layout')

@section('title', 'Memory')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Memory Goals</h3>
                <h6 class="font-weight-normal mb-2">{{ count($activeMemories) }} active, {{ count($completedMemories) }} completed</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text" data-bs-toggle="modal" data-bs-target="#createMemoryModal">
                    <i class="mdi mdi-plus me-1"></i> Create New                        
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
        <h4 class="mb-3"><i class="mdi mdi-book-open-page-variant text-primary me-2"></i>Active Goals</h4>
    </div>
</div>

@if($activeMemories->count() > 0)
<div class="row">
    @foreach($activeMemories as $memory)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">
                        {{ $memory->title ?? 'Memory Goal #' . $memory->id }}
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                            <i class="mdi mdi-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('memory.complete', $memory) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-success">
                                        <i class="mdi mdi-check me-2"></i>Mark Complete
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('memory.destroy', $memory) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this memory goal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="mdi mdi-delete me-2"></i>Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                
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
                    <strong class="d-block mb-2">Verses ({{ $memory->verses->count() }}):</strong>
                    @foreach($memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $reference => $verses)
                        <span class="badge bg-light text-dark border mb-1">
                            {{ $reference }}:{{ $verses->pluck('number')->sort()->values()->join(', ') }}
                        </span>
                    @endforeach
                </div>

                @if($memory->notes)
                <div class="mt-3">
                    <small class="text-muted">{{ Str::limit($memory->notes, 100) }}</small>
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent">
                <button class="btn btn-sm btn-outline-primary view-verses-btn" 
                        data-memory-id="{{ $memory->id }}"
                        data-verses='@json($memory->verses->map(fn($v) => ["reference" => $v->chapter->book->name . " " . $v->chapter->number . ":" . $v->number, "text" => $v->text]))'>
                    <i class="mdi mdi-eye me-1"></i>View Verses
                </button>
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
        <h4 class="mb-3"><i class="mdi mdi-check-circle text-success me-2"></i>Completed Goals</h4>
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
                                        <button class="btn btn-outline-primary view-verses-btn" 
                                                data-memory-id="{{ $memory->id }}"
                                                data-verses='@json($memory->verses->map(fn($v) => ["reference" => $v->chapter->book->name . " " . $v->chapter->number . ":" . $v->number, "text" => $v->text]))'>
                                            <i class="mdi mdi-eye"></i>
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
                            <label for="book_select" class="form-label">Book</label>
                            <select class="form-select" id="book_select">
                                <option value="">Select Book</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}" data-chapters="{{ $book->chapters->count() }}">{{ $book->name }}</option>
                                @endforeach
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

<!-- View Verses Modal -->
<div class="modal fade" id="viewVersesModal" tabindex="-1" aria-labelledby="viewVersesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewVersesModalLabel">Memory Verses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="versesContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .min-height-100 {
        min-height: 100px;
    }
    .selected-verse-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .selected-verse-badge .remove-verse {
        cursor: pointer;
        opacity: 0.7;
    }
    .selected-verse-badge .remove-verse:hover {
        opacity: 1;
    }
    #verse_select {
        height: 120px;
    }
    .verse-display {
        padding: 10px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .verse-display .reference {
        font-weight: bold;
        color: #007bff;
    }
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
        
        if (bookId && chapterNumber) {
            $.get('{{ route("memory.verses") }}', {
                book_id: bookId,
                chapter_number: chapterNumber
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

    // View verses modal
    $('.view-verses-btn').click(function() {
        const verses = $(this).data('verses');
        let html = '';
        
        verses.forEach(function(verse) {
            html += `
                <div class="verse-display">
                    <span class="reference">${verse.reference}</span>
                    <p class="mb-0 mt-2">${verse.text}</p>
                </div>
            `;
        });
        
        $('#versesContent').html(html);
        $('#viewVersesModal').modal('show');
    });

    // Reset modal on close
    $('#createMemoryModal').on('hidden.bs.modal', function() {
        selectedVerseIds = [];
        $('#selectedVerses .selected-verse-badge').remove();
        $('#noVersesText').show();
        $('#book_select').val('');
        $('#chapter_select').empty().append('<option value="">Select Chapter</option>').prop('disabled', true);
        $('#verse_select').empty().append('<option value="">Select Book & Chapter first</option>').prop('disabled', true);
        $('#addVersesBtn').prop('disabled', true);
        $('#submitMemoryBtn').prop('disabled', true);
    });
});
</script>
@endpush
