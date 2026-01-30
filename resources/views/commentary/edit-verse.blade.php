@extends('base.layout')

@section('title', 'Edit Verse Comment')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Edit Verse Comment</h3>
                <h6 class="font-weight-normal mb-2">{{ $verseComment->verse->chapter->book->name ?? 'Unknown' }} {{ $verseComment->verse->chapter->number ?? '' }}:{{ $verseComment->verse->number ?? 'N/A' }}</h6>
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
                <h4 class="card-title">Verse Comment Details</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('commentary.update-verse', $verseComment->id) }}" method="POST">
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
                                        {{ $verseComment->verse->chapter->book_id == $book->id ? 'selected' : '' }}>
                                        {{ $book->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="chapter_id" class="col-sm-3 col-form-label">Chapter</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="chapter_id" name="chapter_id" onchange="updateVerses()">
                                <option value="">Select a Chapter</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="verse_id" class="col-sm-3 col-form-label">Verse</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="verse_id" name="verse_id">
                                <option value="">Select a Verse</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="comment" class="col-sm-3 col-form-label">Comment</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="comment" name="comment" rows="8">{{ $verseComment->comment }}</textarea>
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
                <h4 class="card-title">Verse Text</h4>
            </div>
            <div class="card-body">
                <p class="text-muted fst-italic">{{ $verseComment->verse->text ?? 'Verse text not available' }}</p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title">Comment Info</h4>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Created:</strong> {{ $verseComment->created_at->format('M d, Y h:i A') }}</p>
                <p class="mb-0"><strong>Updated:</strong> {{ $verseComment->updated_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h4 class="card-title text-white mb-0">Danger Zone</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('commentary.destroy-verse', $verseComment->id) }}" method="POST">
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
    var booksData = @json($books);
    var currentChapterId = {{ $verseComment->verse->chapter_id }};
    var currentVerseId = {{ $verseComment->verse_id }};

    document.addEventListener('DOMContentLoaded', function() {
        updateChapters();
    });

    function updateChapters() {
        var bookSelect = document.getElementById('book_id');
        var chapterSelect = document.getElementById('chapter_id');
        var verseSelect = document.getElementById('verse_id');
        
        // Clear existing options
        chapterSelect.innerHTML = '<option value="">Select a Chapter</option>';
        verseSelect.innerHTML = '<option value="">Select a Verse</option>';
        
        var selectedBook = bookSelect.options[bookSelect.selectedIndex];
        if (selectedBook.value) {
            var chapters = JSON.parse(selectedBook.getAttribute('data-chapters'));
            chapters.forEach(function(chapter) {
                var option = document.createElement('option');
                option.value = chapter.id;
                option.textContent = 'Chapter ' + chapter.number;
                option.setAttribute('data-verses', JSON.stringify(chapter.verses || []));
                if (chapter.id == currentChapterId) {
                    option.selected = true;
                }
                chapterSelect.appendChild(option);
            });
            // Update verses after setting chapters
            updateVerses();
        }
    }

    function updateVerses() {
        var chapterSelect = document.getElementById('chapter_id');
        var verseSelect = document.getElementById('verse_id');
        
        // Clear existing options
        verseSelect.innerHTML = '<option value="">Select a Verse</option>';
        
        var selectedChapter = chapterSelect.options[chapterSelect.selectedIndex];
        if (selectedChapter.value) {
            var verses = JSON.parse(selectedChapter.getAttribute('data-verses') || '[]');
            verses.forEach(function(verse) {
                var option = document.createElement('option');
                option.value = verse.id;
                option.textContent = 'Verse ' + verse.number;
                if (verse.id == currentVerseId) {
                    option.selected = true;
                }
                verseSelect.appendChild(option);
            });
        }
    }
</script>
@endsection
