@extends('base.layout')

@section('title', 'Admin — Formatting')

@section('content')

<div class="row mb-3">
    <div class="col">
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">Admin</h3>
    </div>
</div>

@include('admin.partials.tabs', ['active' => 'formatting'])

<p class="text-muted mb-4">
    Track which books and chapters have line breaks and section headers. A chapter counts as having line breaks
    or headers when at least one of its verses does.
</p>

<div class="row g-3 mb-4">
    @foreach([
        ['Books fully formatted', $summary['complete'] . ' / ' . $summary['books'], '#059669'],
        ['Partially formatted', $summary['partial'], '#d97706'],
        ['Not started', $summary['none'], '#dc2626'],
        ['Chapters with line breaks', $summary['chapters_with_breaks'] . ' / ' . $summary['chapters'], 'var(--sword-navy)'],
        ['Chapters with headers', $summary['chapters_with_headers'] . ' / ' . $summary['chapters'], 'var(--sword-navy)'],
    ] as [$label, $value, $color])
        <div class="col-6 col-md">
            <div class="card h-100" style="border-top: 3px solid var(--sword-gold);">
                <div class="card-body py-3">
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em;">{{ $label }}</div>
                    <div class="fw-bold" style="font-size: 1.4rem; color: {{ $color }};">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="btn-group btn-group-sm" role="group" id="formatting-filter">
        <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
        <button type="button" class="btn btn-outline-secondary" data-filter="none">Not started</button>
        <button type="button" class="btn btn-outline-secondary" data-filter="partial">Partial</button>
        <button type="button" class="btn btn-outline-secondary" data-filter="complete">Complete</button>
    </div>
    <div class="ms-auto d-flex flex-wrap gap-3 text-muted" style="font-size: 0.78rem;">
        <span><span class="badge bg-success">&nbsp;</span> line breaks + headers</span>
        <span><span class="badge bg-warning">&nbsp;</span> line breaks only</span>
        <span><span class="badge bg-info">&nbsp;</span> headers only</span>
        <span><span class="badge bg-secondary">&nbsp;</span> neither</span>
    </div>
</div>

@php
    $stateStyles = [
        'complete' => ['Complete', 'rgba(16,185,129,0.12)', '#059669'],
        'partial' => ['Partial', 'rgba(245,158,11,0.12)', '#d97706'],
        'none' => ['Not started', 'rgba(220,38,38,0.1)', '#dc2626'],
    ];
@endphp

@foreach(['Old Testament' => false, 'New Testament' => true] as $testament => $isNt)
    <h5 class="mt-4 mb-2" style="color: var(--sword-navy);">{{ $testament }}</h5>
    <div class="card mb-3" style="border-top: 3px solid var(--sword-gold);">
        <div class="list-group list-group-flush">
            @foreach($books->where('new_testament', $isNt) as $i => $book)
                @php [$stateLabel, $stateBg, $stateColor] = $stateStyles[$book['state']]; $id = 'fmt-' . ($isNt ? 'nt' : 'ot') . '-' . $loop->index; @endphp
                <div class="list-group-item p-0 formatting-book" data-state="{{ $book['state'] }}">
                    <a class="d-flex align-items-center flex-wrap gap-2 px-3 py-3 text-decoration-none" data-bs-toggle="collapse" href="#{{ $id }}" role="button" style="color: inherit;">
                        <i class="mdi mdi-chevron-right"></i>
                        <span class="fw-semibold" style="color: var(--sword-navy); min-width: 9rem;">{{ $book['name'] }}</span>
                        <span class="badge" style="background: {{ $stateBg }}; color: {{ $stateColor }}; font-size: 0.72rem; padding: 5px 10px;">{{ $stateLabel }}</span>
                        <span class="ms-auto text-muted" style="font-size: 0.82rem;">
                            Breaks {{ $book['with_breaks'] }}/{{ $book['total'] }}
                            &middot; Headers {{ $book['with_headers'] }}/{{ $book['total'] }}
                        </span>
                    </a>
                    <div class="collapse" id="{{ $id }}">
                        <div class="px-3 pb-3 d-flex flex-wrap gap-1">
                            @foreach($book['chapters'] as $chapter)
                                @php
                                    $hasB = $chapter['breaks'] > 0;
                                    $hasH = $chapter['headers'] > 0;
                                    $cls = $hasB && $hasH ? 'bg-success' : ($hasB ? 'bg-warning text-dark' : ($hasH ? 'bg-info text-dark' : 'bg-secondary'));
                                @endphp
                                <span class="badge {{ $cls }}" style="min-width: 2.4rem; font-size: 0.8rem;"
                                    title="{{ $book['name'] }} {{ $chapter['number'] }}: {{ $chapter['breaks'] }} line break(s), {{ $chapter['headers'] }} header(s) across {{ $chapter['verses'] }} verses">{{ $chapter['number'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

@endsection

@push('js')
<script>
    document.querySelectorAll('#formatting-filter button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#formatting-filter button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            var f = btn.dataset.filter;
            document.querySelectorAll('.formatting-book').forEach(function (el) {
                el.style.display = (f === 'all' || el.dataset.state === f) ? '' : 'none';
            });
        });
    });
</script>
@endpush
