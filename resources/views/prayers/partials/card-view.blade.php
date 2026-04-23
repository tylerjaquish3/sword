<div id="card-view">
    @if($prayers->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-heart-outline mdi-48px mb-3 d-block" style="color: var(--sword-gold);"></i>
                <h5 class="text-muted mb-1">No prayers recorded yet</h5>
                <p class="text-muted small">Click <strong>Create New</strong> to add your first entry.</p>
            </div>
        </div>
    @else
    <div class="row">
        @foreach ($prayers as $date => $dayPrayers)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="card-title mb-0">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</h4>
                        </div>
                        <div class="reading-section-divider mt-0 mb-3"></div>
                        @foreach ($dayPrayers as $prayer)
                            <div class="mb-3">
                                <div class="prayer-type-header">
                                    <span class="prayer-type-dot"></span>
                                    <span class="prayer-type-label">{{ $prayer->type->name }}</span>
                                </div>
                                <p class="text-muted mb-0 ps-3" style="font-size: 0.875rem; line-height: 1.6;">{{ $prayer->content }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="mdi mdi-clock-outline me-1"></i>
                            {{ \Carbon\Carbon::parse($dayPrayers[0]->created_at)->format('g:i A') }}
                        </small>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#sendPrayerModal" data-date="{{ $date }}" data-prayers='@json($dayPrayers)'>
                                <i class="mdi mdi-email-outline"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon btn-delete-prayer" data-date="{{ $date }}">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
