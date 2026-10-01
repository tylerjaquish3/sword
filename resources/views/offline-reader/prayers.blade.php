@extends('base.layout')

@section('title', 'Offline Prayers')

@push('css')
@vite(['resources/js/offline/prayers.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">
        <div class="card">
            <div class="card-header">Add Prayer</div>
            <div class="card-body">
                <div id="or-prayer-list" class="mb-3"></div>
                <select id="or-prayer-type" class="form-select mb-2"></select>
                <textarea id="or-prayer-content" class="form-control mb-2" placeholder="Prayer content"></textarea>
                <button type="button" id="or-save-prayer" class="btn btn-primary btn-sm">Save Prayer</button>
            </div>
        </div>

        <div id="or-sync-status" class="small text-muted mt-3"></div>
    </div>
</div>
@endsection
