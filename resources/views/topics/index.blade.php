@extends('base.layout')

@section('title', 'Topics')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Topics</h3>
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
                <a type="button" href="{{ route('topics.create') }}" class="btn btn-outline-inverse-info btn-icon-text">
                    Create New                        
                </a>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Help                       
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Send                      
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Topics</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-topics" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Count</th>
                                <th>Topic</th>
                                <th>Books</th>
                                <th>Chapters</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topics as $topic)
                                <tr>
                                    <td>{{ $topic['count'] }}</td>
                                    <td>{{ $topic['topic'] }}</td>
                                    <td>{{ $topic['topic'] }}</td>
                                    <td>{{ $topic['topic'] }}</td>
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

@push('js')
<script>

    // setTimeout(function() {
        let table = $('#datatable-topics').DataTable({
            "order": [
                [0, "desc"],
            ]
        });

        
    // }, 2000);

</script>
@endpush