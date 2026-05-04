@extends('base.layout')

@section('title', 'Memory')

@section('content')  

<div class="d-flex align-items-center justify-content-between mb-4 mb-xl-0">
    <div>
        <h3 class="text-dark font-weight-bold mb-2">Memory Goals</h3>
        <p class="page-subtitle mb-0">{{ count($activeMemories) }} active &middot; {{ count($completedMemories) }} completed</p>
    </div>
    <div class="flex-shrink-0">
        <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#createMemoryModal" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
            <i class="mdi mdi-plus"></i> New Goal
        </button>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card" style="border-left: 3px solid var(--sword-gold); background: linear-gradient(160deg, #fff 70%, rgba(201,168,76,0.04) 100%);">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="mdi mdi-book-open-page-variant" style="color: var(--sword-gold); font-size: 1.1rem;"></i>
                    <span class="text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: var(--sword-gold);">Why Memorize Scripture</span>
                </div>
                <p class="mb-2" style="font-size: 0.8rem; color: #4b5563; line-height: 1.6;">First and foremost, before we learn how to memorize Scripture, we should know why it's important to. We should memorize Scripture because it is God's Word. The living God has revealed Himself to us in His Word. He has given us wonderful promises and commands. He tells us what He is like and the things He desires. Because the Word of God is so important, we should commit it to memory.</p>
                <p class="mb-2" style="font-size: 0.8rem; color: #4b5563; line-height: 1.6;">Second, we should memorize the Bible because the Holy Spirit will use the Scriptures we memorize to make us more like Christ. When we are tempted to sin, the Spirit will bring the commands of God to mind and help us flee from temptation. When we are discouraged, the Spirit will give us hope through the promises of God. When we're sharing our faith with someone else, the Spirit will give us specific verses to share with that person.</p>
                <p class="mb-1 mt-3 fw-semibold" style="font-size: 0.78rem; color: var(--sword-navy);">Stick With It!</p>
                <p class="mb-0" style="font-size: 0.8rem; color: #4b5563; line-height: 1.6;">More than anything else, it's essential that you stick with Scripture memorization over the long haul. There are no shortcuts. But if you stick with it, you'll be absolutely amazed at how much progress you make. Over time, you'll commit hundreds, even thousands of verses to memory, and God will use the memorized verses in amazing ways in your life. The way you think and live will be transformed by the Word of God.</p>
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
                @php
                    $cardMasteries = collect($masteryByMemory[$memory->id] ?? [])->filter(fn($m) => $m['total'] > 0);
                    $cardPct = $cardMasteries->count() > 0 ? (int) round($cardMasteries->avg('pct')) : null;
                    if ($cardPct !== null) {
                        $cardBg  = $cardPct >= 80 ? 'rgba(34,197,94,0.12)' : ($cardPct >= 50 ? 'rgba(201,168,76,0.15)' : 'rgba(239,68,68,0.10)');
                        $cardClr = $cardPct >= 80 ? '#16a34a' : ($cardPct >= 50 ? 'var(--sword-gold)' : '#dc2626');
                        $cardBdr = $cardPct >= 80 ? 'rgba(34,197,94,0.3)'  : ($cardPct >= 50 ? 'rgba(201,168,76,0.35)' : 'rgba(239,68,68,0.25)');
                    }
                @endphp
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">
                        {{ $memory->title ?? 'Memory Goal #' . $memory->id }}
                    </h5>
                    <span id="card-mastery-{{ $memory->id }}"
                          style="display:{{ $cardPct !== null ? 'inline-block' : 'none' }}; font-size:0.72rem; padding:3px 10px; border-radius:12px; font-weight:600; white-space:nowrap; flex-shrink:0; margin-left:8px;{{ $cardPct !== null ? ' background:' . $cardBg . '; color:' . $cardClr . '; border:1px solid ' . $cardBdr . ';' : '' }}">
                        {{ $cardPct !== null ? $cardPct . '% mastery' : '' }}
                    </span>
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
            @php
                $quizVerses = $memory->verses->sortBy(fn($v) => sprintf('%05d-%05d-%05d', $v->chapter->book->id, $v->chapter->number, $v->number))->map(fn($v) => [
                    'id'        => $v->id,
                    'book'      => $v->chapter->book->name,
                    'chapter'   => $v->chapter->number,
                    'verse'     => $v->number,
                    'reference' => $v->chapter->book->name . ' ' . $v->chapter->number . ':' . $v->number,
                    'text'      => $v->text,
                    'mastery'   => $masteryByMemory[$memory->id][$v->id] ?? ['correct' => 0, 'total' => 0, 'pct' => null],
                ])->values();
            @endphp
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
                    <button type="button"
                            class="btn btn-sm btn-outline-warning take-quiz-btn"
                            title="Take Quiz"
                            data-memory-id="{{ $memory->id }}"
                            data-memory-title="{{ $memory->title ?? 'Memory Goal #' . $memory->id }}"
                            data-quiz-verses='@json($quizVerses)'>
                        <i class="mdi mdi-head-cog me-1"></i>Quiz
                    </button>
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
                                    @foreach($memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $reference => $groupVerses)
                                        @php
                                            $verseRange = $groupVerses->pluck('number')->sort()->values()->join(', ');
                                            $versesJson = $groupVerses->sortBy('number')->map(fn($v) => ['number' => $v->number, 'text' => $v->text])->values()->toJson();
                                        @endphp
                                        <span class="badge bg-success-subtle text-success border mb-1 verse-badge-clickable"
                                              style="cursor: pointer;"
                                              data-reference="{{ $reference }}:{{ $verseRange }}"
                                              data-verses='{{ $versesJson }}'>
                                            {{ $reference }}:{{ $verseRange }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>{{ $memory->start_date->format('M d, Y') }}</td>
                                <td>{{ $memory->completed_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ round($memory->start_date->floatDiffInDays($memory->completed_at)) }} days
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
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">
            <form action="{{ route('memory.store') }}" method="POST">
                @csrf

                <div class="sword-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="sword-modal-icon"><i class="mdi mdi-brain"></i></div>
                        <div>
                            <h5 class="modal-title mb-0" id="createMemoryModalLabel">New Memory Goal</h5>
                            <p class="sword-modal-subtitle mb-0">Choose verses and set your memorization target</p>
                        </div>
                    </div>
                    <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="modal-body sword-modal-body">

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-text-box-outline"></i></span>
                            <span class="sword-modal-section-title">Details</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div class="mb-3">
                                <label for="title" class="sword-modal-label">Title <span class="sword-modal-optional">optional</span></label>
                                <input type="text" class="form-control sword-modal-input" id="title" name="title" placeholder="e.g., Romans 8 — Freedom in Christ">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="start_date" class="sword-modal-label">Start Date</label>
                                    <input type="date" class="form-control sword-modal-input" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-6">
                                    <label for="end_date" class="sword-modal-label">Target Date <span class="sword-modal-optional">optional</span></label>
                                    <input type="date" class="form-control sword-modal-input" id="end_date" name="end_date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                            <span class="sword-modal-section-title">Add Verses</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div class="mb-3">
                                <label for="translation_select" class="sword-modal-label">Translation</label>
                                <select class="form-select sword-modal-select" id="translation_select">
                                    <option value="">Select Translation</option>
                                    @foreach($translations as $translation)
                                        <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-4">
                                    <label for="book_select" class="sword-modal-label">Book</label>
                                    <select class="form-select sword-modal-select select2-books" id="book_select">
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
                                <div class="col-6 col-sm-4">
                                    <label for="chapter_select" class="sword-modal-label">Chapter</label>
                                    <select class="form-select sword-modal-select" id="chapter_select" disabled>
                                        <option value="">—</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <label for="verse_select" class="sword-modal-label">Verse(s)</label>
                                    <select class="form-select sword-modal-select sword-modal-verse-multiselect" id="verse_select" multiple disabled>
                                        <option value="">—</option>
                                    </select>
                                </div>
                            </div>
                            <div id="verse-preview-row" style="display:none;" class="mb-3">
                                <div id="verse-preview" class="sword-modal-preview"></div>
                            </div>
                            <button type="button" class="btn sword-modal-btn-add" id="addVersesBtn" disabled>
                                <i class="mdi mdi-plus me-1"></i>Add Selected Verses
                            </button>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-format-list-checks"></i></span>
                            <span class="sword-modal-section-title">Selected Verses</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div id="selectedVerses" class="sword-modal-selected-verses">
                                <p class="sword-modal-empty-hint mb-0" id="noVersesText">No verses selected yet</p>
                            </div>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-2">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-note-outline"></i></span>
                            <span class="sword-modal-section-title">Notes <span class="sword-modal-optional">optional</span></span>
                        </div>
                        <div class="sword-modal-section-body p-0">
                            <textarea class="form-control sword-modal-textarea" id="notes" name="notes" rows="2" placeholder="Any notes about this memory goal…"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer sword-modal-footer">
                    <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn sword-modal-btn-save" id="submitMemoryBtn" disabled>
                        <i class="mdi mdi-brain me-1"></i>Create Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Memory Modal -->
<div class="modal fade" id="editMemoryModal" tabindex="-1" aria-labelledby="editMemoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">
            <form id="editMemoryForm" method="POST">
                @csrf
                @method('PUT')

                <div class="sword-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="sword-modal-icon"><i class="mdi mdi-brain"></i></div>
                        <div>
                            <h5 class="modal-title mb-0" id="editMemoryModalLabel">Edit Memory Goal</h5>
                            <p class="sword-modal-subtitle mb-0">Update verses, dates, or notes</p>
                        </div>
                    </div>
                    <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="modal-body sword-modal-body">

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-text-box-outline"></i></span>
                            <span class="sword-modal-section-title">Details</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div class="mb-3">
                                <label for="edit_title" class="sword-modal-label">Title <span class="sword-modal-optional">optional</span></label>
                                <input type="text" class="form-control sword-modal-input" id="edit_title" name="title" placeholder="e.g., Romans 8 — Freedom in Christ">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="edit_start_date" class="sword-modal-label">Start Date</label>
                                    <input type="date" class="form-control sword-modal-input" id="edit_start_date" name="start_date" required>
                                </div>
                                <div class="col-6">
                                    <label for="edit_end_date" class="sword-modal-label">Target Date <span class="sword-modal-optional">optional</span></label>
                                    <input type="date" class="form-control sword-modal-input" id="edit_end_date" name="end_date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                            <span class="sword-modal-section-title">Add Verses</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div class="mb-3">
                                <label for="edit_translation_select" class="sword-modal-label">Translation</label>
                                <select class="form-select sword-modal-select" id="edit_translation_select">
                                    <option value="">Select Translation</option>
                                    @foreach($translations as $translation)
                                        <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-4">
                                    <label for="edit_book_select" class="sword-modal-label">Book</label>
                                    <select class="form-select sword-modal-select" id="edit_book_select">
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
                                <div class="col-6 col-sm-4">
                                    <label for="edit_chapter_select" class="sword-modal-label">Chapter</label>
                                    <select class="form-select sword-modal-select" id="edit_chapter_select" disabled>
                                        <option value="">—</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <label for="edit_verse_select" class="sword-modal-label">Verse(s)</label>
                                    <select class="form-select sword-modal-select sword-modal-verse-multiselect" id="edit_verse_select" multiple disabled>
                                        <option value="">—</option>
                                    </select>
                                </div>
                            </div>
                            <div id="edit-verse-preview-row" style="display:none;" class="mb-3">
                                <div id="edit-verse-preview" class="sword-modal-preview"></div>
                            </div>
                            <button type="button" class="btn sword-modal-btn-add" id="editAddVersesBtn" disabled>
                                <i class="mdi mdi-plus me-1"></i>Add Selected Verses
                            </button>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-4">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-format-list-checks"></i></span>
                            <span class="sword-modal-section-title">Selected Verses</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div id="editSelectedVerses" class="sword-modal-selected-verses">
                                <p class="sword-modal-empty-hint mb-0" id="editNoVersesText">No verses selected yet</p>
                            </div>
                        </div>
                    </div>

                    <div class="sword-modal-section mb-2">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-note-outline"></i></span>
                            <span class="sword-modal-section-title">Notes <span class="sword-modal-optional">optional</span></span>
                        </div>
                        <div class="sword-modal-section-body p-0">
                            <textarea class="form-control sword-modal-textarea" id="edit_notes" name="notes" rows="2" placeholder="Any notes about this memory goal…"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer sword-modal-footer">
                    <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn sword-modal-btn-save" id="editSubmitMemoryBtn">
                        <i class="mdi mdi-content-save-outline me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Verse Text Modal -->
<div class="modal fade" id="verseTextModal" tabindex="-1" aria-labelledby="verseTextModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sword-modal">
            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-book-open-page-variant"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="verseTextModalLabel"></h5>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
            <div class="modal-body sword-modal-body">
                <p id="verseTextModalBody" style="font-size: 0.9rem; line-height: 1.7; color: #4b5563; font-style: italic;"></p>
            </div>
        </div>
    </div>
</div>

<!-- Quiz Modal -->
<div class="modal fade" id="quizModal" tabindex="-1" aria-labelledby="quizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-brain"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="quizModalLabel">Scripture Quiz</h5>
                        <p class="sword-modal-subtitle mb-0" id="quizModalSubtitle">Loading…</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body p-0">

                {{-- Phase: question --}}
                <div id="quiz-phase-question" class="quiz-phase p-4">
                    <div class="sword-modal-section mb-3">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                            <span class="sword-modal-section-title" id="quiz-progress-label">Verse 1 of N</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <p class="mb-0 fw-bold" style="font-size:1.15rem; color:var(--sword-navy);" id="quiz-reference"></p>
                        </div>
                    </div>
                    <div class="sword-modal-section">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-pencil-outline"></i></span>
                            <span class="sword-modal-section-title">Type the verse from memory</span>
                        </div>
                        <div class="sword-modal-section-body p-0">
                            <textarea id="quiz-answer-input"
                                      class="form-control sword-modal-textarea"
                                      rows="4"
                                      placeholder="Type the verse text here…"
                                      style="border-radius:0 0 12px 12px; border:none; resize:none;"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3 gap-2">
                        <button type="button" class="btn sword-modal-btn-cancel" id="quiz-skip-btn">
                            <i class="mdi mdi-skip-next me-1"></i>Skip
                        </button>
                        <button type="button" class="btn sword-modal-btn-save" id="quiz-reveal-btn">
                            <i class="mdi mdi-eye-outline me-1"></i>Reveal Answer
                        </button>
                    </div>
                </div>

                {{-- Phase: answer --}}
                <div id="quiz-phase-answer" class="quiz-phase p-4" style="display:none;">
                    <div class="text-center mb-4">
                        <div id="quiz-score-badge" style="display:inline-flex; align-items:center; justify-content:center; width:84px; height:84px; border-radius:50%; font-size:1.5rem; font-weight:700; border:3px solid;"></div>
                        <p id="quiz-score-verdict" class="mb-0 mt-2 fw-semibold" style="font-size:0.95rem;"></p>
                    </div>
                    <div class="sword-modal-section mb-3">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                            <span class="sword-modal-section-title" id="quiz-answer-reference-label"></span>
                        </div>
                        <div class="sword-modal-section-body">
                            <p class="mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.06em; color:#9ca3af;">Your answer</p>
                            <p id="quiz-user-answer-display"
                               style="font-size:0.9rem; line-height:1.6; color:#6b7280; font-style:italic; background:#f9f8f5; padding:10px 12px; border-radius:8px; border:1px solid #ede8df; margin-bottom:12px;"></p>
                            <p class="mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.06em; color:#9ca3af;">Correct verse</p>
                            <p id="quiz-correct-answer-display"
                               style="font-size:0.9rem; line-height:1.6; color:#1a2545; font-style:italic; margin-bottom:0;"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn sword-modal-btn-save" id="quiz-next-btn">
                            Next Verse <i class="mdi mdi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- Phase: summary --}}
                <div id="quiz-phase-summary" class="quiz-phase p-4" style="display:none;">
                    <div class="sword-modal-section">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-trophy-outline"></i></span>
                            <span class="sword-modal-section-title">Session Complete</span>
                        </div>
                        <div class="sword-modal-section-body">
                            <div class="text-center mb-4">
                                <p class="mb-1" style="font-size:2.5rem; font-weight:700; color:var(--sword-navy);" id="quiz-summary-score"></p>
                                <p class="text-muted mb-0" style="font-size:0.85rem;">verses correct this session</p>
                            </div>
                            <div id="quiz-summary-verse-list"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Done</button>
                        <button type="button" class="btn sword-modal-btn-save" id="quiz-restart-btn">
                            <i class="mdi mdi-restart me-1"></i>Quiz Again
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection


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
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editMemoryModal')).show();
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

    // Verse text modal
    $(document).on('click', '.verse-badge-clickable', function() {
        const reference = $(this).data('reference');
        const verses = $(this).data('verses');
        $('#verseTextModalLabel').text(reference);
        let html = '';
        verses.forEach(function(v) {
            html += `<sup class="fw-bold me-1" style="font-style: normal;">${v.number}</sup>${v.text} `;
        });
        $('#verseTextModalBody').html(html);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('verseTextModal')).show();
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

    // =========================================================
    // Scripture Quiz State Machine
    // =========================================================

    let quiz = {
        memoryId:       null,
        verses:         [],
        index:          0,
        sessionResults: [],
        attemptUrl:     '{{ route("quiz.attempt") }}',
    };

    function quizCalcSimilarity(typed, actual) {
        const normalize = s => s.toLowerCase().replace(/[^a-z0-9\s]/g, '').split(/\s+/).filter(w => w.length > 0);
        const typedWords  = normalize(typed);
        const actualWords = normalize(actual);
        if (actualWords.length === 0 || typedWords.length === 0) return 0;
        // Bag-of-words match: count how many words from the actual verse appear in the typed answer (respecting frequency)
        const freq = {};
        typedWords.forEach(w => { freq[w] = (freq[w] || 0) + 1; });
        let matched = 0;
        actualWords.forEach(function(w) {
            if (freq[w] && freq[w] > 0) { matched++; freq[w]--; }
        });
        return Math.round((matched / actualWords.length) * 100);
    }

    function quizScoreStyle(pct) {
        if (pct >= 80) return { bg: 'rgba(34,197,94,0.1)', color: '#16a34a', border: 'rgba(34,197,94,0.4)', verdict: 'Correct!' };
        if (pct >= 50) return { bg: 'rgba(201,168,76,0.1)', color: 'var(--sword-gold)', border: 'rgba(201,168,76,0.5)', verdict: 'Almost There' };
        return { bg: 'rgba(239,68,68,0.08)', color: '#dc2626', border: 'rgba(239,68,68,0.3)', verdict: 'Keep Practicing' };
    }

    function quizShowPhase(phase) {
        $('#quiz-phase-question, #quiz-phase-answer, #quiz-phase-summary').hide();
        $('#quiz-phase-' + phase).show();
    }

    function quizRenderCurrentVerse() {
        const v = quiz.verses[quiz.index];
        const n = quiz.verses.length;
        $('#quiz-progress-label').text('Verse ' + (quiz.index + 1) + ' of ' + n);
        $('#quiz-reference').text(v.reference);
        $('#quiz-answer-input').val('').trigger('focus');
        $('#quizModalSubtitle').text((quiz.index + 1) + ' / ' + n + ' — ' + v.book);
        quizShowPhase('question');
    }

    function quizUpdateCardBadge() {
        const versesWithData = quiz.verses.filter(v => v.mastery && v.mastery.total > 0);
        if (!versesWithData.length) return;
        const avgPct = Math.round(versesWithData.reduce((sum, v) => sum + v.mastery.pct, 0) / versesWithData.length);
        const style  = quizScoreStyle(avgPct);
        const bg     = avgPct >= 80 ? 'rgba(34,197,94,0.12)' : (avgPct >= 50 ? 'rgba(201,168,76,0.15)' : 'rgba(239,68,68,0.10)');
        const bdr    = avgPct >= 80 ? 'rgba(34,197,94,0.3)'  : (avgPct >= 50 ? 'rgba(201,168,76,0.35)' : 'rgba(239,68,68,0.25)');
        $('#card-mastery-' + quiz.memoryId)
            .css({ background: bg, color: style.color, border: '1px solid ' + bdr, display: 'inline-block' })
            .text(avgPct + '% mastery');
    }

    function quizRecordAttempt(verseObj, correct) {
        const deferred = $.Deferred();
        $.ajax({
            url:  quiz.attemptUrl,
            type: 'POST',
            data: {
                _token:    '{{ csrf_token() }}',
                memory_id: quiz.memoryId,
                verse_id:  verseObj.id,
                correct:   correct ? 1 : 0,
            },
            success: function(resp) {
                verseObj.mastery = { correct: resp.correct, total: resp.total, pct: resp.pct };
                quizUpdateCardBadge();
                deferred.resolve(resp);
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save attempt. Please try again.' });
                deferred.reject();
            }
        });
        return deferred.promise();
    }

    function quizShowAnswer(typed, verseObj) {
        const pct     = quizCalcSimilarity(typed, verseObj.text);
        const correct = pct >= 80;
        const style   = quizScoreStyle(pct);

        // Score badge
        $('#quiz-score-badge').css({
            background: style.bg,
            color:      style.color,
            border:     '3px solid ' + style.border,
        }).text(pct + '%');
        $('#quiz-score-verdict').css('color', style.color).text(style.verdict);

        // Answers
        $('#quiz-answer-reference-label').text(verseObj.reference);
        $('#quiz-user-answer-display').text(typed || '(nothing typed)');
        $('#quiz-correct-answer-display').text(verseObj.text);

        // Record attempt — fire and forget; pill updates when response arrives
        quiz.sessionResults.push({ verse: verseObj, correct: correct, score: pct });
        quizRecordAttempt(verseObj, correct);

        quizShowPhase('answer');
    }

    function quizAdvance() {
        quiz.index++;
        if (quiz.index < quiz.verses.length) {
            quizRenderCurrentVerse();
        } else {
            quizShowSummary();
        }
    }

    function quizShowSummary() {
        const correctCount = quiz.sessionResults.filter(r => r.correct).length;
        const total        = quiz.sessionResults.length;
        $('#quiz-summary-score').text(correctCount + ' / ' + total);
        let html = '';
        quiz.sessionResults.forEach(function(r) {
            const style    = quizScoreStyle(r.score);
            const allTime  = (r.verse.mastery && r.verse.mastery.pct !== null)
                ? ' <small class="text-muted">(' + r.verse.mastery.pct + '% all-time)</small>'
                : '';
            html += '<div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="font-size:0.85rem;">' +
                    '<span>' + r.verse.reference + allTime + '</span>' +
                    '<span class="badge ms-2" style="background:' + style.bg + '; color:' + style.color + '; border:1px solid ' + style.border + '; font-weight:600;">' + r.score + '%</span>' +
                    '</div>';
        });
        $('#quiz-summary-verse-list').html(html || '<p class="text-muted">No verses attempted.</p>');
        quizShowPhase('summary');
        $('#quizModalSubtitle').text('Session complete — ' + correctCount + '/' + total + ' correct');
    }

    $(document).on('click', '.take-quiz-btn', function() {
        const btn = $(this);
        quiz.memoryId       = btn.data('memory-id');
        quiz.verses         = btn.data('quiz-verses');
        quiz.index          = 0;
        quiz.sessionResults = [];
        quiz.verses.sort(() => Math.random() - 0.5);
        $('#quizModalLabel').text(btn.data('memory-title'));
        quizRenderCurrentVerse();
        // Hide verse text on all active memory cards so nothing is visible through the overlay
        $('.verses-list').addClass('quiz-verses-hidden').css('visibility', 'hidden');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('quizModal')).show();
    });

    $('#quiz-reveal-btn').on('click', function() {
        const v     = quiz.verses[quiz.index];
        const typed = $('#quiz-answer-input').val().trim();
        quizShowAnswer(typed, v);
    });

    $('#quiz-skip-btn').on('click', function() {
        const v = quiz.verses[quiz.index];
        quizShowAnswer('', v);
    });

    $('#quiz-next-btn').on('click', quizAdvance);

    $('#quiz-restart-btn').on('click', function() {
        quiz.index          = 0;
        quiz.sessionResults = [];
        quiz.verses.sort(() => Math.random() - 0.5);
        quizRenderCurrentVerse();
    });

    $('#quizModal').on('hidden.bs.modal', function() {
        quiz.memoryId       = null;
        quiz.verses         = [];
        quiz.index          = 0;
        quiz.sessionResults = [];
        quizShowPhase('question');
        $('#quiz-answer-input').val('');
        // Restore verse text on cards
        $('.verses-list').removeClass('quiz-verses-hidden').css('visibility', '');
    });
});
</script>
@endpush
