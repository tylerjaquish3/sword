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

<div class="row">
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
                    <table class="table table-hover mb-0">
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
                                    <a href="{{ route('translations.index') }}?book={{ $read->book_id }}&chapter={{ $read->chapter_number }}&translation={{ $read->translation_id }}">
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
                <h4 class="card-title mb-0"><i class="mdi mdi-login me-2"></i>Login History</h4>
            </div>
            <div class="card-body p-0">
                @if($logins->isEmpty())
                    <p class="text-muted p-4 mb-0">No logins recorded yet.</p>
                @else
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logins as $login)
                            <tr>
                                <td>{{ $login->logged_in_at->format('M j, Y') }}</td>
                                <td class="text-muted">{{ $login->logged_in_at->format('g:i A') }}</td>
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

<div class="row">
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
                                    <a href="{{ route('translations.index') }}?book={{ urlencode($entry['book']) }}">
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

@push('js')
<script>
$(document).ready(function () {
    function applyFilter(days) {
        var cutoff = days > 0 ? new Date(Date.now() - days * 86400000) : null;
        var visible = 0;
        $('#commentary-table tbody tr').each(function () {
            var date = new Date($(this).data('date'));
            var show = !cutoff || date >= cutoff;
            $(this).toggle(show);
            if (show) visible++;
        });
        $('#commentary-count').text(visible);
    }

    $('.filter-btn').on('click', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        applyFilter(parseInt($(this).data('days')));
    });

    // Default to last week
    applyFilter(7);
});
</script>
@endpush

@endsection
