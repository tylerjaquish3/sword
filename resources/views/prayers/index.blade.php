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
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="modal-header sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-hands-pray"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="createPrayerModalLabel">Prayer Journal</h5>
                        <p class="sword-modal-subtitle mb-0">A.C.T.S. — Adoration · Confession · Thanksgiving · Supplication</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">

                <div class="mb-4">
                    <label for="prayer-date" class="sword-modal-label">
                        <i class="mdi mdi-calendar-outline me-1"></i> Date
                    </label>
                    <input type="text" class="form-control sword-modal-input" style="max-width:220px;" id="prayer-date" value="{{ $today }}">
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-letter">A</span>
                        <div>
                            <span class="sword-modal-section-title">Adoration</span>
                            <span class="sword-modal-section-desc">Praise God for who He is</span>
                        </div>
                    </div>
                    <textarea class="sword-modal-textarea" id="prayer-adoration" rows="3" placeholder="Lord, I praise you for…"></textarea>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-letter">C</span>
                        <div>
                            <span class="sword-modal-section-title">Confession</span>
                            <span class="sword-modal-section-desc">Acknowledge and repent of sin</span>
                        </div>
                    </div>
                    <textarea class="sword-modal-textarea" id="prayer-confession" rows="3" placeholder="Father, forgive me for…"></textarea>
                </div>

                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-letter">T</span>
                        <div>
                            <span class="sword-modal-section-title">Thanksgiving</span>
                            <span class="sword-modal-section-desc">Thank God for His blessings</span>
                        </div>
                    </div>
                    <textarea class="sword-modal-textarea" id="prayer-thanksgiving" rows="3" placeholder="Thank you, Lord, for…"></textarea>
                </div>

                <div class="sword-modal-section mb-2">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-letter">S</span>
                        <div>
                            <span class="sword-modal-section-title">Supplication</span>
                            <span class="sword-modal-section-desc">Bring your requests before God</span>
                        </div>
                    </div>
                    <textarea class="sword-modal-textarea" id="prayer-supplication" rows="3" placeholder="Lord, I ask for…"></textarea>
                </div>

            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="btn-save-prayer">
                    <i class="mdi mdi-content-save-outline me-1"></i> Save Entry
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Send Prayer Modal -->
<div class="modal fade" id="sendPrayerModal" tabindex="-1" aria-labelledby="sendPrayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content sword-modal">

            <div class="modal-header sword-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="sword-modal-icon"><i class="mdi mdi-email-send-outline"></i></div>
                    <div>
                        <h5 class="modal-title mb-0" id="sendPrayerModalLabel">Send Prayer Entry</h5>
                        <p class="sword-modal-subtitle mb-0">Share this journal entry by email</p>
                    </div>
                </div>
                <button type="button" class="sword-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="modal-body sword-modal-body">
                <div class="sword-modal-section mb-4">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-email-outline"></i></span>
                        <span class="sword-modal-section-title">Recipient</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <label for="recipientEmail" class="sword-modal-label">Email Address</label>
                        <input type="email" class="form-control sword-modal-input" id="recipientEmail" placeholder="Enter email address">
                    </div>
                </div>

                <div class="sword-modal-section">
                    <div class="sword-modal-section-header">
                        <span class="sword-modal-section-icon"><i class="mdi mdi-eye-outline"></i></span>
                        <span class="sword-modal-section-title">Preview</span>
                    </div>
                    <div class="sword-modal-section-body">
                        <p class="fw-semibold mb-2" id="previewDate" style="color:var(--sword-navy);"></p>
                        <div id="previewContent" class="sword-modal-preview"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer sword-modal-footer">
                <button type="button" class="btn sword-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn sword-modal-btn-save" id="sendPrayerBtn">
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
            Swal.fire({ icon: 'warning', text: 'Please enter a recipient email address' });
            return;
        }
        // TODO: Implement actual send functionality
        Swal.fire({ icon: 'info', text: 'Email functionality coming soon! Would send to: ' + email });
        bootstrap.Modal.getInstance(document.getElementById('sendPrayerModal')).hide();
    });

</script>
@endpush
