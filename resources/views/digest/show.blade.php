@extends('base.layout')

@section('title', 'Digest — ' . $shared->week_start->format('M j') . '–' . $shared->week_end->format('M j, Y'))

@push('css')
<style>
.digest-section-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 700;
    color: var(--sword-gold);
    margin-bottom: 0.75rem;
}
.digest-stat-val {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--sword-navy);
    line-height: 1;
}
.digest-stat-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #9ca3af;
    margin-top: 0.2rem;
}
.digest-item {
    padding: 0.6rem 0;
    border-bottom: 1px solid rgba(14,22,40,0.05);
    font-size: 0.85rem;
    color: #374151;
}
.digest-item:last-child { border-bottom: none; }
.digest-badge {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 2px 7px;
    border-radius: 10px;
}
.digest-verse-ref {
    border-left: 3px solid rgba(201,168,76,0.35);
    padding: 0.3rem 0.6rem 0.3rem 0.65rem;
    margin-bottom: 0.45rem;
    font-size: 0.78rem;
    font-style: italic;
    line-height: 1.5;
    color: #6b7280;
    background: rgba(201,168,76,0.05);
    border-radius: 0 3px 3px 0;
}
</style>
@endpush

@section('content')

@php $snap = $shared->snapshot ?? []; @endphp

