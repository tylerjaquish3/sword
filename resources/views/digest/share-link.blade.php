@extends('base.layout')

@section('title', 'Share Link Ready')

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
.link-box {
    background: rgba(14,22,40,0.03);
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-family: monospace;
    font-size: 0.82rem;
    word-break: break-all;
    color: var(--sword-navy);
}
</style>
@endpush

@section('content')

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="share-section-label mb-1">Shareable Link Ready</p>
        <h3 class="mb-1 fw-bold" style="color: var(--sword-navy);">Your Digest Has Been Shared</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #6b7280;">
            {{ $shared->week_start->format('M j') }} – {{ $shared->week_end->format('M j, Y') }}
        </p>
    </div>
    <a href="{{ route('digest.weekly') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.8rem; font-weight: 600;">
        <i class="mdi mdi-arrow-left"></i> Back to Digest
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card" style="border-top: 2px solid var(--sword-gold);">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="mdi mdi-check-circle mdi-48px" style="color: var(--sword-gold);"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color: var(--sword-navy);">Link Created</h5>
                <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem;">
                    Copy the link below and send it to your accountability partner.
                </p>

                <div class="link-box mb-3" id="share-link">{{ route('digest.shared.show', $shared->uuid) }}</div>

                <button
                    class="btn btn-sm"
                    style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.85rem; font-weight: 600;"
                    onclick="copyLink()"
                    id="copy-btn"
                >
                    <i class="mdi mdi-content-copy me-1"></i> Copy Link
                </button>

                <div class="mt-4 pt-3" style="border-top: 1px solid rgba(14,22,40,0.06);">
                    <a href="{{ route('digest.shared.show', $shared->uuid) }}" target="_blank" style="font-size: 0.82rem; color: var(--sword-gold);">
                        Preview how it looks <i class="mdi mdi-open-in-new"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyLink() {
    const link = document.getElementById('share-link').textContent.trim();
    navigator.clipboard.writeText(link).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Copied!';
        btn.style.background = '#16a34a';
        btn.style.borderColor = '#16a34a';
        btn.style.color = '#fff';
        setTimeout(() => {
            btn.innerHTML = '<i class="mdi mdi-content-copy me-1"></i> Copy Link';
            btn.style.background = 'var(--sword-navy)';
            btn.style.borderColor = 'rgba(201,168,76,0.3)';
            btn.style.color = 'var(--sword-gold)';
        }, 2500);
    });
}
</script>
@endpush

@endsection
