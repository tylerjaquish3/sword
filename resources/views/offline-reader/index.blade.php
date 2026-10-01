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
