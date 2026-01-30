@extends('base.layout')

@section('title', 'Prayers')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Prayers</h3>
                <h6 class="font-weight-normal mb-2">Last entry was 23 hours ago. View details</h6>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                    <button type="button" class="btn bg-white btn-icon">
                        <i class="mdi mdi-view-grid text-success"></i>
                </button>
                    <button type="button" class="btn bg-white btn-icon ms-2">
                        <i class="mdi mdi-format-list-bulleted font-weight-bold text-primary"></i>
                    </button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <a type="button" href="{{ route('prayers.create') }}" class="btn btn-outline-inverse-info btn-icon-text">
                    Create New                        
                </a>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Help                       
                </button>
            </div>
        </div>
    </div>
</div>

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

<!-- Send Prayer Modal -->
<div class="modal fade" id="sendPrayerModal" tabindex="-1" aria-labelledby="sendPrayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendPrayerModalLabel">Send Prayer Journal Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label for="recipientEmail" class="form-label">Recipient Email</label>
                    <input type="email" class="form-control" id="recipientEmail" placeholder="Enter email address">
                </div>
                <hr>
                <h6 class="text-muted mb-3">Preview</h6>
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title" id="previewDate"></h5>
                        <hr>
                        <div id="previewContent"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendPrayerBtn">
                    <i class="mdi mdi-send me-1"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>

// Wait for jQuery and Bootstrap to be available (loaded via Vite)
(function checkDeps() {
    if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
        initPrayerPage();
    } else {
        setTimeout(checkDeps, 50);
    }
})();

function initPrayerPage() {

    // Handle Send Prayer Modal click
    $('.btn-icon[data-bs-target="#sendPrayerModal"]').click(function(e) {
        var modal = new bootstrap.Modal(document.getElementById('sendPrayerModal'));
        var button = $(this);
        var date = button.data('date');
        var prayers = button.data('prayers');
        
        // Format the date for display
        var formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        $('#previewDate').text(formattedDate);
        
        // Build preview content
        var previewHtml = '';
        var typeNames = {1: 'Adoration', 2: 'Confession', 3: 'Thanksgiving', 4: 'Supplication'};
        prayers.forEach(function(prayer) {
            var typeName = prayer.type ? prayer.type.name : typeNames[prayer.prayer_type_id];
            previewHtml += '<div class="mb-3">';
            previewHtml += '<h6 class="text-primary"><i class="mdi mdi-circle-medium"></i> ' + typeName + '</h6>';
            previewHtml += '<p class="text-muted mb-0 ps-3">' + prayer.content + '</p>';
            previewHtml += '</div>';
        });
        
        $('#previewContent').html(previewHtml);
        $('#recipientEmail').val('');
        
        modal.show();
    });

    // Handle Send button click
    $('#sendPrayerBtn').click(function() {
        var email = $('#recipientEmail').val();
        if (!email) {
            alert('Please enter a recipient email address');
            return;
        }
        // TODO: Implement actual send functionality
        alert('Email functionality coming soon! Would send to: ' + email);
        bootstrap.Modal.getInstance(document.getElementById('sendPrayerModal')).hide();
    });

}

</script>
@endpush