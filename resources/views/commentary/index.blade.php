@extends('base.layout')

@section('title', 'Commentary')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Commentary</h3>
                <h6 class="font-weight-normal mb-2">{{ count($chapterComments) }} chapter comments, {{ count($verseComments) }} verse comments</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <a type="button" href="{{ route('commentary.create') }}" class="btn btn-outline-inverse-info btn-icon-text">
                    Add Commentary                        
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

<!-- Chapter Comments Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Chapter Comments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-chapter-comments" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Chapter</th>
                                <th>Comment</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chapterComments as $comment)
                                <tr>
                                    <td>{{ $comment->chapter->book->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->chapter->number ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($comment->comment, 80) }}</td>
                                    <td>{{ $comment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('commentary.edit-chapter', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('commentary.destroy-chapter', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
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

<!-- Verse Comments Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Verse Comments</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-verse-comments" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Chapter</th>
                                <th>Verse</th>
                                <th>Comment</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($verseComments as $comment)
                                <tr>
                                    <td>{{ $comment->verse->chapter->book->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->verse->chapter->number ?? 'N/A' }}</td>
                                    <td>{{ $comment->verse->number ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($comment->comment, 60) }}</td>
                                    <td>{{ $comment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('commentary.edit-verse', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('commentary.destroy-verse', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
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

@section('scripts')
<script>
    $(document).ready(function() {
        $('#datatable-chapter-comments').DataTable({
            "order": [[0, "asc"], [1, "asc"]],
            "pageLength": 10
        });
        $('#datatable-verse-comments').DataTable({
            "order": [[0, "asc"], [1, "asc"], [2, "asc"]],
            "pageLength": 10
        });
    });
</script>
@endsection
