@extends('base.layout')

@section('title', 'Prayers')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Prayers</h3>
                <p class="page-subtitle mb-0">
                    @if($lastPrayer)
                        Last entry {{ $lastPrayer->created_at->diffForHumans() }}
                    @else
                        No entries yet
                    @endif
                </p>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                    <button type="button" class="btn bg-white btn-icon view-toggle active" id="card-view-btn" data-view="card">
                        <i class="mdi mdi-view-grid text-success"></i>
                </button>
                    <button type="button" class="btn bg-white btn-icon ms-2 view-toggle" id="table-view-btn" data-view="table">
                        <i class="mdi mdi-format-list-bulleted font-weight-bold text-primary"></i>
                    </button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text" data-bs-toggle="modal" data-bs-target="#createPrayerModal">
                    Create New
                </button>
            </div>
        </div>
    </div>
</div>

@include('prayers.partials.card-view')
@include('prayers.partials.table-view')

<!-- Create Prayer Modal -->
<div class="modal fade" id="createPrayerModal" tabindex="-1" aria-labelledby="createPrayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPrayerModalLabel">New Prayer Journal Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row mb-3">
                    <label for="prayer-date" class="col-sm-3 col-form-label">Date</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="prayer-date" value="{{ $today }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label for="prayer-adoration" class="col-sm-3 col-form-label">Adoration</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="prayer-adoration" rows="4"></textarea>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label for="prayer-confession" class="col-sm-3 col-form-label">Confession</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="prayer-confession" rows="4"></textarea>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label for="prayer-thanksgiving" class="col-sm-3 col-form-label">Thanksgiving</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="prayer-thanksgiving" rows="4"></textarea>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label for="prayer-supplication" class="col-sm-3 col-form-label">Supplication</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="prayer-supplication" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-prayer">Save Entry</button>
            </div>
        </div>
    </div>
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

    // Save new prayer entry
    $('#btn-save-prayer').on('click', function () {
        $.ajax({
            url: '/prayers',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                date:  $('#prayer-date').val(),
                type1: $('#prayer-adoration').val(),
                type2: $('#prayer-confession').val(),
                type3: $('#prayer-thanksgiving').val(),
                type4: $('#prayer-supplication').val(),
            },
            success: function () {
                window.location.reload();
            }
        });
    });

    // View toggle functionality
    $('.view-toggle').click(function() {
        var view = $(this).data('view');
        
        // Update button states
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        // Toggle views
        if (view === 'card') {
            $('#card-view').show();
            $('#table-view').hide();
        } else {
            $('#card-view').hide();
            $('#table-view').show();
        }
    });

    // Delete prayer entry by date
    $(document).on('click', '.btn-delete-prayer', function() {
        var date = $(this).data('date');
        if (!confirm('Delete all prayers for this date?')) return;

        $.ajax({
            url: '/prayers/date',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}', date: date },
            success: function() {
                window.location.reload();
            }
        });
    });

    // Handle Send Prayer Modal click
    $(document).on('click', '.btn-icon[data-bs-target="#sendPrayerModal"]', function(e) {
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

</script>
@endpush