{{-- Header --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('digest.history') }}" style="font-size: 0.78rem; color: var(--sword-gold); text-decoration: none;">
            <i class="mdi mdi-arrow-left"></i> Digest History
        </a>
        <h3 class="mb-1 fw-bold mt-1" style="color: var(--sword-navy);">
            {{ $shared->week_start->format('M j') }} – {{ $shared->week_end->format('M j, Y') }}
        </h3>
        <p class="mb-0" style="font-size: 0.78rem; color: #9ca3af;">Saved {{ $shared->created_at->format('M j, Y') }}</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($shared->is_shared)
            <button type="button" id="copy-link-btn"
                data-url="{{ route('digest.shared.show', $shared->uuid) }}"
                class="btn btn-sm" style="background: transparent; color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.8rem; font-weight: 600;">
                <i class="mdi mdi-link-variant me-1"></i> Copy Link
            </button>
        @else
            <a href="{{ route('digest.edit', $shared) }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem; font-weight: 600;">
                <i class="mdi mdi-pencil-outline me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('digest.mark-shared', $shared) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.8rem; font-weight: 600;">
                    <i class="mdi mdi-share-variant me-1"></i> Share
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Stats row --}}
<div class="row g-3 mb-4">
    @foreach([['Days Studied', $snap['daysStudied'] ?? 0], ['Chapters Read', $snap['totalChapters'] ?? 0], ['Prayers', $snap['totalPrayers'] ?? 0], ['Notes', $snap['totalNotes'] ?? 0]] as [$label, $val])
    <div class="col-6 col-md-3">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body text-center py-3">
                <div class="digest-stat-val">{{ $val }}</div>
                <div class="digest-stat-label">{{ $label }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">

    {{-- Left column --}}
    <div class="col-lg-6">

        {{-- Chapters Read --}}
        @if($shared->show_chapters && !empty($snap['chaptersRead']))
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-book-open-variant me-1"></i>Chapters Read</p>
                @foreach($snap['chaptersRead'] as $entry)
                <div class="digest-item d-flex justify-content-between">
                    <span>{{ $entry['book'] }}</span>
                    <span style="color: #9ca3af; font-size: 0.8rem;">{{ $entry['count'] }} ch.</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Prayers --}}
        @if($shared->show_prayers && !empty($snap['prayers']))
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-heart me-1"></i>Prayers Written</p>
                @foreach($snap['prayers'] as $prayer)
                <div class="digest-item">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if($prayer['type'])
                            <span class="digest-badge" style="background: rgba(201,168,76,0.1); color: var(--sword-gold);">{{ $prayer['type'] }}</span>
                        @endif
                        <span style="font-size: 0.72rem; color: #9ca3af;">{{ $prayer['date'] }}</span>
                    </div>
                    <p class="mb-0" style="font-size: 0.83rem; color: #374151; line-height: 1.5;">{{ $prayer['content'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Past Note --}}
        @if($shared->show_past_note && !empty($snap['pastNote']))
        @php $past = $snap['pastNote']; @endphp
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold); background: linear-gradient(160deg, #fff 70%, rgba(201,168,76,0.05) 100%);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-clock-time-eight-outline me-1"></i>From One Year Ago</p>
                <p class="mb-1 fw-bold" style="font-size: 0.78rem; color: var(--sword-navy);">{{ $past['ref'] }}</p>
                <p class="mb-1" style="font-size: 0.83rem; color: #374151; font-style: italic; line-height: 1.5;">"{{ $past['comment'] }}"</p>
                <p class="mb-0" style="font-size: 0.72rem; color: #9ca3af;">{{ $past['date'] }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- Right column --}}
    <div class="col-lg-6">

        {{-- Commentary --}}
        @if($shared->show_commentary && !empty($snap['commentary']))
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-pencil me-1"></i>Commentary Added</p>
                @foreach($snap['commentary'] as $note)
                <div class="digest-item">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="digest-badge" style="background: {{ $note['type'] === 'verse' ? 'rgba(201,168,76,0.1)' : 'rgba(14,22,40,0.06)' }}; color: {{ $note['type'] === 'verse' ? 'var(--sword-gold)' : '#6b7280' }};">{{ $note['type'] }}</span>
                        <span style="font-size: 0.78rem; font-weight: 600; color: var(--sword-navy);">{{ $note['ref'] }}</span>
                        @if(!empty($note['date']))
                            <span style="font-size: 0.72rem; color: #9ca3af;">{{ $note['date'] }}</span>
                        @endif
                    </div>
                    @if($note['type'] === 'verse' && !empty($note['verse_text']))
                    <div class="digest-verse-ref">{{ $note['verse_text'] }}</div>
                    @endif
                    @if(strlen($note['comment']) > 240)
                    <p class="mb-0" style="font-size: 0.83rem; color: #374151; line-height: 1.5;">
                        <span class="snip-short">{{ Str::limit($note['comment'], 240) }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">More</a></span>
                        <span class="snip-full" hidden>{{ $note['comment'] }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">Less</a></span>
                    </p>
                    @else
                    <p class="mb-0" style="font-size: 0.83rem; color: #374151; line-height: 1.5;">{{ $note['comment'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Memory --}}
        @php
            $hasMemoryContent = !empty($snap['memories']) || ($snap['completedThisWeek'] ?? 0) > 0 || !empty($snap['completedMemories']) || !empty($snap['startedThisWeek']);
        @endphp
        @if($shared->show_memory && $hasMemoryContent)
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-brain me-1"></i>Memory Practice</p>

                @if(!empty($snap['completedMemories']))
                    @foreach($snap['completedMemories'] as $mem)
                    <div class="mb-3 p-2 rounded" style="background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);">
                        <div class="mb-1" style="font-size: 0.82rem; color: var(--sword-navy); font-weight: 600;">
                            <i class="mdi mdi-trophy" style="color: var(--sword-gold);"></i>
                            {{ $mem['title'] }} — completed!
                        </div>
                        @foreach($mem['verses'] as $verse)
                        <div style="font-size: 0.75rem; color: var(--sword-gold); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">{{ $verse['reference'] }}</div>
                        <p class="mb-1" style="font-size: 0.78rem; line-height: 1.55; color: #4b5563; font-style: italic;">{{ $verse['text'] }}</p>
                        @endforeach
                    </div>
                    @endforeach
                @elseif(($snap['completedThisWeek'] ?? 0) > 0)
                    {{-- backward compat: old snapshots only have the count --}}
                    <div class="digest-item">
                        <span style="color: var(--sword-gold); font-weight: 600;">{{ $snap['completedThisWeek'] }}</span> set{{ $snap['completedThisWeek'] > 1 ? 's' : '' }} completed this week
                    </div>
                @endif

                @foreach($snap['startedThisWeek'] ?? [] as $mem)
                <div class="digest-item mb-2">
                    <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">New memory goal started</div>
                    <span class="digest-ref">{{ $mem['title'] }}</span>
                    <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">
                        {{ collect($mem['verses'])->pluck('reference')->join(' · ') }}
                    </div>
                </div>
                @endforeach

                @if(!empty($snap['memories']))
                <p style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; font-weight: 700; margin: 0.75rem 0 0.25rem;">Still working on…</p>
                @foreach($snap['memories'] as $mem)
                <div class="digest-item d-flex justify-content-between">
                    <span>{{ $mem['title'] }}</span>
                    <span style="color: #9ca3af; font-size: 0.8rem;">{{ $mem['verses'] }} verses</span>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @endif

        {{-- Personal Reflections --}}
        @php
            $hasReflections = !empty($shared->fruits_needing_prayer)
                || !empty($shared->idols)
                || $shared->impactful_scripture
                || $shared->additional_content
                || $shared->sermon_notes;
        @endphp
        @if($hasReflections)
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-heart-outline me-1"></i>Personal Reflections</p>

                @if(!empty($shared->fruits_needing_prayer))
                <p style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-bottom: 0.4rem;">Fruits Needing Prayer</p>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach($shared->fruits_needing_prayer as $fruit)
                    <span class="digest-badge" style="background: rgba(201,168,76,0.1); color: var(--sword-gold);">{{ $fruit }}</span>
                    @endforeach
                </div>
                @if($shared->fruits_description)
                <p class="mb-3" style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $shared->fruits_description }}</p>
                @else
                <div class="mb-3"></div>
                @endif
                @endif

                @if(!empty($shared->idols))
                <p style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-bottom: 0.4rem;">Idols to Surrender</p>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach($shared->idols as $idol)
                    <span class="digest-badge" style="background: rgba(14,22,40,0.07); color: var(--sword-navy);">{{ $idol }}</span>
                    @endforeach
                </div>
                @if($shared->idols_description)
                <p class="mb-3" style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $shared->idols_description }}</p>
                @else
                <div class="mb-3"></div>
                @endif
                @endif

                @if($shared->impactful_scripture)
                <p style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-bottom: 0.4rem;">Impactful Scripture</p>
                <p class="mb-3" style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $shared->impactful_scripture }}</p>
                @endif

                @if($shared->sermon_notes)
                <p style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-bottom: 0.4rem;">Sermon Notes</p>
                <p class="mb-3" style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $shared->sermon_notes }}</p>
                @endif

                @if($shared->additional_content)
                <p style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-bottom: 0.4rem;">Additional Thoughts</p>
                <p class="mb-0" style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $shared->additional_content }}</p>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@if($comments->isNotEmpty())
<div class="row mt-2">
    <div class="col-12">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="digest-section-label"><i class="mdi mdi-comment-text-outline me-1"></i>Comments</p>
                @foreach($comments as $c)
                <div class="digest-item">
                    <div class="mb-1">
                        <span style="font-size: 0.72rem; font-weight: 700; color: var(--sword-navy);">{{ $c->displayName() }}</span>
                        <span style="font-size: 0.68rem; color: #9ca3af; margin-left: 0.4rem;">{{ $c->created_at->format('M j, Y') }}</span>
                    </div>
                    <p class="mb-0" style="font-size: 0.85rem; color: #374151; line-height: 1.55;">{{ $c->comment }}</p>
                </div>
                @endforeach
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

var copyBtn = document.getElementById('copy-link-btn');
if (copyBtn) {
    copyBtn.addEventListener('click', function() {
        var url = this.dataset.url;
        var btn = this;
        var orig = btn.innerHTML;
        var confirm = function() {
            btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Copied!';
            setTimeout(function() { btn.innerHTML = orig; }, 2000);
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(confirm);
        } else {
            var ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            confirm();
        }
    });
}
</script>
@endpush
