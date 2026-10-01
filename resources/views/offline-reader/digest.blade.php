@extends('base.layout')

@section('title', 'Offline Digest')

@push('css')
@vite(['resources/js/offline/digest.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3" id="offline-reader-empty-message">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">
        <div class="card">
            <div class="card-header">Weekly Digest Reflection</div>
            <div class="card-body">
                <p class="small text-muted">
                    Your summary of chapters read, prayers, and commentary for this week will be
                    attached automatically once this syncs — it isn't available offline.
                </p>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="or-digest-show-chapters" checked>
                    <label class="form-check-label" for="or-digest-show-chapters">Include chapters read</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="or-digest-show-prayers" checked>
                    <label class="form-check-label" for="or-digest-show-prayers">Include prayers</label>
                </div>

                <label class="form-label small fw-semibold">Fruits of the Spirit needing prayer</label>
                <div class="d-flex flex-wrap gap-2 mb-2" id="or-digest-fruits">
                    @foreach(['Love', 'Joy', 'Peace', 'Patience', 'Kindness', 'Goodness', 'Faithfulness', 'Self Control'] as $fruit)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="{{ $fruit }}">
                        <label class="form-check-label">{{ $fruit }}</label>
                    </div>
                    @endforeach
                </div>
                <textarea id="or-digest-fruits-description" class="form-control mb-3" placeholder="Fruits description"></textarea>

                <label class="form-label small fw-semibold">Idols</label>
                <div class="d-flex flex-wrap gap-2 mb-2" id="or-digest-idols">
                    @foreach(['Laziness', 'Comfort', 'Food', 'Work', 'Money', 'Status', 'Entertainment', 'Relationships', 'Control', 'Approval'] as $idol)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" value="{{ $idol }}">
                        <label class="form-check-label">{{ $idol }}</label>
                    </div>
                    @endforeach
                </div>
                <input type="text" id="or-digest-idols-other" class="form-control mb-2" placeholder="Other idols (comma separated)">
                <textarea id="or-digest-idols-description" class="form-control mb-3" placeholder="Idols description"></textarea>

                <textarea id="or-digest-impactful-scripture" class="form-control mb-3" placeholder="Most impactful scripture this week"></textarea>
                <textarea id="or-digest-additional-content" class="form-control mb-3" placeholder="Additional reflections"></textarea>
                <textarea id="or-digest-sermon-notes" class="form-control mb-3" placeholder="Sermon notes"></textarea>

                <button type="button" id="or-save-digest-draft" class="btn btn-secondary btn-sm">Save as Draft</button>
                <button type="button" id="or-save-digest-share" class="btn btn-primary btn-sm">Save &amp; Share</button>
            </div>
        </div>

        <div id="or-sync-status" class="small text-muted mt-3"></div>
    </div>
</div>
@endsection
