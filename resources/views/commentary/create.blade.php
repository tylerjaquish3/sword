@extends('base.layout')

@section('title', 'Add Commentary')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Add Commentary</h3>
                <h6 class="font-weight-normal mb-2">Create a new chapter or verse comment</h6>
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
                <h4 class="card-title">Commentary Details</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('commentary.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group row mb-3">
                        <label for="type" class="col-sm-3 col-form-label">Comment Type</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="type" name="type" onchange="toggleCommentType()">
                                <option value="chapter">Chapter Comment</option>
                                <option value="verse">Verse Comment</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="book_id" class="col-sm-3 col-form-label">Book</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="book_id" name="book_id" onchange="updateChapters()">
                                <option value="">Select a Book</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" data-chapters="{{ json_encode($book->chapters) }}">{{ $book->name }}</option>
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

                    <div class="form-group row mb-3" id="verse_row" style="display: none;">
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
                            <textarea class="form-control" id="comment" name="comment" rows="6" placeholder="Enter your commentary..."></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('commentary.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Commentary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tips</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2"><strong>Chapter Comments:</strong> Use for commentary on an entire chapter's theme or context.</p>
                <p class="text-muted mb-0"><strong>Verse Comments:</strong> Use for specific verse-by-verse analysis or notes.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Store books data for JavaScript
    var booksData = @json($books);

    function toggleCommentType() {
        var type = document.getElementById('type').value;
        var verseRow = document.getElementById('verse_row');
        
        if (type === 'verse') {
            verseRow.style.display = 'flex';
        } else {
            verseRow.style.display = 'none';
        }
    }

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
                chapterSelect.appendChild(option);
            });
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
                verseSelect.appendChild(option);
            });
        }
    }
</script>
@endsection
