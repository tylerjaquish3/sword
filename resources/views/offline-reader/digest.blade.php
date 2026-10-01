@extends('base.layout')

@section('title', 'Offline Digest')

@push('css')
@include('digest._share-styles')
@vite(['resources/js/offline/digest.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3" id="offline-reader-empty-message">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">

        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <p class="share-section-label mb-1">Weekly Digest</p>
                <h3 class="mb-1 fw-bold" style="color: var(--sword-navy);">Offline Reflection</h3>
                <p class="mb-0" style="font-size: 0.85rem; color: #6b7280;">This week</p>
            </div>
        </div>

        <div class="row justify-content-center mb-1">
            <div class="col-12 col-lg-8">
                <div class="p-3 rounded" style="background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.25); font-size: 0.82rem; color: #4b5563; line-height: 1.6;">
                    <i class="mdi mdi-information-outline me-1" style="color: var(--sword-gold);"></i>
                    Your summary of chapters read, prayers, and commentary for this week will be
                    attached automatically once this syncs — it isn't available offline, so only
                    your written reflections below are shown here. Use <strong>Save</strong> to
                    record this digest privately, or <strong>Save &amp; Share</strong> to generate
                    a link once it syncs.
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                {{-- What to include --}}
                <div class="card share-card mb-3">
                    <div class="card-body">
                        <p class="share-section-label"><i class="mdi mdi-eye me-1"></i>What to Include</p>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Choose which sections to include once your summary is attached.</p>

                        <div class="section-toggle">
                            <input class="form-check-input" type="checkbox" id="or-digest-show-chapters" checked>
                            <label class="form-check-label" for="or-digest-show-chapters" style="font-size: 0.85rem; cursor: pointer;">
                                <i class="mdi mdi-book-open-variant me-1" style="color: var(--sword-gold);"></i>
                                Chapters Read
                            </label>
                        </div>
                        <div class="section-toggle">
                            <input class="form-check-input" type="checkbox" id="or-digest-show-prayers" checked>
                            <label class="form-check-label" for="or-digest-show-prayers" style="font-size: 0.85rem; cursor: pointer;">
                                <i class="mdi mdi-heart me-1" style="color: var(--sword-gold);"></i>
                                Prayers Written
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Fruits of the Spirit --}}
                <div class="card share-card mb-3">
                    <div class="card-body">
                        <p class="share-section-label"><i class="mdi mdi-spa me-1"></i>Fruits of the Spirit</p>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Which fruits could use prayer this week?</p>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="or-digest-fruits">
                            @foreach(['Love', 'Joy', 'Peace', 'Patience', 'Kindness', 'Goodness', 'Faithfulness', 'Self Control'] as $fruit)
                            <label class="fruit-check">
                                <input type="checkbox" value="{{ $fruit }}" style="display:none;">
                                <span>{{ $fruit }}</span>
                            </label>
                            @endforeach
                        </div>
                        <textarea
                            id="or-digest-fruits-description"
                            class="form-control"
                            rows="2"
                            placeholder="Optional — why are these areas on your heart this week?"
                            style="font-size: 0.82rem; resize: vertical;"
                        ></textarea>
                    </div>
                </div>

                {{-- Impactful Scripture --}}
                <div class="card share-card mb-3">
                    <div class="card-body">
                        <p class="share-section-label"><i class="mdi mdi-star me-1"></i>Impactful Scripture</p>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">What chapter or verse has impacted you recently, and why?</p>
                        <textarea
                            id="or-digest-impactful-scripture"
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
                        <div class="d-flex flex-wrap gap-2 mb-3" id="or-digest-idols">
                            @foreach(['Laziness', 'Comfort', 'Food', 'Work', 'Money', 'Status', 'Entertainment', 'Relationships', 'Control', 'Approval'] as $idol)
                            <label class="idol-check">
                                <input type="checkbox" value="{{ $idol }}" style="display:none;">
                                <span>{{ $idol }}</span>
                            </label>
                            @endforeach
                        </div>
                        <input
                            type="text"
                            id="or-digest-idols-other"
                            class="form-control mb-3"
                            placeholder="Other (comma-separated)"
                            style="font-size: 0.82rem;"
                        >
                        <textarea
                            id="or-digest-idols-description"
                            class="form-control"
                            rows="2"
                            placeholder="Optional — reflect on how these have shown up this week"
                            style="font-size: 0.82rem; resize: vertical;"
                        ></textarea>
                    </div>
                </div>

                {{-- Sermon Notes --}}
                <div class="card share-card mb-3">
                    <div class="card-body">
                        <p class="share-section-label"><i class="mdi mdi-microphone me-1"></i>Sermon Notes</p>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">What was the theme? What did you learn? What questions do you have?</p>
                        <textarea
                            id="or-digest-sermon-notes"
                            class="form-control"
                            rows="5"
                            placeholder="e.g. Theme: grace in suffering. Learned that Paul's thorn wasn't removed but redeemed. Questions: what does 'strength in weakness' look like practically?"
                            style="font-size: 0.85rem; resize: vertical;"
                        ></textarea>
                    </div>
                </div>

                {{-- Additional Content --}}
                <div class="card share-card mb-3">
                    <div class="card-body">
                        <p class="share-section-label"><i class="mdi mdi-text me-1"></i>Additional Content</p>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Anything else you'd like to share with your accountability partner?</p>
                        <textarea
                            id="or-digest-additional-content"
                            class="form-control"
                            rows="4"
                            placeholder="Prayer requests, reflections, encouragement..."
                            style="font-size: 0.85rem; resize: vertical;"
                        ></textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="d-flex justify-content-end gap-2 mb-2">
                    <button type="button" id="or-save-digest-draft" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.85rem; font-weight: 600; padding: 0.4rem 1.25rem;">
                        <i class="mdi mdi-content-save-outline me-1"></i> Save
                    </button>
                    <button type="button" id="or-save-digest-share" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.85rem; font-weight: 600; padding: 0.4rem 1.25rem;">
                        <i class="mdi mdi-share-variant me-1"></i> Save &amp; Share
                    </button>
                </div>
                <div id="or-sync-status" class="small text-muted text-end mb-4"></div>
            </div>
        </div>

    </div>
</div>
@endsection
