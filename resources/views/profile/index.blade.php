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
                                    <a class="sword-link" href="{{ route('translations.index') }}?book={{ $fav['book_id'] }}&chapter={{ $fav['chapter'] }}">
                                        {{ $fav['reference'] }}
                                    </a>
                                </td>
                                <td class="text-muted" style="max-width:480px;">
                                    @if($fav['verse_id'])
                                        <span class="verse-clickable" data-verse-id="{{ $fav['verse_id'] }}" style="cursor:pointer;" title="Click to view full verse">{{ Str::limit($fav['text'], 100) }}</span>
                                    @else
                                        {{ Str::limit($fav['text'], 100) }}
                                    @endif
                                </td>
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
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0"><i class="mdi mdi-book-open-page-variant me-2"></i>Weekly Digests</h4>
                <a href="{{ route('digest.history') }}" style="font-size: 0.75rem; color: var(--sword-gold); text-decoration: none;">All Digests &rarr;</a>
            </div>
            <div class="card-body p-0">
                @if($digests->isEmpty())
                    <p class="text-muted p-4 mb-0">No digests saved yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-digests">
                        <thead class="table-light">
                            <tr>
                                <th>Week</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($digests as $digest)
                            <tr>
                                <td style="white-space:nowrap;">{{ $digest->week_start->format('M j') }}–{{ $digest->week_end->format('M j, Y') }}</td>
                                <td style="white-space:nowrap;">
                                    @if($digest->is_shared)
                                        <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; padding: 2px 7px; border-radius: 10px; background: rgba(201,168,76,0.12); color: var(--sword-gold);">Shared</span>
                                    @else
                                        <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; padding: 2px 7px; border-radius: 10px; background: rgba(14,22,40,0.06); color: #6b7280;">Saved</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('digest.show', $digest) }}" class="sword-link">View</a>
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
                    <table class="table table-hover mb-0" id="commentary-table" style="min-width:700px;">
                        <thead class="table-light">
                            <tr>
                                <th style="white-space:nowrap;">Type</th>
                                <th style="white-space:nowrap;">Reference</th>
                                <th style="min-width:420px;">Comment</th>
                                <th style="white-space:nowrap;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commentary as $entry)
                            <tr data-date="{{ $entry['created_at']?->toISOString() }}">
                                <td style="white-space:nowrap;">
                                    <span class="badge {{ $entry['type'] === 'Verse' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a class="sword-link" href="{{ route('translations.index') }}?book={{ $entry['book_id'] }}">
                                        {{ $entry['reference'] }}
                                    </a>
                                </td>
                                <td class="text-muted">
                                    {{ Str::limit($entry['comment'], 180) }}
                                </td>
                                <td style="white-space:nowrap;">
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

<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-cloud-off-outline me-2"></i>Offline Mode</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <input class="form-check-input m-0" type="checkbox" id="offline_enabled"
                           {{ auth()->user()->offline_enabled ? 'checked' : '' }}>
                    <label class="fw-semibold mb-0" for="offline_enabled" style="cursor: pointer;">
                        Enable offline access
                    </label>
                </div>
                <div class="form-text">
                    Downloads Bible text (all translations) plus your comments, prayers,
                    and highlights for offline reading. New comments, prayers, highlights,
                    and digests created while offline will sync once you're back online.
                </div>
                <div id="offline-mode-status" class="small mt-2"></div>
            </div>
        </div>
    </div>
</div>

@include('commentary.modals.verse')

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

    // Loads each offline page in a hidden iframe so the service worker caches it and the
    // JS/CSS it needs — a real navigation, not a bare fetch, so sub-resource requests (each
    // page's build assets) go through the same runtime-caching the service worker already
    // does for any normal page visit. Without this, nothing would warm the offline fallback
    // until the user happened to visit each page manually.
    function warmOfflineReaderCache() {
        if (!('serviceWorker' in navigator)) {
            return;
        }
        var urls = [
            '{{ route("offline-reader.index") }}',
            '{{ route("offline-reader.prayers") }}',
            '{{ route("offline-reader.digest") }}',
            '{{ route("offline-reader.unavailable") }}',
        ];
        urls.forEach(function (url) {
            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            iframe.onload = function () {
                setTimeout(function () { iframe.remove(); }, 1000);
            };
            document.body.appendChild(iframe);
        });
    }

    $('#offline_enabled').on('change', function () {
        var enabled = $(this).is(':checked');
        var checkbox = $(this);

        function applyChange() {
            $.ajax({
                url: '{{ route("profile.offline-mode") }}',
                type: 'PATCH',
                data: { _token: '{{ csrf_token() }}', offline_enabled: enabled ? 1 : 0 },
                success: function () {
                    $('#offline-mode-status').text(enabled ? 'Offline Mode enabled.' : 'Offline Mode disabled.');
                    if (enabled && window.swordOffline) {
                        window.swordOffline.bundleSync.sync().then(warmOfflineReaderCache);
                    }
                    if (!enabled && window.swordOffline) {
                        window.swordOffline.db.clearAll();
                    }
                },
                error: function () {
                    $('#offline-mode-status').text('Could not save — please try again.');
                    checkbox.prop('checked', !enabled);
                }
            });
        }

        if (!enabled && window.swordOffline && window.swordOffline.db) {
            window.swordOffline.db.getAll('outbox').then(function (items) {
                if (items.length > 0) {
                    var ok = confirm(items.length + ' item(s) haven\'t synced yet. Turning off Offline Mode will discard them. Continue?');
                    if (ok) {
                        applyChange();
                    } else {
                        checkbox.prop('checked', true);
                    }
                } else {
                    applyChange();
                }
            });
        } else {
            applyChange();
        }
    });
});
</script>
@endpush

@endsection
