@extends('base.layout')

@section('title', 'Edit Topic: ' . $topic->name)

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">{{ $topic->name }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <a href="{{ route('topics.index') }}" type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Topics                         
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
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Topic Details</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('topics.update', $topic->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group row mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $topic->name }}">
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label for="description" class="col-sm-3 col-form-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="description" name="description" rows="4">{{ $topic->description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label for="keywords" class="col-sm-3 col-form-label">Keywords</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="keywords" name="keywords" rows="3" placeholder="Comma-separated keywords">{{ $topic->keywords }}</textarea>
                            <small class="text-muted">Enter keywords separated by commas</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('topics.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Keywords</h4>
            </div>
            <div class="card-body">
                @if($topic->keywords)
                    @foreach(explode(',', $topic->keywords) as $keyword)
                        <span class="badge bg-primary me-1 mb-1 keyword-filter" style="cursor: pointer;" data-keyword="{{ trim($keyword) }}">{{ trim($keyword) }}</span>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No keywords defined</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Matching Verses Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Verses Containing Keywords</h4>
                <p class="text-muted mb-0">{{ $matchingVerses->count() }} verses found</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-matching-verses" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Keyword</th>
                                <th>Testament</th>
                                <th>Reference</th>
                                <th>Text</th>
                                <th>Translation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($matchingVerses as $verse)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $verse->matched_keyword }}</span></td>
                                    <td><span class="badge {{ $verse->chapter->book->new_testament ? 'bg-success' : 'bg-warning' }}">{{ $verse->chapter->book->new_testament ? 'NT' : 'OT' }}</span></td>
                                    <td><a href="{{ route('translations.index') }}?translation={{ $verse->translation_id }}&book={{ $verse->chapter->book->id }}&chapter={{ $verse->chapter->number }}">{{ $verse->reference }}</a></td>
                                    <td><span class="verse-clickable" data-verse-id="{{ $verse->id }}" style="cursor: pointer;">{{ $verse->text }}</span></td>
                                    <td>{{ $verse->translation->name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('commentary.modals.verse')

@endsection

@push('js')
<script>
    $(document).ready(function() {
        var table = $('#datatable-matching-verses').DataTable({
            "order": [[0, "asc"], [2, "asc"]],
            "pageLength": 25,
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "8%", "targets": 1 },
                { "width": "12%", "targets": 2 },
                { "width": "60%", "targets": 3 },
                { "width": "10%", "targets": 4 }
            ]
        });

        // Click on keyword badge to filter the table
        $('.keyword-filter').on('click', function() {
            var keyword = $(this).data('keyword');
            table.search(keyword).draw();
        });
    });
</script>
@endpush
