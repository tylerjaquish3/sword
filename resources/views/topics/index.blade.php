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
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">66</span>
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

        @php
            $otBooks = $books->where('new_testament', 0);
            $ntBooks = $books->where('new_testament', 1);
        @endphp

        {{-- Old Testament --}}
        <div class="mb-4">
            <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--sword-gold);" class="mb-3">
                Old Testament &mdash; {{ $otBooks->count() }} Books
            </p>
            <div class="row g-2">
                @foreach($otBooks as $book)
                    @php
                        $read = $chaptersReadByBook->get($book->id, 0);
                        $total = $book->chapters_count;
                        $pct = $total > 0 ? round($read / $total * 100) : 0;
                        $hasStudy = $book->author || $book->description || $book->history || $book->themes || $book->notes;
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                        <a href="{{ route('books.study', $book) }}"
                           class="card h-100 text-decoration-none book-study-card"
                           style="transition: border-color 0.18s, box-shadow 0.18s, transform 0.15s; border-top: 2px solid {{ $hasStudy ? 'var(--sword-gold)' : 'transparent' }};">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start justify-content-between mb-1">
                                    <span style="font-size: 0.62rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">{{ $book->abbr }}</span>
                                    @if($hasStudy)
                                        <i class="mdi mdi-pencil-circle" style="font-size: 0.8rem; color: var(--sword-gold);"></i>
                                    @endif
                                </div>
                                <p class="mb-2 fw-bold" style="font-size: 0.85rem; color: var(--sword-navy); line-height: 1.3;">{{ $book->name }}</p>
                                <div class="progress mb-1" style="height: 3px; background: rgba(14,22,40,0.08);">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: var(--sword-gold);"></div>
                                </div>
                                <p class="mb-0" style="font-size: 0.65rem; color: #9ca3af;">{{ $read }}/{{ $total }} ch</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- New Testament --}}
        <div>
            <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--sword-gold);" class="mb-3">
                New Testament &mdash; {{ $ntBooks->count() }} Books
            </p>
            <div class="row g-2">
                @foreach($ntBooks as $book)
                    @php
                        $read = $chaptersReadByBook->get($book->id, 0);
                        $total = $book->chapters_count;
                        $pct = $total > 0 ? round($read / $total * 100) : 0;
                        $hasStudy = $book->author || $book->description || $book->history || $book->themes || $book->notes;
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                        <a href="{{ route('books.study', $book) }}"
                           class="card h-100 text-decoration-none book-study-card"
                           style="transition: border-color 0.18s, box-shadow 0.18s, transform 0.15s; border-top: 2px solid {{ $hasStudy ? 'var(--sword-gold)' : 'transparent' }};">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start justify-content-between mb-1">
                                    <span style="font-size: 0.62rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">{{ $book->abbr }}</span>
                                    @if($hasStudy)
                                        <i class="mdi mdi-pencil-circle" style="font-size: 0.8rem; color: var(--sword-gold);"></i>
                                    @endif
                                </div>
                                <p class="mb-2 fw-bold" style="font-size: 0.85rem; color: var(--sword-navy); line-height: 1.3;">{{ $book->name }}</p>
                                <div class="progress mb-1" style="height: 3px; background: rgba(14,22,40,0.08);">
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: var(--sword-gold);"></div>
                                </div>
                                <p class="mb-0" style="font-size: 0.65rem; color: #9ca3af;">{{ $read }}/{{ $total }} ch</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

{{-- Create Topic Modal (unchanged) --}}
<div class="modal fade" id="createTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Topic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="create-topic-name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="create-topic-description" rows="3"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">Keywords</label>
                    <textarea class="form-control" id="create-topic-keywords" rows="2" placeholder="Comma-separated keywords"></textarea>
                    <small class="text-muted">Enter keywords separated by commas</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="create-topic-save">Save Topic</button>
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

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // Activate tab from URL hash
    var hash = window.location.hash;
    if (hash === '#books') {
        var booksTab = document.getElementById('tab-books');
        bootstrap.Tab.getOrCreateInstance(booksTab).show();
    } else {
        var topicsTab = document.getElementById('tab-topics');
        bootstrap.Tab.getOrCreateInstance(topicsTab).show();
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
});
</script>
@endpush
