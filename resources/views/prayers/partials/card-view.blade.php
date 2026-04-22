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
                        <hr class="mt-0">
                        @foreach ($dayPrayers as $prayer)
                            <div class="mb-3">
                                <h6 class="text-primary mb-2">
                                    <i class="mdi mdi-circle-medium"></i>
                                    {{ $prayer->type->name }}
                                </h6>
                                <p class="text-muted mb-0 ps-3">{{ $prayer->content }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="mdi mdi-clock-outline me-1"></i>
                            {{ \Carbon\Carbon::parse($dayPrayers[0]->created_at)->format('g:i A') }}
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#sendPrayerModal" data-date="{{ $date }}" data-prayers='@json($dayPrayers)'>
                            <i class="mdi mdi-email-outline"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
