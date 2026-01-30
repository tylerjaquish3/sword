@extends('base.layout')

@section('title', 'Topics')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Topics</h3>
                <h6 class="font-weight-normal mb-2">{{ count($topics) }} topics found</h6>
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
                <a type="button" href="{{ route('topics.create') }}" class="btn btn-outline-inverse-info btn-icon-text">
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

<!-- Card/Grid View (Default) -->
<div id="gridView" class="row">
    @foreach ($topics as $topic)
        <div class="col-lg-4 col-xl-3 col-md-6 mb-4">
            <a href="{{ route('topics.edit', $topic->id) }}" class="text-decoration-none">
                <div class="card h-100 topic-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-dark mb-3">{{ $topic->name }}</h5>
                            <p class="text-muted small mb-0">{{ Str::limit($topic->description, 80) }}</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-secondary">{{ $topic->keywords ? count(explode(',', $topic->keywords)) : 0 }} keywords</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Datatable/List View (Hidden by default) -->
<div id="listView" class="row" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Topics</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-topics" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Description</th>
                                <th>Keywords</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topics as $topic)
                                <tr class="topic-row" data-href="{{ route('topics.edit', $topic->id) }}" style="cursor: pointer;">
                                    <td>{{ $topic->name }}</td>
                                    <td>{{ Str::limit($topic->description, 50) }}</td>
                                    <td>{{ Str::limit($topic->keywords, 40) }}</td>
                                    <td>
                                        <a href="{{ route('topics.edit', $topic->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .topic-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    .topic-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-color: #6c7ae0;
    }
    .view-toggle.active {
        background-color: #e9ecef !important;
    }
    .topic-row:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('js')
<script>

(function checkDeps() {
    if (typeof $ !== 'undefined') {
        initTopicsPage();
    } else {
        setTimeout(checkDeps, 50);
    }
})();

function initTopicsPage() {
    let table = null;

    // View toggle functionality
    $('.view-toggle').click(function() {
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        var view = $(this).data('view');
        
        if (view === 'grid') {
            $('#listView').hide();
            $('#gridView').show();
        } else {
            $('#gridView').hide();
            $('#listView').show();
            
            // Initialize DataTable only when list view is shown (and only once)
            if (!table) {
                table = $('#datatable-topics').DataTable({
                    "order": [
                        [0, "desc"],
                    ],
                    "pageLength": 25
                });
            }
        }
    });

    // Make table rows clickable
    $(document).on('click', '.topic-row', function(e) {
        // Don't navigate if clicking on a link/button
        if ($(e.target).closest('a, button').length === 0) {
            window.location.href = $(this).data('href');
        }
    });
}

</script>
@endpush