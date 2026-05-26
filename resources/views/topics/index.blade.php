@extends('base.layout')

@section('title', 'Study')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-1">
    <div>
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">Study</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">Deep-dive into topics and Bible books</p>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav mb-0 mt-3" id="studyTabs" role="tablist" style="border-bottom: 2px solid rgba(14,22,40,0.08); gap: 0;">
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2 fw-600" id="tab-topics" data-bs-toggle="tab" data-bs-target="#pane-topics" type="button" role="tab"
            style="border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; border-radius: 0; font-size: 0.88rem; color: #6b7280; background: transparent; font-weight: 600;">
            <i class="mdi mdi-tag-multiple me-1"></i> Topics
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">{{ $topics->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2" id="tab-books" data-bs-toggle="tab" data-bs-target="#pane-books" type="button" role="tab"
            style="border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; border-radius: 0; font-size: 0.88rem; color: #6b7280; background: transparent; font-weight: 600;">
            <i class="mdi mdi-book-open-variant me-1"></i> Books
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">{{ $activeStudies->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ── Topics Tab ─────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-topics" role="tabpanel">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 mt-3">
            <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">{{ $topics->count() }} {{ Str::plural('topic', $topics->count()) }}</p>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn bg-white btn-icon view-toggle active" id="gridViewBtn" data-view="grid" style="border: 1px solid rgba(14,22,40,0.15);">
                    <i class="mdi mdi-view-grid text-success"></i>
                </button>
                <button type="button" class="btn bg-white btn-icon view-toggle" id="listViewBtn" data-view="list" style="border: 1px solid rgba(14,22,40,0.15);">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i>
                </button>
                <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#createTopicModal"
                    style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
                    <i class="mdi mdi-plus"></i> New Topic
                </button>
            </div>
        </div>

        @include('topics.partials.card-view')
        @include('topics.partials.table-view')

    </div>

    {{-- ── Books Tab ──────────────────────────────────────────────── --}}
    <div class="tab-pane fade pt-3" id="pane-books" role="tabpanel">

        {{-- Header row --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">
                {{ $activeStudies->count() }} active {{ Str::plural('study', $activeStudies->count()) }}
            </p>
            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addBookStudyModal"
                style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
                <i class="mdi mdi-plus"></i> Add Book to Study
            </button>
        </div>

        {{-- Active studies --}}
        @if($activeStudies->isNotEmpty())
        <div class="row g-2 mb-4">
            @foreach($activeStudies as $study)
                @php
                    $book = $study->book;
                    $read = $chaptersReadByBook->get($book->id, 0);
                    $total = $book->chapters_count;
                    $pct = $total > 0 ? round($read / $total * 100) : 0;
                @endphp
                <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                    <div class="card h-100 book-study-card" style="border-top: 2px solid var(--sword-gold); position: relative;">
                        <form method="POST" action="{{ route('book-studies.destroy', $study) }}" class="book-study-delete-form" style="position: absolute; top: 6px; right: 6px; z-index: 2;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm p-0" style="line-height:1; background: none; border: none; color: #9ca3af; font-size: 0.8rem;" title="Remove study" onclick="return confirm('Remove this book study?')">
                                <i class="mdi mdi-close-circle"></i>
                            </button>
                        </form>
                        <a href="{{ route('books.study', $book) }}" class="card-body p-3 text-decoration-none d-block">
                            <span style="font-size: 0.62rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 2px;">
                                {{ $book->new_testament ? 'NT' : 'OT' }}
                            </span>
                            <p class="mb-2 fw-bold" style="font-size: 0.85rem; color: var(--sword-navy); line-height: 1.3; padding-right: 1rem;">{{ $book->name }}</p>
                            <div class="progress mb-1" style="height: 3px; background: rgba(14,22,40,0.08);">
                                <div class="progress-bar" style="width: {{ $pct }}%; background: var(--sword-gold);"></div>
                            </div>
                            <p class="mb-0" style="font-size: 0.65rem; color: #9ca3af;">{{ $read }} of {{ $total }} {{ Str::plural('chapter', $total) }} read</p>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 mb-3" style="border: 2px dashed rgba(14,22,40,0.1); border-radius: 10px;">
            <i class="mdi mdi-book-open-page-variant mdi-48px mb-2 d-block" style="color: rgba(14,22,40,0.15);"></i>
            <p class="mb-2" style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">No active book studies</p>
            <p class="mb-3" style="font-size: 0.82rem; color: #9ca3af;">Choose a book of the Bible to dive into.</p>
            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addBookStudyModal"
                style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
                <i class="mdi mdi-plus"></i> Add Book to Study
            </button>
        </div>
        @endif

        {{-- Completed studies --}}
        @if($completedStudies->isNotEmpty())
        <div>
            <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #9ca3af;" class="mb-2">
                Completed &mdash; {{ $completedStudies->count() }}
            </p>
            <div class="row g-2">
                @foreach($completedStudies as $study)
                    @php
                        $book = $study->book;
                        $read = $chaptersReadByBook->get($book->id, 0);
                        $total = $book->chapters_count;
                        $pct = $total > 0 ? round($read / $total * 100) : 0;
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                        <div class="card h-100 book-study-card" style="border-top: 2px solid rgba(14,22,40,0.12); opacity: 0.75; position: relative;">
                            <form method="POST" action="{{ route('book-studies.destroy', $study) }}" style="position: absolute; top: 6px; right: 6px; z-index: 2;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm p-0" style="line-height:1; background: none; border: none; color: #9ca3af; font-size: 0.8rem;" title="Remove" onclick="return confirm('Remove this book study?')">
                                    <i class="mdi mdi-close-circle"></i>
                                </button>
                            </form>
                            <a href="{{ route('books.study', $book) }}" class="card-body p-3 text-decoration-none d-block">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span style="font-size: 0.62rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">{{ $book->new_testament ? 'NT' : 'OT' }}</span>
                                    <i class="mdi mdi-check-circle" style="font-size: 0.72rem; color: #6b7280;"></i>
                                </div>
                                <p class="mb-2 fw-bold" style="font-size: 0.85rem; color: #6b7280; line-height: 1.3; padding-right: 1rem;">{{ $book->name }}</p>
                                <div class="progress mb-1" style="height: 3px; background: rgba(14,22,40,0.08);">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: rgba(14,22,40,0.2);"></div>
                                </div>
                                <p class="mb-0" style="font-size: 0.65rem; color: #9ca3af;">Completed {{ $study->completed_at->format('M j, Y') }}</p>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>

{{-- Create Topic Modal --}}
<div class="modal fade" id="createTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-tag-multiple"></i></div>
                    <div>
                        <h5 class="modal-title mb-0">New Topic</h5>
                        <p class="sword-modal-subtitle mb-0">Organize verses around a theme or idea</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text-subject"></i></span>
                        <span class="sword-modal-section-title">Name <span class="sword-modal-required">required</span></span>
                    </div>
                    <div class="sword-modal-section-body">
                        <input type="text" class="form-control sword-modal-input" id="create-topic-name" placeholder="e.g., Faith, Grace, The Holy Spirit">
                    </div>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-text"></i></span>
                        <span class="sword-modal-section-title">Description <span class="sword-modal-optional">optional</span></span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="create-topic-description" rows="3" placeholder="What is this topic about?"></textarea>
                    </div>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-key-variant"></i></span>
                        <span class="sword-modal-section-title">Keywords <span class="sword-modal-optional">optional</span></span>
                    </div>
                    <div class="sword-modal-section-body p-0">
                        <textarea class="form-control sword-modal-textarea" id="create-topic-keywords" rows="2" placeholder="faith, trust, belief, salvation…"></textarea>
                        <p class="sword-modal-hint">Comma-separated — used to surface related verses</p>
                    </div>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="create-topic-save">
                    <i class="mdi mdi-tag-plus me-1"></i>Save Topic
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Add Book Study Modal --}}
<div class="modal fade" id="addBookStudyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-book-open-variant"></i></div>
                    <div>
                        <h5 class="modal-title mb-0">Add Book to Study</h5>
                        <p class="sword-modal-subtitle mb-0">Choose a book of the Bible to study</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">
                <form method="POST" action="{{ route('book-studies.store') }}" id="addBookStudyForm">
                    @csrf
                    <div class="sword-modal-section mb-2">
                        <div class="sword-modal-section-header">
                            <span class="sword-modal-section-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                            <span class="sword-modal-section-title">Book <span class="sword-modal-required">required</span></span>
                        </div>
                        <div class="sword-modal-section-body">
                            <select name="book_id" id="bookStudySelect" class="form-select sword-modal-input" required>
                                <option value="">Select a book…</option>
                                <optgroup label="Old Testament">
                                    @foreach($allBooks->where('new_testament', 0) as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="New Testament">
                                    @foreach($allBooks->where('new_testament', 1) as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addBookStudyForm" class="btn sword-modal-btn-save">
                    <i class="mdi mdi-plus me-1"></i>Start Study
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.nav-link.active[data-bs-toggle="tab"] {
    color: var(--sword-navy) !important;
    border-bottom-color: var(--sword-gold) !important;
}
.book-study-card:hover {
    border-top-color: var(--sword-gold) !important;
    box-shadow: 0 4px 16px rgba(14,22,40,0.1) !important;
    transform: translateY(-2px);
}
</style>

@endsection

@push('js')
<script>
$(document).ready(function () {

    // Debug helper — writes to console and the ?debug overlay when present
    function dbg(msg, isErr) {
        var fn = isErr ? console.error : console.log;
        fn('[topics/index] ' + msg);
        var el = document.getElementById('__dbg');
        if (el) {
            var line = document.createElement('div');
            line.style.color = isErr ? '#f87171' : '#93c5fd';
            line.textContent = '[topics/index] ' + msg;
            el.appendChild(line);
            el.scrollTop = el.scrollHeight;
        }
    }

    dbg('ready — jQuery v' + ($.fn.jquery || '?'));
    dbg('bootstrap: ' + (typeof bootstrap !== 'undefined' ? 'OK' : 'MISSING'), typeof bootstrap === 'undefined');
    dbg('Swal: ' + (typeof Swal !== 'undefined' ? 'OK' : 'MISSING'));

    // Activate tab from URL hash
    var hash = window.location.hash;
    try {
        if (hash === '#books') {
            var booksTab = document.getElementById('tab-books');
            bootstrap.Tab.getOrCreateInstance(booksTab).show();
            dbg('activated #books tab');
        } else {
            var topicsTab = document.getElementById('tab-topics');
            bootstrap.Tab.getOrCreateInstance(topicsTab).show();
            dbg('activated #topics tab');
        }
    } catch (e) {
        dbg('FAILED to activate tab: ' + e.message, true);
        // Fallback: manually show the tabs without Bootstrap
        var defaultPane = document.getElementById(hash === '#books' ? 'pane-books' : 'pane-topics');
        if (defaultPane) {
            defaultPane.classList.add('active', 'show');
            dbg('applied fallback active/show to ' + defaultPane.id);
        }
    }

    // Update hash when switching tabs
    document.getElementById('tab-topics').addEventListener('shown.bs.tab', function () {
        history.replaceState(null, '', window.location.pathname + '#topics');
    });
    document.getElementById('tab-books').addEventListener('shown.bs.tab', function () {
        history.replaceState(null, '', window.location.pathname + '#books');
    });

    // Topic modal / save (unchanged)
    $('#createTopicModal').on('hidden.bs.modal', function () {
        $('#create-topic-name').val('');
        $('#create-topic-description').val('');
        $('#create-topic-keywords').val('');
    });

    $('#create-topic-save').on('click', function () {
        var name = $('#create-topic-name').val().trim();
        if (!name) {
            Swal.fire({ icon: 'warning', text: 'Name is required.' });
            return;
        }

        var btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: '{{ route('topics.store') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                description: $('#create-topic-description').val().trim(),
                keywords: $('#create-topic-keywords').val().trim(),
            },
            success: function (response) {
                window.location.href = response.redirect;
            },
            error: function () {
                Swal.fire({ icon: 'error', text: 'Error saving topic. Please try again.' });
                btn.prop('disabled', false).text('Save Topic');
            }
        });
    });

    // Select2 for Add Book Study modal
    $('#addBookStudyModal').on('shown.bs.modal', function () {
        if ($('#bookStudySelect').hasClass('select2-hidden-accessible')) {
            $('#bookStudySelect').select2('destroy');
        }
        $('#bookStudySelect').select2({
            dropdownParent: $('#addBookStudyModal'),
            placeholder: 'Select a book…',
            allowClear: true,
            width: '100%',
        });
    });

    $('#addBookStudyModal').on('hidden.bs.modal', function () {
        if ($('#bookStudySelect').hasClass('select2-hidden-accessible')) {
            $('#bookStudySelect').select2('destroy');
        }
    });
});
</script>
@endpush
