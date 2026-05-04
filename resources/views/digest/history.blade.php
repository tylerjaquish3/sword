@extends('base.layout')

@section('title', 'Digest History')

@section('content')

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="mb-1" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold); font-weight: 700;">Digest History</p>
        <h3 class="mb-0 fw-bold" style="color: var(--sword-navy);">Past Weekly Digests</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('digest.weekly') }}" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
            <i class="mdi mdi-arrow-left"></i> This Week
        </a>
        <a href="{{ route('digest.complete.create') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.8rem;">
            <i class="mdi mdi-plus"></i> Complete This Week
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert mb-4 py-2" style="font-size: 0.85rem; border: 1px solid rgba(201,168,76,0.3); background: rgba(201,168,76,0.08); color: var(--sword-navy); border-radius: 6px;">
    <i class="mdi mdi-check-circle me-1" style="color: var(--sword-gold);"></i> {{ session('success') }}
</div>
@endif

@if($digests->isEmpty())
<div class="card" style="border-top: 3px solid var(--sword-gold);">
    <div class="card-body text-center py-5">
        <i class="mdi mdi-book-open-page-variant mdi-48px mb-3 d-block" style="color: rgba(201,168,76,0.3);"></i>
        <p class="mb-1 fw-bold" style="color: var(--sword-navy);">No digests saved yet</p>
        <p class="mb-3" style="font-size: 0.85rem; color: #6b7280;">Complete your first weekly digest to start building your history.</p>
        <a href="{{ route('digest.complete.create') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.8rem;">
            Complete This Week's Digest
        </a>
    </div>
</div>
@else
<div class="card" style="border-top: 3px solid var(--sword-gold);">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table mb-0" style="font-size: 0.85rem; min-width: 640px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(14,22,40,0.08);">
                    <th style="padding: 0.75rem 1.25rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Week</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Days</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Chapters</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Prayers</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Notes</th>
                    <th style="padding: 0.75rem 1rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);">Status</th>
                    <th style="padding: 0.75rem 1.25rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 700; background: rgba(14,22,40,0.02);"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($digests as $digest)
                @php $snap = $digest->snapshot ?? []; @endphp
                <tr style="border-bottom: 1px solid rgba(14,22,40,0.05);">
                    <td style="padding: 0.85rem 1.25rem; color: var(--sword-navy); font-weight: 600; white-space: nowrap;">
                        {{ $digest->week_start->format('M j') }} – {{ $digest->week_end->format('M j, Y') }}
                    </td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">{{ $snap['daysStudied'] ?? '—' }}</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">{{ $snap['totalChapters'] ?? '—' }}</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">{{ $snap['totalPrayers'] ?? '—' }}</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">{{ $snap['totalNotes'] ?? '—' }}</td>
                    <td style="padding: 0.85rem 1rem;">
                        @if($digest->is_shared)
                            <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 2px 8px; border-radius: 10px; background: rgba(201,168,76,0.12); color: var(--sword-gold);">Shared</span>
                        @else
                            <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 2px 8px; border-radius: 10px; background: rgba(14,22,40,0.06); color: #6b7280;">Saved</span>
                        @endif
                    </td>
                    <td style="padding: 0.85rem 1.25rem;">
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <a href="{{ route('digest.show', $digest) }}" class="btn btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2);">
                                View
                            </a>
                            @if($digest->is_shared)
                                <button type="button"
                                    class="btn btn-sm copy-link-btn"
                                    data-url="{{ route('digest.shared.show', $digest->uuid) }}"
                                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: transparent; color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3);">
                                    Copy Link
                                </button>
                            @else
                                <form method="POST" action="{{ route('digest.mark-shared', $digest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2);">
                                        Share
                                    </button>
                                </form>
                            @endif
                            <button type="button"
                                class="btn btn-sm delete-digest-btn"
                                data-id="{{ $digest->id }}"
                                data-url="{{ route('digest.destroy', $digest) }}"
                                data-label="{{ $digest->week_start->format('M j') }} – {{ $digest->week_end->format('M j, Y') }}"
                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: transparent; color: #dc2626; border: 1px solid rgba(220,38,38,0.25);">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script>
function copyToClipboard(text, btn) {
    var orig = btn.textContent;
    var confirm = function() {
        btn.textContent = 'Copied!';
        setTimeout(function() { btn.textContent = orig; }, 2000);
    };
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(confirm);
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        confirm();
    }
}
document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
    btn.addEventListener('click', function() { copyToClipboard(this.dataset.url, this); });
});

document.querySelectorAll('.delete-digest-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url   = this.dataset.url;
        var label = this.dataset.label;
        var row   = this.closest('tr');

        Swal.fire({
            title: 'Delete this digest?',
            html: '<span style="font-size:0.9rem;color:#6b7280;">' + label + '</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Cancel',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(function(res) {
                if (res.ok) {
                    row.style.transition = 'opacity 0.2s';
                    row.style.opacity = '0';
                    setTimeout(function() { row.remove(); }, 200);
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not delete digest.' });
                }
            });
        });
    });
});
</script>
@endpush
