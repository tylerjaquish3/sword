@extends('base.layout')

@section('title', 'Notifications')

@section('content')

<div class="row">
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="text-dark font-weight-bold mb-1">Notifications</h3>
                <p class="page-subtitle mb-0">Updates and reminders for your study</p>
            </div>
            @if($notifications->where('read_at', null)->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-check-all me-1"></i> Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-12">
        @forelse($notifications as $notif)
            <div class="card mb-2 {{ $notif->isUnread() ? 'border-start border-primary border-3' : '' }}" style="{{ $notif->isUnread() ? 'border-left: 3px solid #464dee !important;' : '' }}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="preview-icon {{ $notif->icon_color }} flex-shrink-0" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="mdi {{ $notif->icon }} text-white" style="font-size:1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 font-weight-{{ $notif->isUnread() ? 'bold' : 'normal' }}">
                                    {{ $notif->title }}
                                    @if($notif->isUnread())
                                        <span class="badge bg-primary ms-2" style="font-size:0.65rem;">New</span>
                                    @endif
                                </h6>
                                <span class="text-muted small">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mb-0 text-muted small">{{ $notif->message }}</p>
                            @if($notif->url && $notif->isUnread())
                                <form method="POST" action="{{ route('notifications.read', $notif) }}" class="mt-2 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-primary">
                                        Go <i class="mdi mdi-arrow-right"></i>
                                    </button>
                                </form>
                            @elseif($notif->url)
                                <a href="{{ $notif->url }}" class="btn btn-link btn-sm p-0 text-muted mt-1">
                                    Go <i class="mdi mdi-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="mdi mdi-bell-check text-success" style="font-size:3rem;"></i>
                    <h5 class="mt-3 text-muted">You're all caught up</h5>
                </div>
            </div>
        @endforelse

        {{ $notifications->links() }}
    </div>

    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Notification Types</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2"><i class="mdi mdi-fire text-warning me-2"></i>Reading streak milestones</li>
                    <li class="mb-2"><i class="mdi mdi-heart text-primary me-2"></i>Prayer reminders</li>
                    <li class="mb-2"><i class="mdi mdi-brain text-warning me-2"></i>Expiring memory verses</li>
                    <li class="mb-2"><i class="mdi mdi-book-open-variant text-info me-2"></i>Profile suggestions</li>
                    <li class="mb-2"><i class="mdi mdi-bullhorn text-success me-2"></i>App updates &amp; announcements</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
