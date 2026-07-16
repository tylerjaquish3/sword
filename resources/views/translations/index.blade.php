@extends('base.layout')

@section('title', 'Read & Compare')

@section('content')  

<div class="row">
    <div class="col-12 mb-4 mb-xl-0">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="text-dark font-weight-bold mb-0">Read &amp; Compare</h3>
                <a id="book-study-link" href="#" class="d-none" style="font-size: 0.78rem; color: var(--sword-gold); text-decoration: none;">
                    <i class="mdi mdi-book-open-page-variant"></i> <span id="book-study-link-text"></span>
                </a>
            </div>
            <div class="d-flex">
                <button type="button" id="btn-edit-book-info" class="btn bg-white btn-icon me-2" title="Edit book info" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                    <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" id="btn-chapter-note" class="btn bg-white btn-icon me-2" title="Add chapter note">
                    +<i class="mdi mdi-note-text"></i>
                </button>
                @if(auth()->user()->is_admin)
                <button type="button" id="btn-section-editor" class="btn bg-white btn-icon me-2" title="Edit section titles &amp; paragraphs">
                    <i class="mdi mdi-format-section"></i>
                </button>
                @endif
                <button type="button" id="btn-single-col" class="btn btn-primary btn-icon" title="Single column">
                    <i class="mdi mdi-rectangle-outline"></i>
                </button>
                <button type="button" id="btn-double-col" class="btn bg-white btn-icon ms-2" title="Compare columns">
                    <i class="mdi mdi-view-split-vertical"></i>
                </button>
                <button type="button" id="btn-cross-ref" class="btn bg-white btn-icon ms-2" title="Cross references">
                    <i class="mdi mdi-link-variant"></i>
                </button>
                <button type="button" id="btn-read-aloud" class="btn bg-white btn-icon ms-2" title="Read aloud">
                    <i class="mdi mdi-volume-high"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div id="reading-col" class="col-12 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header p-0">
                <div class="reader-selector-bar">
                    {{-- Translation pill --}}
                    <div class="rsel-group rsel-translation">
                        <span class="rsel-label">Version</span>
                        <select class="rsel-native" id="translation_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ ($defaultTranslationId ?? null) == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>

                    <div class="rsel-divider"></div>

                    {{-- Book (select2) --}}
                    <div class="rsel-group rsel-book">
                        <span class="rsel-label">Book</span>
                        <select class="form-select select2-books rsel-native" id="book_select">
                            <option value="">— choose —</option>
                            <optgroup label="Old Testament">
                                @foreach ($books->where('new_testament', 0) as $book)
                                    <option value="{{ $book->id }}">{{ $book->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="New Testament">
                                @foreach ($books->where('new_testament', 1) as $book)
                                    <option value="{{ $book->id }}">{{ $book->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="rsel-divider"></div>

                    {{-- Chapter --}}
                    <div class="rsel-group rsel-chapter">
                        <span class="rsel-label">Ch.</span>
                        <select class="rsel-native" id="chapter_select">
                            <option value=1>1</option>
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>

                    <div class="rsel-divider"></div>

                    {{-- Quick next-chapter button --}}
                    <button type="button" id="btn-rsel-next" class="rsel-next-btn" title="Next chapter">
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter_content"></div>

                <div class="xref-footnotes-section mb-3 d-none" id="xref_footnotes_section">
                    <div class="reading-notes-header">
                        <span class="notes-icon xref-footnote-glyph">&dagger;</span>
                        <span class="notes-title">Cross References</span>
                    </div>
                    <div id="xref_footnotes_body" class="reading-notes-body xref-footnotes-body"></div>
                </div>

                <div class="reading-section-divider my-4"></div>

                <div id="book-info" class="reading-book-info mb-4">
                    <div class="reading-book-meta">
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-account-outline me-1"></i>Author</span>
                            <span id="book-author" class="reading-meta-value"></span>
                        </div>
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-calendar-range-outline me-1"></i>Timeframe</span>
                            <span id="book-timeframe" class="reading-meta-value"></span>
                        </div>
                        <div class="reading-meta-row">
                            <span class="reading-meta-label"><i class="mdi mdi-text-subject me-1"></i>About</span>
                            <span id="book-description" class="reading-meta-value"></span>
                        </div>
                    </div>
                    <button type="button" id="btn-edit-book" class="reading-edit-btn btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#bookEditModal">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                </div>

                <div class="reading-notes-section mb-3">
                    <div class="reading-notes-header">
                        <span class="notes-icon"><i class="mdi mdi-note-text"></i></span>
                        <span class="notes-title">Chapter Notes</span>
                        <a href="#" id="chapter_comment_link" class="ms-auto btn btn-sm btn-secondary reading-edit-btn" title="Add chapter note"><i class="mdi mdi-plus"></i></a>
                    </div>
                    <div id="chapter_comments_display" class="reading-notes-body">
                        <p class="reading-notes-empty mb-0">No chapter notes yet.</p>
                    </div>
                </div>

                <div class="reading-actions">
                    <button type="button" id="btn-mark-read" class="btn btn-outline-success btn-sm"><i class="mdi mdi-check"></i> Mark as Read</button>
                    <small id="read-status-display" class="reading-read-status"></small>
                </div>

                <div class="reading-nav mt-4">
                    <button type="button" id="btn-prev-chapter" class="btn btn-outline-secondary reading-nav-btn">
                        <i class="mdi mdi-chevron-left"></i> Prev
                    </button>
                    <span id="chapter-nav-label" class="reading-nav-label"></span>
                    <button type="button" id="btn-next-chapter" class="btn btn-outline-secondary reading-nav-btn">
                        Next <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="compare-col" class="col-sm-6 grid-margin grid-margin-md-0 stretch-card d-none">
        <div class="card">
            <div class="card-header p-0">
                <div class="reader-selector-bar">
                    <div class="rsel-group" style="flex:1;">
                        <span class="rsel-label">Compare with</span>
                        <select class="rsel-native" id="translation2_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}" {{ $translation->name == 'NIV' ? 'selected' : '' }}>{{ $translation->name }}</option>
                            @endforeach
                        </select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter2_content"></div>
            </div>
        </div>
    </div>
    <div id="xref-col" class="col-sm-6 grid-margin grid-margin-md-0 stretch-card d-none">
        <div class="card xref-panel">
            <div class="card-header p-0">
                <div class="reader-selector-bar">
                    <div class="rsel-group" style="flex:1;">
                        <span class="rsel-label">From Verse</span>
                        <select class="rsel-native" id="xref_source_verse"></select>
                        <i class="mdi mdi-chevron-down rsel-chevron"></i>
                    </div>
                </div>
            </div>
            <div class="card-body xref-body">

                <div class="xref-source-preview" id="xref_source_preview"></div>

                <div class="xref-connector">
                    <span class="xref-connector-glyph">&dagger;</span>
                    <span class="xref-connector-label">correlates to</span>
                </div>

                <div class="xref-lookup">
                    <label class="xref-label" for="xref_target_select">Find a verse</label>
                    <select class="xref-target-select" id="xref_target_select" style="width:100%"></select>
                </div>
                <div class="xref-target-preview d-none" id="xref_target_preview"></div>

                <button type="button" class="btn xref-add-btn" id="xref_add_btn" disabled>
                    <i class="mdi mdi-link-variant me-1"></i>Add Cross Reference
                </button>

                <div class="xref-divider"></div>

                <div class="xref-existing-header">Existing Cross References</div>
                <div id="xref_existing_list" class="xref-existing-list">
                    <p class="text-muted mb-0 xref-empty">No cross references for this verse yet.</p>
                </div>

            </div>
        </div>
    </div>
</div>

@include('commentary.modals.verse')
@include('commentary.modals.chapter')

<div class="modal fade" id="bookEditModal" tabindex="-1" aria-labelledby="bookEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-book-open-page-variant"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="bookEditModalLabel">Edit Book Info</h5>
                        <p class="sword-modal-subtitle mb-0" id="book-edit-title"></p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-account"></i></span>
                        <span class="sword-modal-section-title">Author</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="book-edit-author" placeholder="e.g. Moses">
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-subject"></i></span>
                        <span class="sword-modal-section-title">Description</span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="book-edit-description" rows="3" placeholder="Brief overview of the book…"></textarea>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-calendar-range-outline"></i></span>
                        <span class="sword-modal-section-title">Timeframe</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="book-edit-timeframe" placeholder="e.g. ~1446–1406 BC">
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="btn-save-book">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save
                </button>
            </div>

        </div>
    </div>
</div>

@if(auth()->user()->is_admin)
<div class="modal fade" id="sectionEditorModal" tabindex="-1" aria-labelledby="sectionEditorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-format-section"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="sectionEditorModalLabel">Section Titles &amp; Paragraphs</h5>
                        <p class="sword-modal-subtitle mb-0" id="section-editor-subtitle"></p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" id="section-editor-close" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="section-editor-table">
                        <thead>
                            <tr>
                                <th style="min-width:200px;">Section Title</th>
                                <th style="width:110px;" class="text-center">New Paragraph</th>
                                <th>Verse Text</th>
                            </tr>
                        </thead>
                        <tbody id="section-editor-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" id="section-editor-cancel">Close</button>
                <button type="button" class="btn sword-modal-btn-save" id="section-editor-save">
                    <i class="mdi mdi-content-save-outline me-1"></i>Save Changes
                </button>
            </div>

        </div>
    </div>
</div>
@endif

@endsection

@push('css')
<style>
/* ── Reader selector bar ─────────────────────────────────────── */
.reader-selector-bar {
    display: flex;
    align-items: stretch;
    background: #0e1628;
    border-radius: 0.375rem 0.375rem 0 0;
    overflow: hidden;
    min-height: 56px;
}

.rsel-group {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 26px 16px 8px;
    position: relative;
    cursor: pointer;
    transition: background 0.18s;
    min-width: 0;
}
.rsel-group:hover { background: rgba(201,168,76,0.08); }

.rsel-translation { flex: 0 0 auto; min-width: 80px; }
.rsel-book        { flex: 1 1 auto; }
.rsel-chapter     { flex: 0 0 auto; min-width: 64px; }

.rsel-label {
    position: absolute;
    top: 10px;
    left: 16px;
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(201,168,76,0.6);
    pointer-events: none;
}

/* Native selects (translation + chapter) */
.rsel-native {
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
}
.rsel-native option,
.rsel-native optgroup { background: #0e1628; color: #e2e8f0; }

.rsel-chevron {
    position: absolute;
    right: 10px;
    bottom: 12px;
    font-size: 0.85rem;
    color: rgba(201,168,76,0.5);
    pointer-events: none;
}

/* Dividers */
.rsel-divider {
    width: 1px;
    background: rgba(201,168,76,0.15);
    align-self: stretch;
    flex-shrink: 0;
}

/* ── select2 inside the bar ──────────────────────────────────── */
.rsel-book .select2-container {
    width: 100% !important;
}
.rsel-book .select2-container--default .select2-selection--single {
    background: transparent !important;
    border: none !important;
    height: auto !important;
    min-height: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    line-height: 1 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    line-height: normal !important;
    padding: 0 20px 0 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: rgba(255,255,255,0.35) !important;
    font-weight: 400 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: rgba(201,168,76,0.5) transparent transparent transparent !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent rgba(201,168,76,0.8) transparent !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__clear {
    color: rgba(201,168,76,0.6) !important;
    font-size: 1rem !important;
    margin-right: 18px !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single {
    border: none !important;
    box-shadow: none !important;
}

/* ── Quick next-chapter button in selector bar ──────────────── */
.rsel-next-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    outline: none;
    color: rgba(201,168,76,0.85);
    font-size: 1.5rem;
    padding: 10px 14px 0;
    cursor: pointer;
    transition: color 0.15s, background 0.15s;
    align-self: stretch;
    flex-shrink: 0;
}
.rsel-next-btn:hover:not(:disabled) {
    color: rgba(201,168,76,1);
    background: rgba(201,168,76,0.08);
}
.rsel-next-btn:disabled {
    color: rgba(201,168,76,0.2);
    cursor: default;
}

/* ── Bottom gold accent line on active group ─────────────────── */
.reader-selector-bar::after {
    content: '';
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(201,168,76,0.4), transparent);
    pointer-events: none;
}
.reader-selector-bar { position: relative; }

/* ── Cross reference marker + footnotes ──────────────────────── */
.xref-marker {
    color: var(--sword-bronze);
    font-size: 0.65rem;
    font-weight: 700;
    margin-left: 1px;
    cursor: pointer;
}
.xref-footnotes-section {
    border: 1px solid rgba(138,106,69,0.18);
    border-radius: 8px;
    background: rgba(138,106,69,0.04);
    overflow: hidden;
}
.xref-footnote-glyph {
    color: var(--sword-bronze) !important;
    font-weight: 700;
}
.xref-footnotes-body { padding: 4px 18px 14px; }
.xref-footnote-row {
    font-size: 0.85rem;
    padding: 5px 0;
    border-bottom: 1px dashed rgba(138,106,69,0.18);
    transition: background 0.4s;
}
.xref-footnote-row:last-child { border-bottom: none; }
.xref-footnote-row.xref-footnote-flash { background: rgba(138,106,69,0.14); }
.xref-footnote-glyph-inline { color: var(--sword-bronze); font-weight: 700; margin-right: 4px; }
.xref-footnote-verse { color: #6b7280; font-weight: 600; margin-right: 4px; }
.xref-footnote-link { color: var(--sword-bronze); font-weight: 600; text-decoration: none; }
.xref-footnote-link:hover { text-decoration: underline; }
.xref-footnote-preview { color: #9ca3af; }

/* ── Cross reference side panel ──────────────────────────────── */
.xref-panel .card-body.xref-body { padding: 18px; }
.xref-source-preview {
    font-style: italic;
    color: #374151;
    font-size: 0.92rem;
    line-height: 1.5;
    padding-bottom: 14px;
    border-bottom: 1px solid #f0ebe2;
}
.xref-connector {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 14px 0;
    color: var(--sword-bronze-dim);
}
.xref-connector-glyph {
    font-size: 1rem;
    font-weight: 700;
    color: var(--sword-bronze);
}
.xref-connector-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.xref-connector::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(138,106,69,0.2);
}
.xref-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-bottom: 4px;
}
.xref-target-preview {
    font-style: italic;
    color: #374151;
    font-size: 0.88rem;
    line-height: 1.5;
    margin-top: 8px;
    padding: 8px 10px;
    background: var(--sword-bronze-glow);
    border-radius: 6px;
}
.xref-add-btn {
    width: 100%;
    margin-top: 14px;
    background: var(--sword-bronze);
    border-color: var(--sword-bronze);
    color: #fff;
    font-weight: 600;
    font-size: 0.88rem;
}
.xref-add-btn:hover:not(:disabled) { background: #75593a; border-color: #75593a; color: #fff; }
.xref-add-btn:disabled { opacity: 0.4; }
.xref-divider {
    height: 1px;
    background: #f0ebe2;
    margin: 20px 0 14px;
}
.xref-existing-header {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-bottom: 8px;
}
.xref-existing-list { max-height: 260px; overflow-y: auto; }
.xref-existing-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid #f6f3ee;
}
.xref-existing-row:last-child { border-bottom: none; }
.xref-existing-ref { font-weight: 600; font-size: 0.85rem; color: var(--sword-bronze); }
.xref-existing-preview { font-size: 0.8rem; color: #9ca3af; margin-top: 2px; }
.xref-delete-btn {
    color: #b91c1c;
    background: transparent;
    border: none;
    padding: 2px 4px;
    flex-shrink: 0;
}
.xref-delete-btn:hover { color: #7f1d1d; }
.xref-empty { font-size: 0.85rem; }
</style>
@endpush


@push('js')
<script>

const defaultTranslationId = {{ $defaultTranslationId ?? 'null' }};

var ttsActive = false;

// ── Cross reference state ──────────────────────────────────────────
var currentChapterDbId = null;   // chapters.id for the chapter currently shown on the main pane
var currentChapterXrefs = [];    // footnote rows for the current chapter, from GET /verse-links
var xrefVerseTextByNumber = {};  // verse number -> text, for the "From Verse" preview
var xrefSelectedTarget = null;   // {id: "chapterId:verseNumber", text, preview} picked in the select2 lookup

function stopSpeech() {
    if (window.speechSynthesis) window.speechSynthesis.cancel();
    ttsActive = false;
    $('#btn-read-aloud').removeClass('btn-warning').addClass('bg-white')
        .attr('title', 'Read aloud')
        .find('i').removeClass('mdi-stop-circle-outline').addClass('mdi-volume-high');
}

$(document).ready(function() {

    // ── Read Aloud (Web Speech API) ───────────────────────────────
    $('#btn-read-aloud').on('click', function() {
        if (!window.speechSynthesis) {
            alert('Your browser does not support text-to-speech.');
            return;
        }
        if (ttsActive) {
            stopSpeech();
            return;
        }
        var text = '';
        $('#chapter_content .verse-clickable').each(function() {
            var $clone = $(this).clone();
            $clone.find('sup').remove();
            text += $clone.text().trim() + ' ';
        });
        text = text.trim();
        if (!text) return;

        var utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.95;
        utterance.onend = function() { stopSpeech(); };
        utterance.onerror = function() { stopSpeech(); };

        ttsActive = true;
        $('#btn-read-aloud').removeClass('bg-white').addClass('btn-warning')
            .attr('title', 'Stop reading')
            .find('i').removeClass('mdi-volume-high').addClass('mdi-stop-circle-outline');
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
    });

    $('#btn-mark-read').on('click', function() {
        const btn = $(this);
        $.ajax({
            url: '/chapters/mark-read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                book_id: $('#book_select').val(),
                chapter_number: $('#chapter_select').val(),
                translation_id: $('#translation_select').val(),
            },
            success: function() {
                btn.removeClass('btn-outline-success').addClass('btn-success');
                setTimeout(function() {
                    btn.removeClass('btn-success').addClass('btn-outline-success');
                }, 2000);
                const now = new Date();
                const formatted = now.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                $('#read-status-display').text('Last read: ' + formatted);
            }
        });
    });

    $('#btn-chapter-note').on('click', function() {
        $('#chapter_comment_link').trigger('click');
    });

    // The right-hand column shows one of: nothing, the compare translation, or the
    // cross-reference panel. Compare and cross-ref are mutually exclusive — picking
    // one while the other is open swaps the content instead of adding a third column.
    function setRightColumnMode(mode) { // 'none' | 'compare' | 'xref'
        $('#compare-col').toggleClass('d-none', mode !== 'compare');
        $('#xref-col').toggleClass('d-none', mode !== 'xref');
        $('#reading-col')
            .toggleClass('col-12', mode === 'none')
            .toggleClass('col-sm-6', mode !== 'none');

        $('#btn-single-col').toggleClass('btn-primary', mode === 'none').toggleClass('bg-white', mode !== 'none');
        $('#btn-double-col').toggleClass('btn-primary', mode === 'compare').toggleClass('bg-white', mode !== 'compare');
        $('#btn-cross-ref').toggleClass('btn-primary', mode === 'xref').toggleClass('bg-white', mode !== 'xref');

        if (mode === 'compare') lookupVerses(2);
        if (mode === 'xref') renderXrefExistingList();
    }

    $('#btn-single-col').on('click', function() {
        setRightColumnMode('none');
    });

    $('#btn-double-col').on('click', function() {
        setRightColumnMode($('#compare-col').hasClass('d-none') ? 'compare' : 'none');
    });

    $('#btn-cross-ref').on('click', function() {
        setRightColumnMode($('#xref-col').hasClass('d-none') ? 'xref' : 'none');
    });

    // ── Cross reference panel ──────────────────────────────────────
    function formatXrefResult(item) {
        if (item.loading || !item.preview) return item.text;
        return $('<div>')
            .append($('<div>').addClass('fw-semibold').text(item.text))
            .append($('<div>').css({ 'font-size': '0.78rem', color: '#9ca3af' }).text(item.preview));
    }

    $('#xref_target_select').select2({
        placeholder: 'Search by reference or keyword…',
        allowClear: true,
        minimumInputLength: 2,
        dropdownParent: $('#xref-col'),
        templateResult: formatXrefResult,
        ajax: {
            url: '{{ route("verse-links.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) {
                return { results: data.map(function(v) { return { id: v.id, text: v.text, preview: v.preview }; }) };
            }
        }
    });

    $('#xref_target_select').on('select2:select', function(e) {
        xrefSelectedTarget = e.params.data;
        $('#xref_target_preview').text(xrefSelectedTarget.preview || '').removeClass('d-none');
        updateXrefAddButtonState();
    });

    $('#xref_target_select').on('select2:clear', function() {
        xrefSelectedTarget = null;
        $('#xref_target_preview').addClass('d-none').empty();
        updateXrefAddButtonState();
    });

    $(document).on('change', '#xref_source_verse', function() {
        updateXrefSourcePreview();
        renderXrefExistingList();
        updateXrefAddButtonState();
    });

    $('#xref_add_btn').on('click', function() {
        if (!xrefSelectedTarget || !currentChapterDbId) return;
        const sourceNum = $('#xref_source_verse').val();
        const parts = xrefSelectedTarget.id.split(':');
        const $btn = $(this).prop('disabled', true);

        $.ajax({
            url: '{{ route("verse-links.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                chapter_id: currentChapterDbId,
                verse_number: sourceNum,
                linked_chapter_id: parts[0],
                linked_verse_number: parts[1],
            },
            success: function() {
                $('#xref_target_select').val(null).trigger('change');
                $('#xref_target_preview').addClass('d-none').empty();
                xrefSelectedTarget = null;
                loadChapterXrefs();
            },
            error: function() {
                $btn.prop('disabled', false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'Error adding cross reference' });
                } else {
                    alert('Error adding cross reference');
                }
            }
        });
    });

    $(document).on('click', '.xref-delete-btn', function() {
        const linkId = $(this).data('link-id');
        $.ajax({
            url: '/verse-links/' + linkId,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function() { loadChapterXrefs(); }
        });
    });

    $(document).on('click', '.xref-marker', function() {
        const num = $(this).data('verse-number');
        const $target = $('#xref_footnotes_body .xref-footnote-row[data-verse-number="' + num + '"]').first();
        if (!$target.length) return;
        $target.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
        $target.addClass('xref-footnote-flash');
        setTimeout(function() { $target.removeClass('xref-footnote-flash'); }, 1200);
    });

    $(document).on('click', '.xref-footnote-link', function(e) {
        e.preventDefault();
        navigateToReference($(this).data('book-id'), $(this).data('chapter-number'));
    });

    // Read query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramTranslation = urlParams.get('translation');
    const paramBook = urlParams.get('book');
    const paramChapter = urlParams.get('chapter');

    // Set initial values from query params if present
    if (paramTranslation) {
        $('#translation_select').val(paramTranslation);
    }
    if (paramBook) {
        $('#book_select').val(paramBook).trigger('change.select2');
    }

    // Keep compare dropdown in sync — remove whichever version is selected in the main picker
    const allTranslations = @json($translations->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));
    function syncCompareOptions() {
        const selectedId = parseInt($('#translation_select').val());
        const currentCompare = parseInt($('#translation2_select').val());
        const $compare = $('#translation2_select');
        $compare.empty();
        allTranslations.forEach(function(t) {
            if (t.id !== selectedId) {
                $compare.append(new Option(t.name, t.id));
            }
        });
        // Restore previous selection if still available, otherwise pick first
        if (currentCompare && currentCompare !== selectedId) {
            $compare.val(currentCompare);
        }
    }
    syncCompareOptions();

    // When translation changes, preserve the current chapter
    $('#translation_select').change(function() {
        syncCompareOptions();
        book_id = $('#book_select').val();
        var currentChapter = $('#chapter_select').val();
        loadChapters(book_id, function() {
            if (currentChapter) {
                $('#chapter_select').val(currentChapter);
            }
            lookupVerses('');
            lookupVerses(2);
            loadReadStatus();
        });
    });

    // When book changes, update chapter options and both sides
    $('#book_select').change(function() {
        book_id = $(this).val();
        loadChapters(book_id, function() {
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo(book_id);
    });

    // When chapter changes, update verses in both sides
    $('#chapter_select').change(function() {
        lookupVerses('');
        lookupVerses(2);
        loadChapterComments();
        loadReadStatus();
    });

    // When translation2 changes, update the compare side
    $('#translation2_select').change(function() {
        lookupVerses(2);
    });

    function initWithDefaults() {
        var bookId = $('#book_select').val();
        if (!bookId) return;
        if (defaultTranslationId) {
            $('#translation_select').val(defaultTranslationId);
        }
        loadChapters(bookId, function() {
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo(bookId);
    }

    // Load chapters for the initially selected book on page load,
    // defaulting to last-read position unless query params override.
    if (paramTranslation || paramBook || paramChapter) {
        loadChapters($('#book_select').val(), function() {
            if (paramChapter) {
                $('#chapter_select').val(paramChapter);
            }
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
        });
        loadBookInfo($('#book_select').val());
    } else {
        $.get('/chapters/last-read')
            .done(function(last) {
                if (last && last.book_id) {
                    $('#translation_select').val(last.translation_id);
                    $('#book_select').val(last.book_id).trigger('change.select2');
                    loadChapters(last.book_id, function() {
                        $('#chapter_select').val(last.chapter_number);
                        lookupVerses('');
                        lookupVerses(2);
                        loadChapterComments();
                        loadReadStatus();
                    });
                    loadBookInfo(last.book_id);
                } else {
                    initWithDefaults();
                }
            })
            .fail(function() {
                initWithDefaults();
            });
    }

});

    function loadChapters(book_id, callback) {
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
                if (callback) callback();
            }
        });
    }

    function loadChapterComments() {
        let bookId = $('#book_select').val();
        let chapterNumber = $('#chapter_select').val();
        
        $.ajax({
            url: '/chapters/comments?book_id=' + bookId + '&chapter_number=' + chapterNumber,
            type: 'GET',
            success: function(response) {
                let commentsHtml = '';
                if (response.comments && response.comments.length > 0) {
                    const sorted = response.comments.slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    sorted.forEach(function(comment) {
                        let date = new Date(comment.created_at);
                        let formattedDate = date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        commentsHtml += '<div class="mb-2 pb-2 border-bottom">';
                        commentsHtml += '<small class="text-muted">' + formattedDate + '</small>';
                        commentsHtml += '<p class="mb-0 mt-1">' + comment.comment + '</p>';
                        commentsHtml += '</div>';
                    });
                } else {
                    commentsHtml = '<p class="text-muted mb-0">No chapter notes yet.</p>';
                }
                $('#chapter_comments_display').html(commentsHtml);
            }
        });
    }

    function loadBookInfo(bookId) {
        if (!bookId) return;
        $('#book-study-link').attr('href', '/books/' + bookId + '/study').removeClass('d-none');
        $.get('/books/' + bookId, function(book) {
            var desc = book.description || '';
            $('#book-author').text(book.author || '—');
            $('#book-timeframe').text(book.timeframe || '—');
            $('#book-description').text(desc.length > 120 ? desc.substring(0, 120) + '…' : (desc || '—'));
            // Pre-populate modal fields
            $('#book-edit-title').text(book.name);
            $('#book-edit-author').val(book.author || '');
            $('#book-edit-timeframe').val(book.timeframe || '');
            $('#book-edit-description').val(book.description || '');
            $('#book-study-link-text').text('Study ' + book.name);
        });
    }

    $('#btn-save-book').on('click', function() {
        var bookId = $('#book_select').val();
        if (!bookId) return;
        $.ajax({
            url: '/books/' + bookId,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                author: $('#book-edit-author').val(),
                timeframe: $('#book-edit-timeframe').val(),
                description: $('#book-edit-description').val(),
            },
            success: function() {
                var savedDesc = $('#book-edit-description').val();
                $('#book-author').text($('#book-edit-author').val() || '—');
                $('#book-timeframe').text($('#book-edit-timeframe').val() || '—');
                $('#book-description').text(savedDesc.length > 120 ? savedDesc.substring(0, 120) + '…' : (savedDesc || '—'));
                bootstrap.Modal.getInstance(document.getElementById('bookEditModal')).hide();
            }
        });
    });

    function loadReadStatus() {
        var bookId = $('#book_select').val();
        var chapterNumber = $('#chapter_select').val();
        var translationId = $('#translation_select').val();
        if (!bookId || !chapterNumber || !translationId) return;
        $.get('/chapters/read-status', {
            book_id: bookId,
            chapter_number: chapterNumber,
            translation_id: translationId,
        }, function(response) {
            if (response && response.read_at) {
                var date = new Date(response.read_at);
                var formatted = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                $('#read-status-display').text('Last read: ' + formatted);
            } else {
                $('#read-status-display').text('');
            }
        });
    }

    function updateChapterNavLabel() {
        var bookName = $('#book_select option:selected').text();
        var chapter  = $('#chapter_select').val();
        $('#chapter-nav-label').text(bookName + ' ' + chapter);

        var isFirstBook    = $('#book_select option:selected').is(':first-child');
        var isLastBook     = $('#book_select option:selected').is(':last-child');
        var isFirstChapter = $('#chapter_select option:selected').is(':first-child');
        var isLastChapter  = $('#chapter_select option:selected').is(':last-child');

        $('#btn-prev-chapter').prop('disabled', isFirstBook && isFirstChapter);
        $('#btn-next-chapter').prop('disabled', isLastBook  && isLastChapter);
        $('#btn-rsel-next').prop('disabled',    isLastBook  && isLastChapter);
    }

    $('#btn-prev-chapter').on('click', function() {
        var $chapterSelect = $('#chapter_select');
        var $bookSelect    = $('#book_select');

        if (!$chapterSelect.find('option:selected').is(':first-child')) {
            // Stay in book, go to previous chapter
            $chapterSelect.find('option:selected').prev().prop('selected', true);
            $chapterSelect.trigger('change');
        } else {
            // Cross book boundary — go to last chapter of previous book
            var $prevBook = $bookSelect.find('option:selected').prev();
            if (!$prevBook.length) return;
            $bookSelect.val($prevBook.val()).trigger('change.select2');
            loadBookInfo($prevBook.val());
            loadChapters($prevBook.val(), function() {
                $chapterSelect.find('option:last-child').prop('selected', true);
                lookupVerses('');
                lookupVerses(2);
                loadChapterComments();
                loadReadStatus();
                updateChapterNavLabel();
            });
        }
    });

    $('#btn-next-chapter').on('click', function() {
        var $chapterSelect = $('#chapter_select');
        var $bookSelect    = $('#book_select');

        if (!$chapterSelect.find('option:selected').is(':last-child')) {
            // Stay in book, go to next chapter
            $chapterSelect.find('option:selected').next().prop('selected', true);
            $chapterSelect.trigger('change');
        } else {
            // Cross book boundary — go to chapter 1 of next book
            var $nextBook = $bookSelect.find('option:selected').next();
            if (!$nextBook.length) return;
            $bookSelect.val($nextBook.val()).trigger('change.select2');
            loadBookInfo($nextBook.val());
            loadChapters($nextBook.val(), function() {
                $chapterSelect.find('option:first-child').prop('selected', true);
                lookupVerses('');
                lookupVerses(2);
                loadChapterComments();
                loadReadStatus();
                updateChapterNavLabel();
            });
        }
    });

    $('#btn-rsel-next').on('click', function() {
        $('#btn-next-chapter').trigger('click');
    });

    function updateXrefAddButtonState() {
        const sourceNum = $('#xref_source_verse').val();
        let disabled = true;
        if (xrefSelectedTarget && sourceNum && currentChapterDbId) {
            disabled = xrefSelectedTarget.id === (currentChapterDbId + ':' + sourceNum);
        }
        $('#xref_add_btn').prop('disabled', disabled);
    }

    function updateXrefSourceOptions(verses) {
        const current = $('#xref_source_verse').val();
        const $sel = $('#xref_source_verse').empty();
        xrefVerseTextByNumber = {};
        verses.forEach(function(v) {
            xrefVerseTextByNumber[v.number] = v.text;
            $sel.append('<option value="' + v.number + '">' + v.number + '</option>');
        });
        if (current && xrefVerseTextByNumber[current]) {
            $sel.val(current);
        }
        updateXrefSourcePreview();
        updateXrefAddButtonState();
    }

    function updateXrefSourcePreview() {
        const num = $('#xref_source_verse').val();
        const text = xrefVerseTextByNumber[num] || '';
        const $preview = $('#xref_source_preview').empty();
        $preview.append($('<sup>').addClass('text-muted me-1').text(num));
        $preview.append(document.createTextNode(text));
    }

    function loadChapterXrefs() {
        if (!currentChapterDbId) return;
        $.get('{{ route("verse-links.index") }}', {
            chapter_id: currentChapterDbId,
            translation_id: $('#translation_select').val(),
        }, function(links) {
            currentChapterXrefs = links || [];
            renderXrefMarkers();
            renderXrefFootnotes();
            renderXrefExistingList();
        });
    }

    function renderXrefMarkers() {
        $('#chapter_content .xref-marker').remove();
        const seen = {};
        currentChapterXrefs.forEach(function(l) {
            if (seen[l.verse_number]) return;
            seen[l.verse_number] = true;
            $('#chapter_content .verse-clickable[data-verse-number="' + l.verse_number + '"]').first()
                .after('<sup class="xref-marker" data-verse-number="' + l.verse_number + '" title="Cross references">&dagger;</sup>');
        });
    }

    function renderXrefFootnotes() {
        const $section = $('#xref_footnotes_section');
        const $body = $('#xref_footnotes_body').empty();
        if (!currentChapterXrefs.length) { $section.addClass('d-none'); return; }

        const sorted = currentChapterXrefs.slice().sort(function(a, b) { return a.verse_number - b.verse_number; });
        sorted.forEach(function(l) {
            const $row = $('<div>').addClass('xref-footnote-row').attr('data-verse-number', l.verse_number);
            $row.append($('<span>').addClass('xref-footnote-glyph-inline').html('&dagger;'));
            $row.append($('<span>').addClass('xref-footnote-verse').text('v.' + l.verse_number));
            $row.append(document.createTextNode(' → '));
            $row.append(
                $('<a>').addClass('xref-footnote-link').attr('href', '#')
                    .data('book-id', l.linked_book_id).data('chapter-number', l.linked_chapter_number)
                    .text(l.linked_reference || 'Unknown reference')
            );
            if (l.linked_preview) {
                $row.append($('<span>').addClass('xref-footnote-preview').text(' — ' + l.linked_preview));
            }
            $body.append($row);
        });
        $section.removeClass('d-none');
    }

    function renderXrefExistingList() {
        const verseNum = parseInt($('#xref_source_verse').val());
        const $list = $('#xref_existing_list').empty();
        const matches = currentChapterXrefs.filter(function(l) { return l.verse_number === verseNum; });

        if (!matches.length) {
            $list.html('<p class="text-muted mb-0 xref-empty">No cross references for this verse yet.</p>');
            return;
        }

        matches.forEach(function(l) {
            const $row = $('<div>').addClass('xref-existing-row');
            const $info = $('<div>');
            $info.append($('<div>').addClass('xref-existing-ref').text(l.linked_reference || 'Unknown reference'));
            if (l.linked_preview) {
                $info.append($('<div>').addClass('xref-existing-preview').text(l.linked_preview));
            }
            const $del = $('<button type="button">').addClass('xref-delete-btn').attr('title', 'Remove cross reference')
                .data('link-id', l.id).html('<i class="mdi mdi-delete-outline"></i>');
            $row.append($info).append($del);
            $list.append($row);
        });
    }

    function navigateToReference(bookId, chapterNumber) {
        if (!bookId) return;
        $('#book_select').val(bookId).trigger('change.select2');
        loadBookInfo(bookId);
        loadChapters(bookId, function() {
            $('#chapter_select').val(chapterNumber);
            lookupVerses('');
            lookupVerses(2);
            loadChapterComments();
            loadReadStatus();
            updateChapterNavLabel();
        });
    }

    function lookupVerses(side)
    {
        if (!side) stopSpeech();
        translation_id = $('#translation'+side+'_select').val();
        // Always use the main book/chapter selectors
        book_id = $('#book_select').val();
        chapter_id = $('#chapter_select').val();

        // Preserve content area height while loading so the page doesn't collapse and shift scroll position
        const $chapterContent = $('#chapter'+side+'_content');
        const prevHeight = $chapterContent.outerHeight();
        if (prevHeight) $chapterContent.css('min-height', prevHeight + 'px');

        // Show loading spinner
        $chapterContent.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: '/translations/verses?translation_id='+translation_id+'&book_id='+book_id+'&chapter_id='+chapter_id,
            type: 'GET',
            success: function(response) {
                $chapterContent.empty();
                const hlBg = { yellow: '#fef9c3', blue: '#dbeafe', green: '#dcfce7', red: '#fee2e2' };
                let html = '<p>';
                response.forEach(function(verse) {
                    // Add prefix (contains HTML like <br> or <h5>Header</h5>)
                    if (verse.prefix) {
                        html += verse.prefix;
                    }
                    let highlightStyle = '';
                    if (verse.highlight_color && hlBg[verse.highlight_color]) {
                        highlightStyle = 'background-color:' + hlBg[verse.highlight_color] + ';padding:0px 4px 2px;border-radius:3px;';
                    }
                    if (verse.has_commentary) {
                        highlightStyle += 'text-decoration:underline dotted #94a3b8;text-underline-offset:3px;';
                    }
                    html += '<span class="verse-clickable" data-verse-id="' + verse.id + '" data-verse-number="' + verse.number + '" style="cursor:pointer;' + highlightStyle + '">';
                    html += '<sup class="text-muted">' + verse.number + '</sup> ' + verse.text;
                    html += '</span>';
                    if (verse.is_favorite) {
                        html += '<sup style="color:#f59e0b;font-size:0.6rem;margin-left:1px;">★</sup>';
                    }
                    html += ' ';
                });
                html += '</p>';
                $chapterContent.html(html);
                $chapterContent.css('min-height', '');
                if (!side) {
                    updateChapterNavLabel();
                    currentChapterDbId = response.length ? response[0].chapter_id : null;
                    updateXrefSourceOptions(response);
                    loadChapterXrefs();
                }
            },
            error: function() {
                $chapterContent.css('min-height', '');
            }
        });
    }

    // ── Section Titles & Paragraphs bulk editor (admin only) ──────
    let sectionEditorDirty = false;

    function confirmDiscardSectionEditorChanges() {
        if (!sectionEditorDirty) return true;
        return confirm('You have unsaved changes in the section editor. Discard them?');
    }

    function loadSectionEditorTable() {
        const bookId = $('#book_select').val();
        const chapterNumber = $('#chapter_select').val();
        const translationId = $('#translation_select').val();
        if (!bookId || !chapterNumber) return;

        $('#section-editor-subtitle').text($('#book_select option:selected').text() + ' ' + chapterNumber);
        $('#section-editor-tbody').html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.ajax({
            url: '/translations/section-editor',
            type: 'GET',
            data: { book_id: bookId, chapter_number: chapterNumber, translation_id: translationId },
            success: function(response) {
                const $tbody = $('#section-editor-tbody').empty();
                (response.verses || []).forEach(function(verse) {
                    const $row = $('<tr>').attr('data-verse-number', verse.number);

                    const $titleInput = $('<input type="text" class="form-control form-control-sm section-title-input">')
                        .val(verse.section_title || '')
                        .attr('placeholder', 'e.g., The Beatitudes');
                    $row.append($('<td>').append($titleInput));

                    const $checkboxCell = $('<td class="text-center">');
                    const $checkbox = $('<input type="checkbox" class="form-check-input line-break-input">')
                        .prop('checked', !!verse.line_break);
                    $checkboxCell.append($checkbox);
                    $row.append($checkboxCell);

                    const $textCell = $('<td>');
                    $textCell.append($('<sup class="text-muted me-1">').text(verse.number));
                    $textCell.append(document.createTextNode(verse.text));
                    $row.append($textCell);

                    $tbody.append($row);
                });
                sectionEditorDirty = false;
            }
        });
    }

    $('#btn-section-editor').on('click', function() {
        loadSectionEditorTable();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('sectionEditorModal')).show();
    });

    $(document).on('input', '#section-editor-tbody .section-title-input', function() {
        sectionEditorDirty = true;
    });
    $(document).on('change', '#section-editor-tbody .line-break-input', function() {
        sectionEditorDirty = true;
    });

    $('#section-editor-close, #section-editor-cancel').on('click', function() {
        if (!confirmDiscardSectionEditorChanges()) return;
        sectionEditorDirty = false;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('sectionEditorModal')).hide();
    });

    $('#section-editor-save').on('click', function() {
        const bookId = $('#book_select').val();
        const chapterNumber = $('#chapter_select').val();
        const rows = [];
        $('#section-editor-tbody tr').each(function() {
            rows.push({
                number: $(this).data('verse-number'),
                section_title: $(this).find('.section-title-input').val().trim(),
                line_break: $(this).find('.line-break-input').is(':checked') ? 1 : 0,
            });
        });

        const $btn = $(this).prop('disabled', true);
        $.ajax({
            url: '/translations/section-editor',
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                book_id: bookId,
                chapter_number: chapterNumber,
                verses: rows,
            },
            success: function() {
                sectionEditorDirty = false;
                $btn.prop('disabled', false);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('sectionEditorModal')).hide();
                if (typeof lookupVerses === 'function') {
                    lookupVerses('');
                    lookupVerses(2);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'Error saving changes' });
                } else {
                    alert('Error saving changes');
                }
            }
        });
    });
</script>
@endpush