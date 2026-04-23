@extends('base.layout')

@section('title', $q ? 'Search: ' . $q : 'Search')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0" style="color: var(--sword-navy); font-family: Georgia, serif; font-style: italic;">
        Search God's Word
    </h3>
</div>

<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('search.index') }}" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text" style="background: var(--sword-navy); border-color: var(--sword-navy);">
                    <i class="mdi mdi-magnify" style="color: var(--sword-gold);"></i>
                </span>
                <input
                    type="text"
                    name="q"
                    class="form-control form-control-lg"
                    placeholder="Search verses…"
                    value="{{ $q }}"
                    autofocus
                    style="border-color: rgba(14,22,40,0.2);"
                >
                <button class="btn btn-lg" type="submit" style="background: var(--sword-navy); color: var(--sword-gold); border-color: var(--sword-navy); font-weight: 600;">
                    Search
                </button>
            </div>
        </form>
    </div>
</div>

@if($q)
@php
    $groupedVerses = $verses->groupBy('reference');
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0" style="color: var(--sword-navy);">Results</h4>
                <span class="text-muted" style="font-size: 0.9rem;">
                    @if($verses->isEmpty())
                        No verses found for <strong>"{{ $q }}"</strong>
                    @elseif($verses->count() === 500)
                        500+ matches for <strong>"{{ $q }}"</strong> — showing first 500
                    @else
                        {{ $groupedVerses->count() }} {{ Str::plural('reference', $groupedVerses->count()) }} ({{ $verses->count() }} total) containing <strong>"{{ $q }}"</strong>
                    @endif
                </span>
            </div>
            <div class="card-body">
                @if($verses->isEmpty())
                    <div class="text-center py-5">
                        <i class="mdi mdi-book-search-outline mdi-48px mb-3" style="color: rgba(14,22,40,0.15);"></i>
                        <p style="color: #9ca3af;">No verses found containing "{{ $q }}"</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="datatable-search-results" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width:32px;"></th>
                                    <th>Reference</th>
                                    <th>Text</th>
                                    <th>Translation</th>
                                    <th>Testament</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedVerses as $reference => $group)
                                    @php
                                        $first       = $group->first();
                                        $hasMultiple = $group->count() > 1;
                                        $extraTranslations = $hasMultiple
                                            ? $group->skip(1)->map(fn($v) => [
                                                'translation' => $v->translation->name ?? 'N/A',
                                                'text'        => $v->text,
                                                'verse_id'    => $v->id,
                                                'url'         => route('translations.index') . '?translation=' . $v->translation_id . '&book=' . $v->chapter->book->id . '&chapter=' . $v->chapter->number,
                                            ])->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
                                            : '[]'
                                    @endphp
                                    <tr data-extra="{{ $extraTranslations }}">
                                        <td class="toggle-cell text-center" style="cursor: {{ $hasMultiple ? 'pointer' : 'default' }};">
                                            @if($hasMultiple)
                                                <span class="toggle-icon" style="color:#1a3a6b; font-size:1rem; line-height:1; user-select:none;">+</span>
                                            @endif
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a class="sword-link" href="{{ route('translations.index') }}?translation={{ $first->translation_id }}&book={{ $first->chapter->book->id }}&chapter={{ $first->chapter->number }}">
                                                {{ $reference }}
                                            </a>
                                            @if($hasMultiple)
                                                <small class="text-muted ms-1">({{ $group->count() }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $first->text }}</td>
                                        <td>{{ $first->translation->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $first->chapter->book->new_testament ? 'btn-info' : 'btn-warning' }}">
                                                {{ $first->chapter->book->new_testament ? 'NT' : 'OT' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@push('css')
<style>
    #datatable-search-results th:nth-child(3),
    #datatable-search-results td:nth-child(3) {
        min-width: 280px;
    }
    .child-translations {
        background: rgba(201, 168, 76, 0.04);
        border-top: 1px solid var(--sword-navy-border);
    }
    .child-translations table { margin-bottom: 0; }
    .child-translations td {
        border-top: 1px solid var(--sword-navy-border) !important;
        padding: 6px 12px;
        vertical-align: top;
    }
    @media (max-width: 575px) {
        #datatable-search-results_wrapper .dt-length { display: none; }
    }
</style>
@endpush

@push('js')
@if($verses->isNotEmpty())
<script>
$(document).ready(function () {
    var table = $('#datatable-search-results').DataTable({
        order: [[1, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: 0, width: '32px' },
            { width: '12%', targets: 1 },
            { width: '56%', targets: 2 },
            { width: '14%', targets: 3 },
            { width: '6%',  targets: 4 }
        ]
    });

    $('#datatable-search-results').on('click', 'td.toggle-cell', function () {
        var tr  = $(this).closest('tr');
        var row = table.row(tr);

        if (!tr.data('extra') || tr.data('extra') === '[]') return;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            tr.find('.toggle-icon').text('+');
        } else {
            var extras = tr.data('extra');
            var html = '<div class="child-translations"><table class="table table-sm mb-0"><tbody>';
            $.each(extras, function (i, t) {
                html += '<tr>'
                      + '<td style="width:32px;"></td>'
                      + '<td style="width:12%;"><a class="sword-link" href="' + t.url + '">(same ref)</a></td>'
                      + '<td style="width:56%;">' + $('<div>').text(t.text).html() + '</td>'
                      + '<td style="width:14%;">' + $('<div>').text(t.translation).html() + '</td>'
                      + '<td style="width:6%;"></td>'
                      + '</tr>';
            });
            html += '</tbody></table></div>';
            row.child(html).show();
            tr.addClass('shown');
            tr.find('.toggle-icon').text('−');
        }
    });
});
</script>
@endif
@endpush

@endsection
