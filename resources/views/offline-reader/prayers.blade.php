@extends('base.layout')

@section('title', 'Offline Prayers')

@push('css')
@vite(['resources/js/offline/prayers.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3" id="offline-reader-empty-message">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Prayers</h3>
                <p class="page-subtitle mb-0" id="or-prayer-subtitle">No entries yet</p>
            </div>
            <span class="badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.7rem; font-weight: 600;">
                <i class="mdi mdi-cloud-off-outline me-1"></i>Offline
            </span>
        </div>

        <div id="or-prayer-list" class="row"></div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-plus-circle-outline me-2"></i>New Prayer</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="sword-modal-label">Type</label>
                    <select id="or-prayer-type" class="form-select sword-modal-select"></select>
                </div>
                <div class="sword-modal-section mb-3">
                    <textarea id="or-prayer-content" class="sword-modal-textarea" rows="3" placeholder="Write your prayer…"></textarea>
                </div>
                <button type="button" id="or-save-prayer" class="btn sword-modal-btn-save">
                    <i class="mdi mdi-content-save-outline me-1"></i> Save Entry
                </button>
            </div>
        </div>

        <div id="or-sync-status" class="small text-muted mt-3"></div>
    </div>
</div>
@endsection
