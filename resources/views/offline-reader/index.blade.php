@extends('base.layout')

@section('title', 'Offline Reader')

@push('css')
@vite(['resources/js/offline/offline-reader.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">
        <div class="row mb-3">
            <div class="col-4">
                <select id="or-translation" class="form-select"></select>
            </div>
            <div class="col-4">
                <select id="or-book" class="form-select"></select>
            </div>
            <div class="col-4">
                <select id="or-chapter" class="form-select"></select>
            </div>
        </div>
        <div id="or-verse-list"></div>

        <div class="modal fade" id="or-verse-panel" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h6 id="or-panel-reference"></h6>
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-sm or-color-btn" data-color="yellow" style="background:#f1c40f;">&nbsp;</button>
                            <button type="button" class="btn btn-sm or-color-btn" data-color="blue" style="background:#3498db;">&nbsp;</button>
                            <button type="button" class="btn btn-sm or-color-btn" data-color="green" style="background:#2ecc71;">&nbsp;</button>
                            <button type="button" class="btn btn-sm or-color-btn" data-color="red" style="background:#e74c3c;">&nbsp;</button>
                        </div>
                        <textarea id="or-comment-input" class="form-control mb-2" placeholder="Add a comment"></textarea>
                        <button type="button" id="or-save-comment" class="btn btn-primary btn-sm">Save Comment</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Add Prayer</div>
            <div class="card-body">
                <div id="or-prayer-list" class="mb-3"></div>
                <select id="or-prayer-type" class="form-select mb-2"></select>
                <textarea id="or-prayer-content" class="form-control mb-2" placeholder="Prayer content"></textarea>
                <button type="button" id="or-save-prayer" class="btn btn-primary btn-sm">Save Prayer</button>
            </div>
        </div>

        <div id="or-sync-status" class="small text-muted mt-3"></div>

        <div class="card mt-3">
            <div class="card-header">Chapter Notes</div>
            <div class="card-body">
                <div id="or-chapter-comments-list" class="mb-3"></div>
                <textarea id="or-chapter-comment-input" class="form-control mb-2" placeholder="Add a note on this chapter"></textarea>
                <button type="button" id="or-save-chapter-comment" class="btn btn-primary btn-sm">Save Chapter Note</button>
            </div>
        </div>
    </div>
</div>
@endsection
