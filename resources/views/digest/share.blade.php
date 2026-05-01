@extends('base.layout')

@section('title', 'Complete Weekly Digest')

@push('css')
<style>
.share-section-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--sword-gold);
    font-weight: 700;
    margin-bottom: 0.75rem;
}
.share-card {
    border-top: 2px solid var(--sword-gold);
}
.fruit-check, .idol-check {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    border: 1px solid rgba(14,22,40,0.15);
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.82rem;
    transition: background 0.15s, border-color 0.15s;
    user-select: none;
}
.fruit-check input:checked + span,
.idol-check input:checked + span {
    font-weight: 600;
}
.fruit-check:has(input:checked) {
    background: rgba(201,168,76,0.12);
    border-color: var(--sword-gold);
}
.idol-check:has(input:checked) {
    background: rgba(14,22,40,0.07);
    border-color: var(--sword-navy);
}
.section-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid rgba(14,22,40,0.06);
}
.section-toggle:last-child { border-bottom: none; }
.form-check-input:checked { background-color: var(--sword-navy); border-color: var(--sword-navy); }
</style>
@endpush

@section('content')

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="share-section-label mb-1">Weekly Digest</p>
        <h3 class="mb-1 fw-bold" style="color: var(--sword-navy);">Complete This Week's Digest</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #6b7280;">
            {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}
        </p>
    </div>
    <a href="{{ route('digest.weekly') }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
        <i class="mdi mdi-arrow-left"></i> Back to Digest
    </a>
</div>

<form action="{{ $formAction ?? route('digest.complete.store') }}" method="POST">
    @csrf

    <div class="row g-3">

        {{-- Left column --}}
        <div class="col-lg-6">

            {{-- What to include --}}
            <div class="card share-card mb-3">
                <div class="card-body">
                    <p class="share-section-label"><i class="mdi mdi-eye me-1"></i>What to Include</p>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Choose which sections of your digest to share.</p>

                    <div class="section-toggle">
                        <input class="form-check-input" type="checkbox" name="show_chapters" id="show_chapters" value="1"
                            {{ $chaptersRead->isNotEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_chapters" style="font-size: 0.85rem; cursor: pointer;">
                            <i class="mdi mdi-book-open-variant me-1" style="color: var(--sword-gold);"></i>
                            Chapters Read
                            <span style="color: #9ca3af; font-size: 0.75rem;">({{ $chaptersRead->sum(fn($r) => $r->count()) }} chapters)</span>
                        </label>
                    </div>

                    <div class="section-toggle">
                        <input class="form-check-input" type="checkbox" name="show_prayers" id="show_prayers" value="1"
                            {{ $prayers->isNotEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_prayers" style="font-size: 0.85rem; cursor: pointer;">
                            <i class="mdi mdi-heart me-1" style="color: var(--sword-gold);"></i>
                            Prayers Written
                            <span style="color: #9ca3af; font-size: 0.75rem;">({{ $prayers->count() }} prayers)</span>
                        </label>
                    </div>

                    <div class="section-toggle">
                        <input class="form-check-input" type="checkbox" name="show_commentary" id="show_commentary" value="1"
                            {{ ($chapterComments->isNotEmpty() || $verseComments->isNotEmpty()) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_commentary" style="font-size: 0.85rem; cursor: pointer;">
                            <i class="mdi mdi-pencil me-1" style="color: var(--sword-gold);"></i>
                            Commentary
                            <span style="color: #9ca3af; font-size: 0.75rem;">({{ $chapterComments->count() + $verseComments->count() }} notes)</span>
                        </label>
                    </div>

                    <div class="section-toggle">
                        <input class="form-check-input" type="checkbox" name="show_memory" id="show_memory" value="1"
                            {{ $activeMemories->isNotEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_memory" style="font-size: 0.85rem; cursor: pointer;">
                            <i class="mdi mdi-brain me-1" style="color: var(--sword-gold);"></i>
                            Memory Practice
                            <span style="color: #9ca3af; font-size: 0.75rem;">({{ $activeMemories->count() }} active sets)</span>
                        </label>
                    </div>

                    <div class="section-toggle">
                        <input class="form-check-input" type="checkbox" name="show_past_note" id="show_past_note" value="1"
                            {{ $pastNote ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_past_note" style="font-size: 0.85rem; cursor: pointer;">
                            <i class="mdi mdi-clock-time-eight-outline me-1" style="color: var(--sword-gold);"></i>
                            Note from One Year Ago
                            @if(!$pastNote)
                                <span style="color: #9ca3af; font-size: 0.75rem;">(none found)</span>
                            @endif
                        </label>
                    </div>
                </div>
            </div>

            {{-- Fruits of the Spirit --}}
            <div class="card share-card mb-3">
                <div class="card-body">
                    <p class="share-section-label"><i class="mdi mdi-spa me-1"></i>Fruits of the Spirit</p>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Which fruits could use prayer this week?</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['Love', 'Joy', 'Peace', 'Patience', 'Kindness', 'Goodness', 'Faithfulness', 'Self Control'] as $fruit)
                        <label class="fruit-check">
                            <input type="checkbox" name="fruits_needing_prayer[]" value="{{ $fruit }}" style="display:none;">
                            <span>{{ $fruit }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- Right column --}}
        <div class="col-lg-6">

            {{-- Impactful Scripture --}}
            <div class="card share-card mb-3">
                <div class="card-body">
                    <p class="share-section-label"><i class="mdi mdi-star me-1"></i>Impactful Scripture</p>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">What chapter or verse has impacted you recently, and why?</p>
                    <textarea
                        name="impactful_scripture"
                        class="form-control"
                        rows="5"
                        placeholder="e.g. Romans 8:28 — this verse reminded me that even in difficulty, God is working..."
                        style="font-size: 0.85rem; resize: vertical;"
                    ></textarea>
                </div>
            </div>

            {{-- Idols --}}
            <div class="card share-card mb-3">
                <div class="card-body">
                    <p class="share-section-label"><i class="mdi mdi-alert-circle-outline me-1"></i>Idols to Surrender</p>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">What have you been putting above God this week?</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach(['Laziness', 'Comfort', 'Food', 'Work', 'Money', 'Status', 'Entertainment', 'Relationships', 'Control', 'Approval'] as $idol)
                        <label class="idol-check">
                            <input type="checkbox" name="idols[]" value="{{ $idol }}" style="display:none;">
                            <span>{{ $idol }}</span>
                        </label>
                        @endforeach
                    </div>
                    <input
                        type="text"
                        name="idols_other"
                        class="form-control"
                        placeholder="Other (comma-separated)"
                        style="font-size: 0.82rem;"
                    >
                </div>
            </div>

            {{-- Additional Content --}}
            <div class="card share-card mb-3">
                <div class="card-body">
                    <p class="share-section-label"><i class="mdi mdi-text me-1"></i>Additional Content</p>
                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Anything else you'd like to share with your accountability partner?</p>
                    <textarea
                        name="additional_content"
                        class="form-control"
                        rows="4"
                        placeholder="Prayer requests, reflections, encouragement..."
                        style="font-size: 0.85rem; resize: vertical;"
                    ></textarea>
                </div>
            </div>

        </div>

    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('digest.weekly') }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.85rem;">
            Cancel
        </a>
        <button type="submit" name="submit_action" value="save" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.85rem; font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="mdi mdi-content-save-outline me-1"></i> Save
        </button>
        <button type="submit" name="submit_action" value="share" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.85rem; font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="mdi mdi-share-variant me-1"></i> Save &amp; Share
        </button>
    </div>

</form>

@endsection
