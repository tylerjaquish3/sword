@extends('base.layout')

@section('title', 'Profile')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-dark font-weight-bold mb-1">{{ auth()->user()->name }}</h3>
        <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-star me-2" style="color:#f59e0b;"></i>Favorite Verses</h4>
            </div>
            <div class="card-body p-0">
                @if($favorites->isEmpty())
                    <p class="text-muted p-4 mb-0">No favorite verses yet. Open a verse in the reader and click the star to mark it.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-favorites">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Text</th>
                                <th>Favorited</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($favorites as $fav)
                            <tr>
                                <td style="white-space:nowrap;">
                                    <a class="sword-link" href="{{ route('translations.index') }}?book={{ $fav['book_id'] }}">
                                        {{ $fav['reference'] }}
                                    </a>
                                </td>
                                <td class="text-muted" style="max-width:480px;">{{ Str::limit($fav['text'], 100) }}</td>
                                <td style="white-space:nowrap;">{{ $fav['favorited']->format('M j, Y') }}</td>
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

<div class="row mb-4">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-book-open-page-variant me-2"></i>Reading History</h4>
            </div>
            <div class="card-body p-0">
                @if($reads->isEmpty())
                    <p class="text-muted p-4 mb-0">No chapters marked as read yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-reads">
                        <thead class="table-light">
                            <tr>
                                <th>Book</th>
                                <th>Chapter</th>
                                <th>Translation</th>
                                <th>Last Read</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reads as $read)
                            <tr>
                                <td>
                                    <a class="sword-link" href="{{ route('translations.index') }}?book={{ $read->book_id }}&chapter={{ $read->chapter_number }}&translation={{ $read->translation_id }}">
                                        {{ $read->book->name }}
                                    </a>
                                </td>
                                <td>{{ $read->chapter_number }}</td>
                                <td>{{ $read->translation->name }}</td>
                                <td>
                                    <span title="{{ $read->read_at->format('F j, Y g:i A') }}">
                                        {{ $read->read_at->format('M j, Y') }}
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

    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-share-variant me-2"></i>Weekly Digests</h4>
            </div>
            <div class="card-body p-0">
                @if($digests->isEmpty())
                    <p class="text-muted p-4 mb-0">No digests shared yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-digests">
                        <thead class="table-light">
                            <tr>
                                <th>Week</th>
                                <th>Shared</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($digests as $digest)
                            <tr>
                                <td style="white-space:nowrap;">{{ $digest->week_start->format('M j') }}–{{ $digest->week_end->format('M j, Y') }}</td>
                                <td class="text-muted" style="white-space:nowrap;">{{ $digest->created_at->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('digest.shared.show', $digest->uuid) }}" class="sword-link" target="_blank">View</a>
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

<div class="row mb-4">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4 class="card-title mb-0"><i class="mdi mdi-comment-text-multiple-outline me-2"></i>Commentary Activity</h4>
                    <div class="btn-group" role="group" id="commentary-filter">
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active" data-days="7">Week</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-days="30">Month</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-days="90">Quarter</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-days="365">Year</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-days="0">All</button>
                    </div>
                </div>
                <p class="text-muted small mb-0 mt-1"><span id="commentary-count">{{ $commentary->count() }}</span> entries shown</p>
            </div>
            <div class="card-body p-0">
                @if($commentary->isEmpty())
                    <p class="text-muted p-4 mb-0">No commentary added yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="commentary-table">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Comment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commentary as $entry)
                            <tr data-date="{{ $entry['created_at']?->toISOString() }}">
                                <td>
                                    <span class="badge {{ $entry['type'] === 'Verse' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td>
                                    <a class="sword-link" href="{{ route('translations.index') }}?book={{ $entry['book_id'] }}">
                                        {{ $entry['reference'] }}
                                    </a>
                                </td>
                                <td class="text-muted" style="max-width: 420px;">
                                    {{ Str::limit($entry['comment'], 80) }}
                                </td>
                                <td style="white-space: nowrap;">
                                    <span title="{{ $entry['created_at']?->format('F j, Y g:i A') }}">
                                        {{ $entry['created_at']?->format('M j, Y') }}
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

<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-cog-outline me-2"></i>Preferences</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('profile.default-translation') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label for="translation_id" class="form-label fw-semibold">Default Translation</label>
                        <select name="translation_id" id="translation_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($translations as $translation)
                                <option value="{{ $translation->id }}" {{ auth()->user()->default_translation_id == $translation->id ? 'selected' : '' }}>
                                    {{ $translation->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Used as the default when opening the reader.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function () {
    var dtOpts = {
        paging: true,
        searching: false,
        lengthChange: false,
        info: false,
        ordering: false,
    };

    @if(!$favorites->isEmpty())
    $('#table-favorites').DataTable(dtOpts);
    @endif

    @if(!$reads->isEmpty())
    $('#table-reads').DataTable(dtOpts);
    @endif

    @if(!$digests->isEmpty())
    $('#table-digests').DataTable(dtOpts);
    @endif

    @if(!$commentary->isEmpty())
    var cutoffDate = null;

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'commentary-table') return true;
        if (!cutoffDate) return true;
        var $row = $(commentaryTable.row(dataIndex).node());
        return new Date($row.data('date')) >= cutoffDate;
    });

    var commentaryTable = $('#commentary-table').DataTable(dtOpts);

    commentaryTable.on('draw', function () {
        $('#commentary-count').text(commentaryTable.rows({ search: 'applied' }).count());
    });

    function applyFilter(days) {
        cutoffDate = days > 0 ? new Date(Date.now() - days * 86400000) : null;
        commentaryTable.draw();
    }

    $('.filter-btn').on('click', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        applyFilter(parseInt($(this).data('days')));
    });

    applyFilter(7);
    @endif
});
</script>
@endpush

@endsection
