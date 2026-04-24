@extends('base.layout')

@section('title', 'Topics')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Topics</h3>
                <p class="page-subtitle mb-0">{{ count($topics) }} topics found</p>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                <button type="button" class="btn bg-white btn-icon view-toggle active" id="gridViewBtn" data-view="grid">
                    <i class="mdi mdi-view-grid text-success"></i>
                </button>
                <button type="button" class="btn bg-white btn-icon ms-2 view-toggle" id="listViewBtn" data-view="list">
                    <i class="mdi mdi-format-list-bulleted font-weight-bold text-primary"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                    Create New
                </button>
            </div>
        </div>
    </div>
</div>

@include('topics.partials.card-view')
@include('topics.partials.table-view')

<div class="modal fade" id="createTopicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Topic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="create-topic-name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="create-topic-description" rows="3"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">Keywords</label>
                    <textarea class="form-control" id="create-topic-keywords" rows="2" placeholder="Comma-separated keywords"></textarea>
                    <small class="text-muted">Enter keywords separated by commas</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="create-topic-save">Save Topic</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('#createTopicModal').on('hidden.bs.modal', function () {
        $('#create-topic-name').val('');
        $('#create-topic-description').val('');
        $('#create-topic-keywords').val('');
    });

    $('#create-topic-save').on('click', function () {
        var name = $('#create-topic-name').val().trim();
        if (!name) {
            Swal.fire({ icon: 'warning', text: 'Name is required.' });
            return;
        }

        var btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: '{{ route('topics.store') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                description: $('#create-topic-description').val().trim(),
                keywords: $('#create-topic-keywords').val().trim(),
            },
            success: function (response) {
                window.location.href = response.redirect;
            },
            error: function () {
                Swal.fire({ icon: 'error', text: 'Error saving topic. Please try again.' });
                btn.prop('disabled', false).text('Save Topic');
            }
        });
    });
});
</script>
@endpush