@extends('base.layout')

@section('title', 'Accountability Check-In')

@push('css')
<style>
.acc-section-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 700;
    color: var(--sword-gold);
    margin-bottom: 0.75rem;
}
.acc-stat-val { font-size: 1.5rem; font-weight: 700; color: var(--sword-navy); line-height: 1; }
.acc-stat-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-top: 0.2rem; }
.acc-stars .mdi { color: rgba(14,22,40,0.15); font-size: 1.1rem; }
.acc-stars .mdi.is-filled { color: var(--sword-gold); }
.acc-touchpoint { display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0; font-size: 0.85rem; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('digest.history') . '#accountability' }}" style="font-size: 0.78rem; color: var(--sword-gold); text-decoration: none;">
            <i class="mdi mdi-arrow-left"></i> Back to History
        </a>
        <h3 class="mb-1 fw-bold mt-1" style="color: var(--sword-navy);">Accountability Check-In</h3>
        <p class="mb-0" style="font-size: 0.78rem; color: #9ca3af;">Saved {{ $checkIn->created_at->format('M j, Y') }}</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($checkIn->is_shared)
            <button type="button" id="copy-link-btn"
                data-url="{{ route('accountability.shared.show', $checkIn->uuid) }}"
                class="btn btn-sm" style="background: transparent; color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.8rem; font-weight: 600;">
                <i class="mdi mdi-link-variant me-1"></i> Copy Link
            </button>
        @else
            <a href="{{ route('accountability.edit', $checkIn) }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem; font-weight: 600;">
                <i class="mdi mdi-pencil-outline me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('accountability.mark-shared', $checkIn) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.8rem; font-weight: 600;">
                    <i class="mdi mdi-share-variant me-1"></i> Share
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([['Days in the Word', $checkIn->word_days . '/7'], ['Days in Prayer', $checkIn->prayer_days . '/7'], ['Fellowship', $checkIn->fellowship_level], ['Overall', $checkIn->overall_number . '/10']] as [$label, $val])
    <div class="col-6 col-md-3">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body text-center py-3">
                <div class="acc-stat-val">{{ $val }}</div>
                <div class="acc-stat-label">{{ $label }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="acc-section-label">Quality of Time in the Word</p>
                <div class="acc-stars">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="mdi mdi-star {{ $i <= $checkIn->word_quality ? 'is-filled' : '' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="acc-section-label">Quality of Time in Prayer</p>
                <div class="acc-stars">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="mdi mdi-star {{ $i <= $checkIn->prayer_quality ? 'is-filled' : '' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" style="border-top: 2px solid var(--sword-gold);">
    <div class="card-body">
        <p class="acc-section-label">Touch with Corner Man</p>
        @foreach([
            ['corner_man_prayer_request', 'Asked for prayer'],
            ['corner_man_asked_how_to_pray', 'Asked how to pray for them'],
            ['corner_man_encouragement', 'Shared encouragement, scripture, or truth'],
            ['corner_man_multiple_touchpoints', 'Had multiple touchpoints'],
        ] as [$field, $label])
        <div class="acc-touchpoint">
            <i class="mdi {{ $checkIn->$field ? 'mdi-check-circle' : 'mdi-close-circle-outline' }}" style="color: {{ $checkIn->$field ? 'var(--sword-gold)' : '#d1d5db' }};"></i>
            {{ $label }}
        </div>
        @endforeach
    </div>
</div>

@if($checkIn->overall_why)
<div class="card mt-3" style="border-top: 2px solid var(--sword-gold);">
    <div class="card-body">
        <p class="acc-section-label">Why That Number?</p>
        <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->overall_why }}</p>
    </div>
</div>
@endif

@if($checkIn->one_praise || $checkIn->one_prayer)
<div class="row g-3 mt-1">
    @if($checkIn->one_praise)
    <div class="col-md-6">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="acc-section-label">One Praise</p>
                <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->one_praise }}</p>
            </div>
        </div>
    </div>
    @endif
    @if($checkIn->one_prayer)
    <div class="col-md-6">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body">
                <p class="acc-section-label">One Prayer</p>
                <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->one_prayer }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@if($comments->isNotEmpty())
<div class="card mt-3" style="border-top: 2px solid var(--sword-gold);">
    <div class="card-body">
        <p class="acc-section-label"><i class="mdi mdi-comment-text-outline me-1"></i>Comments</p>
        @foreach($comments as $c)
        <div class="py-2" style="border-bottom: 1px solid rgba(14,22,40,0.05); font-size: 0.85rem;">
            <div class="mb-1">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--sword-navy);">{{ $c->displayName() }}</span>
                <span style="font-size: 0.68rem; color: #9ca3af; margin-left: 0.4rem;">{{ $c->created_at->format('M j, Y') }}</span>
            </div>
            <p class="mb-0" style="color: #374151; line-height: 1.55;">{{ $c->comment }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('js')
<script>
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
