@extends('base.layout')

@section('title', 'Offline Reader')

@push('css')
@include('translations._reader-selector-styles')
@vite(['resources/js/offline/read.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3" id="offline-reader-empty-message">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">

        <div class="row">
            <div class="col-12 mb-4 mb-xl-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="text-dark font-weight-bold mb-0">Read</h3>
                    <span class="badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.7rem; font-weight: 600;">
                        <i class="mdi mdi-cloud-off-outline me-1"></i>Offline
                    </span>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12 grid-margin grid-margin-md-0 stretch-card">
                <div class="card">
                    <div class="card-header p-0">
                        <div class="reader-selector-bar">
                            <div class="rsel-group rsel-translation">
                                <span class="rsel-label">Version</span>
                                <select class="rsel-native" id="or-translation"></select>
                                <i class="mdi mdi-chevron-down rsel-chevron"></i>
                            </div>

                            <div class="rsel-divider"></div>

                            <div class="rsel-group rsel-book">
                                <span class="rsel-label">Book</span>
                                <select class="rsel-native" id="or-book"></select>
                            </div>

                            <div class="rsel-divider"></div>

                            <div class="rsel-group rsel-chapter">
                                <span class="rsel-label">Ch.</span>
                                <select class="rsel-native" id="or-chapter"></select>
                                <i class="mdi mdi-chevron-down rsel-chevron"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="or-verse-list" style="font-size: 1rem; line-height: 1.9;"></div>

                        <div class="reading-section-divider my-4"></div>

                        <div class="reading-notes-section mb-3">
                            <div class="reading-notes-header">
                                <span class="notes-icon"><i class="mdi mdi-note-text"></i></span>
                                <span class="notes-title">Chapter Notes</span>
                            </div>
                            <div id="or-chapter-comments-list" class="reading-notes-body mb-2">
                                <p class="reading-notes-empty mb-0">No chapter notes yet.</p>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <textarea id="or-chapter-comment-input" class="form-control form-control-sm" rows="1" placeholder="Add a note on this chapter" style="font-size: 0.85rem;"></textarea>
                                <button type="button" id="or-save-chapter-comment" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); white-space: nowrap;">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Verse comment + highlight modal, styled with the shared .sword-modal system --}}
        <div class="modal fade" id="or-verse-panel" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content sword-modal">

                    <div class="modal-header sword-modal-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="sword-modal-icon"><i class="mdi mdi-book-open-variant"></i></div>
                            <div>
                                <h5 class="modal-title mb-0" id="or-panel-reference">Verse</h5>
                                <p class="sword-modal-subtitle mb-0">Commentary &amp; highlight</p>
                            </div>
                        </div>
                        <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <div class="modal-body sword-modal-body">

                        <div class="sword-modal-section mb-4">
                            <div class="sword-modal-section-header">
                                <span class="sword-modal-section-icon"><i class="mdi mdi-comment-text-multiple"></i></span>
                                <span class="sword-modal-section-title">Comments</span>
                            </div>
                            <div class="sword-modal-section-body">
                                <div id="or-panel-comments-list" class="mb-3" style="max-height:180px;overflow-y:auto;">
                                    <p class="text-muted mb-0">No comments yet.</p>
                                </div>
                                <label class="sword-modal-label">Add New Comment</label>
                                <textarea class="sword-modal-textarea" id="or-comment-input" rows="2" placeholder="Add a new comment…" style="border-top:1px solid #f0ebe2 !important;"></textarea>
                            </div>
                        </div>

                        <div class="sword-modal-section mb-2">
                            <div class="sword-modal-section-header">
                                <span class="sword-modal-section-icon"><i class="mdi mdi-palette"></i></span>
                                <span class="sword-modal-section-title">Highlight</span>
                            </div>
                            <div class="sword-modal-section-body">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="or-color-btn" data-color="yellow" title="Important" style="background:#fef08a;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                                    <button type="button" class="or-color-btn" data-color="blue"   title="Prophecy"  style="background:#93c5fd;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                                    <button type="button" class="or-color-btn" data-color="green"  title="Promise"   style="background:#86efac;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                                    <button type="button" class="or-color-btn" data-color="red"    title="Command"   style="background:#fca5a5;border:2px solid transparent;border-radius:6px;width:32px;height:32px;cursor:pointer;"></button>
                                    <span class="text-muted ms-1" style="font-size:0.75rem;">Click again to remove</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer sword-modal-footer">
                        <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn sword-modal-btn-save" id="or-save-comment">
                            <i class="mdi mdi-content-save-outline me-1"></i>Save Comment
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
