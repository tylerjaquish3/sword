@extends('base.layout')

@section('title', 'Edit Chapter Comment')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Edit Chapter Comment</h3>
                <h6 class="font-weight-normal mb-2">{{ $chapterComment->chapter->book->name ?? 'Unknown' }} - Chapter {{ $chapterComment->chapter->number ?? 'N/A' }}</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <a href="{{ route('commentary.index') }}" type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Commentary                         
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Chapter Comment Details</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('commentary.update-chapter', $chapterComment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group row mb-3">
                        <label for="book_id" class="col-sm-3 col-form-label">Book</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="book_id" name="book_id" onchange="updateChapters()">
                                <option value="">Select a Book</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" 
                                        data-chapters="{{ json_encode($book->chapters) }}"
                                        {{ $chapterComment->chapter->book_id == $book->id ? 'selected' : '' }}>
                                        {{ $book->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="chapter_id" class="col-sm-3 col-form-label">Chapter</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="chapter_id" name="chapter_id">
                                <option value="">Select a Chapter</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="comment" class="col-sm-3 col-form-label">Comment</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="comment" name="comment" rows="8">{{ $chapterComment->comment }}</textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('commentary.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Comment Info</h4>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Created:</strong> {{ $chapterComment->created_at->format('M d, Y h:i A') }}</p>
                <p class="mb-0"><strong>Updated:</strong> {{ $chapterComment->updated_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h4 class="card-title text-white mb-0">Danger Zone</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('commentary.destroy-chapter', $chapterComment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block w-100" onclick="return confirm('Are you sure you want to delete this comment?')">
                        <i class="mdi mdi-delete me-1"></i> Delete Comment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var currentChapterId = {{ $chapterComment->chapter_id }};

    document.addEventListener('DOMContentLoaded', function() {
        updateChapters();
    });

    function updateChapters() {
        var bookSelect = document.getElementById('book_id');
        var chapterSelect = document.getElementById('chapter_id');
        
        // Clear existing options
        chapterSelect.innerHTML = '<option value="">Select a Chapter</option>';
        
        var selectedBook = bookSelect.options[bookSelect.selectedIndex];
        if (selectedBook.value) {
            var chapters = JSON.parse(selectedBook.getAttribute('data-chapters'));
            chapters.forEach(function(chapter) {
                var option = document.createElement('option');
                option.value = chapter.id;
                option.textContent = 'Chapter ' + chapter.number;
                if (chapter.id == currentChapterId) {
                    option.selected = true;
                }
                chapterSelect.appendChild(option);
            });
        }
    }
</script>
@endsection
