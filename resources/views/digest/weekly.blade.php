@extends('base.layout')

@section('title', 'Weekly Digest')

@push('css')
<style>
.digest-section-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--sword-gold);
    font-weight: 700;
    margin-bottom: 0.75rem;
}
.digest-card {
    border-top: 2px solid var(--sword-gold);
}
.digest-item {
    padding: 0.6rem 0;
    border-bottom: 1px solid rgba(14,22,40,0.06);
}
.digest-item:last-child {
    border-bottom: none;
}
.digest-ref {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--sword-navy);
    letter-spacing: 0.03em;
}
.digest-snippet {
    font-size: 0.82rem;
    color: #4b5563;
    line-height: 1.5;
}
.digest-empty {
    text-align: center;
    padding: 1.5rem 0;
    color: #9ca3af;
    font-size: 0.85rem;
}
.digest-stat-val {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--sword-navy);
    line-height: 1;
}
.digest-stat-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-top: 0.25rem;
}
.past-note-card {
    background: linear-gradient(135deg, rgba(14,22,40,0.03) 0%, rgba(201,168,76,0.06) 100%);
    border: 1px solid rgba(201,168,76,0.25);
    border-left: 4px solid var(--sword-gold);
}
@media print {
    .container-scroller > div:first-child,
    .d-print-none {
        display: none !important;
    }
    .container-fluid.page-body-wrapper {
        padding: 0 !important;
    }
    .main-panel {
        width: 100% !important;
        margin: 0 !important;
    }
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="digest-section-label mb-1">Weekly Spiritual Digest</p>
        <h3 class="mb-1 fw-bold" style="color: var(--sword-navy);">Your Week in Review</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #6b7280;">
            {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center d-print-none">
        <a href="{{ route('digest.history') }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
            <i class="mdi mdi-history"></i> All Digests
        </a>
        <a href="{{ route('digest.complete.create') }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
            <i class="mdi mdi-check-circle-outline"></i> {{ $savedThisWeek ? 'Edit Digest' : 'Complete Digest' }}
        </a>
    </div>
</div>

{{-- Stats Row --}}
<div class="row mb-4 g-3">
    <div class="col-6 col-sm-3">
        <div class="card text-center py-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="digest-stat-val">{{ $daysStudied }}</div>
            <div class="digest-stat-label">Days Studied</div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card text-center py-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="digest-stat-val">{{ $chaptersRead->sum(fn($r) => $r->count()) }}</div>
            <div class="digest-stat-label">Chapters Read</div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card text-center py-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="digest-stat-val">{{ $prayers->count() }}</div>
            <div class="digest-stat-label">Prayers Written</div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card text-center py-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="digest-stat-val">{{ $chapterComments->count() + $verseComments->count() }}</div>
            <div class="digest-stat-label">Notes Added</div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="row g-3 mb-3">

    {{-- Left column --}}
    <div class="col-lg-6">

        {{-- Chapters Read --}}
        <div class="card digest-card mb-3">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-book-open-variant me-1"></i>Chapters Read</p>
                @if($chaptersRead->isNotEmpty())
                    @foreach($chaptersRead as $bookId => $reads)
                        @php $book = $reads->first()->book; @endphp
                        <div class="digest-item d-flex align-items-center justify-content-between">
                            <span class="digest-ref">{{ $book?->name ?? 'Unknown' }}</span>
                            <span class="badge" style="background: rgba(201,168,76,0.12); color: var(--sword-navy); font-weight: 600; font-size: 0.72rem;">
                                {{ $reads->count() }} {{ Str::plural('chapter', $reads->count()) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="digest-empty">
                        <i class="mdi mdi-book-open-page-variant mdi-36px d-block mb-2" style="color: rgba(14,22,40,0.15);"></i>
                        No chapters read this week yet
                    </div>
                @endif
            </div>
        </div>

        {{-- Prayers --}}
        <div class="card digest-card">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-heart me-1"></i>Prayers Written</p>
                @if($prayers->isNotEmpty())
                    @foreach($prayers as $prayer)
                        <div class="digest-item">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($prayer->type)
                                    <span class="badge" style="background: var(--sword-navy); color: var(--sword-gold); font-size: 0.65rem; font-weight: 600;">{{ $prayer->type->name }}</span>
                                @endif
                                <span style="font-size: 0.7rem; color: #9ca3af;">{{ \Carbon\Carbon::parse($prayer->date)->format('M j') }}</span>
                            </div>
                            <p class="digest-snippet mb-0">{{ Str::limit($prayer->content, 120) }}</p>
                        </div>
                    @endforeach
                @else
                    <div class="digest-empty">
                        <i class="mdi mdi-heart-outline mdi-36px d-block mb-2" style="color: rgba(14,22,40,0.15);"></i>
                        No prayers recorded this week
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right column --}}
    <div class="col-lg-6">

        {{-- Memory Practice --}}
        <div class="card digest-card mb-3">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-brain me-1"></i>Memory Practice</p>

                @foreach($completedMemories as $memory)
                    <div class="mb-3 p-2 rounded" style="background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);">
                        <div class="mb-1" style="font-size: 0.82rem; color: var(--sword-navy); font-weight: 600;">
                            <i class="mdi mdi-trophy" style="color: var(--sword-gold);"></i>
                            {{ $memory->title ?? 'Untitled Set' }} — completed!
                        </div>
                        @foreach($memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $ref => $verses)
                            <div class="mb-1" style="font-size: 0.78rem; color: var(--sword-gold); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">
                                {{ $ref }}:{{ $verses->pluck('number')->sort()->values()->join(', ') }}
                            </div>
                            <p class="mb-0" style="font-size: 0.78rem; line-height: 1.55; color: #4b5563; font-style: italic;">
                                @foreach($verses->sortBy('number') as $verse)
                                    <sup class="fw-bold me-1" style="font-style: normal;">{{ $verse->number }}</sup>{{ $verse->text }}
                                @endforeach
                            </p>
                        @endforeach
                    </div>
                @endforeach

                @foreach($startedThisWeek as $memory)
                    <div class="digest-item mb-2">
                        <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">New memory goal started</div>
                        <span class="digest-ref">{{ $memory->title ?? 'Untitled Set' }}</span>
                        <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">
                            {{ $memory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number)->map(fn($vs, $ref) => $ref . ':' . $vs->pluck('number')->sort()->values()->join(', '))->values()->join(' · ') }}
                        </div>
                    </div>
                @endforeach

                @if($activeMemories->isNotEmpty())
                    @foreach($activeMemories as $memory)
                        <div class="digest-item d-flex align-items-center justify-content-between">
                            <span class="digest-ref">{{ $memory->title ?? 'Untitled Set' }}</span>
                            <span style="font-size: 0.75rem; color: #9ca3af;">{{ $memory->verses_count }} {{ Str::plural('verse', $memory->verses_count) }}</span>
                        </div>
                    @endforeach
                @elseif($completedMemories->isEmpty() && $startedThisWeek->isEmpty())
                    <div class="digest-empty">
                        <i class="mdi mdi-brain mdi-36px d-block mb-2" style="color: rgba(14,22,40,0.15);"></i>
                        No active memory sets
                        <div class="mt-2">
                            <a href="{{ route('memory.index') }}" style="font-size: 0.78rem; color: var(--sword-gold);">Start memorizing →</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Commentary --}}
        <div class="card digest-card">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-pencil me-1"></i>Commentary Added</p>
                @php $allNotes = $chapterComments->map(fn($c) => ['type' => 'chapter', 'model' => $c])->concat($verseComments->map(fn($c) => ['type' => 'verse', 'model' => $c]))->sortByDesc(fn($n) => $n['model']->created_at); @endphp
                @if($allNotes->isNotEmpty())
                    @foreach($allNotes as $note)
                        @php $m = $note['model']; $book = $m->chapter?->book; @endphp
                        <div class="digest-item">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="digest-ref">
                                    {{ $book?->name }}
                                    {{ $m->chapter?->number }}@if($note['type'] === 'verse'):{{ $m->verse_number }}@endif
                                </span>
                                <span class="badge" style="background: rgba(14,22,40,0.07); color: #6b7280; font-size: 0.65rem;">{{ $note['type'] === 'verse' ? 'Verse' : 'Chapter' }}</span>
                            </div>
                            @if(strlen($m->comment) > 120)
                            <p class="digest-snippet mb-0">
                                <span class="snip-short">{{ Str::limit($m->comment, 120) }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">More</a></span>
                                <span class="snip-full" hidden>{{ $m->comment }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">Less</a></span>
                            </p>
                            @else
                            <p class="digest-snippet mb-0">{{ $m->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="digest-empty">
                        <i class="mdi mdi-file-document-outline mdi-36px d-block mb-2" style="color: rgba(14,22,40,0.15);"></i>
                        No commentary added this week
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- From Your Notes, One Year Ago --}}
@if($pastNote)
<div class="row mb-3">
    <div class="col-12">
        <div class="card past-note-card">
            <div class="card-body">
                <p class="digest-section-label mb-2">
                    <i class="mdi mdi-clock-time-eight-outline me-1"></i>From Your Notes, One Year Ago
                </p>
                @php
                    $book = $pastNote->chapter?->book;
                    if ($pastNoteType === 'verse') {
                        $ref = ($book?->name ?? '') . ' ' . ($pastNote->chapter?->number ?? '') . ':' . $pastNote->verse_number;
                    } else {
                        $ref = ($book?->name ?? '') . ' ' . ($pastNote->chapter?->number ?? '') . ' (chapter)';
                    }
                @endphp
                <div class="d-flex align-items-start gap-3">
                    <div style="flex-shrink: 0;">
                        <i class="mdi mdi-format-quote-open mdi-36px" style="color: rgba(201,168,76,0.4);"></i>
                    </div>
                    <div>
                        <p class="mb-1" style="font-size: 0.9rem; color: #374151; line-height: 1.6; font-style: italic;">{{ $pastNote->comment }}</p>
                        <p class="mb-0 digest-ref">{{ $ref }}</p>
                        <p class="mb-0" style="font-size: 0.7rem; color: #9ca3af; margin-top: 2px;">{{ $pastNote->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script>
document.querySelectorAll('.snip-toggle').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var p = link.closest('p');
        p.querySelector('.snip-short').hidden = !p.querySelector('.snip-short').hidden;
        p.querySelector('.snip-full').hidden = !p.querySelector('.snip-full').hidden;
    });
});
</script>
@endpush
