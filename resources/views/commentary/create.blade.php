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
            <a href="{{ route('commentary.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Commentary
            </a>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Commentary Details</h4>
            </div>
            <div class="card-body">
                <form id="commentary-form" action="{{ route('commentary.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3 align-items-center">
                        <label for="type" class="col-sm-3 col-form-label fw-semibold">Comment Type</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="type" name="type">
                                <option value="chapter">Chapter Comment</option>
                                <option value="verse">Verse Comment</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label for="book_id" class="col-sm-3 col-form-label fw-semibold">Book</label>
                        <div class="col-sm-9">
                            <select class="form-select select2-books" id="book_id" name="book_id" required>
                                <option value="">Select a Book</option>
                                <optgroup label="Old Testament">
                                    @foreach ($books->where('new_testament', 0) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ json_encode($book->chapters) }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="New Testament">
                                    @foreach ($books->where('new_testament', 1) as $book)
                                        <option value="{{ $book->id }}" data-chapters="{{ json_encode($book->chapters) }}">{{ $book->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label for="chapter_id" class="col-sm-3 col-form-label fw-semibold">Chapter</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="chapter_id" name="chapter_id" required disabled>
                                <option value="">Select a Chapter</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center" id="verse_row" style="display: none;">
                        <label for="verse_id" class="col-sm-3 col-form-label fw-semibold">Verse</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="verse_id" name="verse_id" disabled>
                                <option value="">Select a Verse</option>
                            </select>
                            <div id="verse_preview" class="mt-2 p-3 rounded border bg-light text-muted small lh-base" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-start">
                        <label for="comment" class="col-sm-3 col-form-label fw-semibold">Comment</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="comment" name="comment" rows="6" placeholder="Enter your commentary..." required></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('commentary.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="save-btn">Save Commentary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tips</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2"><strong>Chapter Comments:</strong> Use for commentary on an entire chapter's theme or context.</p>
                <p class="text-muted mb-0"><strong>Verse Comments:</strong> Use for specific verse-by-verse analysis or notes.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {

    // Show/hide verse row based on type
    $('#type').on('change', function () {
        var isVerse = $(this).val() === 'verse';
        $('#verse_row').toggle(isVerse);
        $('#verse_id').prop('required', isVerse).prop('disabled', isVerse ? !$('#chapter_id').val() : true);
    });

    // Populate chapters when a book is selected
    $('#book_id').on('change', function () {
        var $chapterSelect = $('#chapter_id');
        var $verseSelect   = $('#verse_id');

        $chapterSelect.html('<option value="">Select a Chapter</option>').prop('disabled', true);
        $verseSelect.html('<option value="">Select a Verse</option>').prop('disabled', true);

        var chapters = $(this).find(':selected').data('chapters') || [];
        if (chapters.length) {
            chapters.forEach(function (chapter) {
                $chapterSelect.append(
                    $('<option>').val(chapter.id)
                        .text('Chapter ' + chapter.number)
                        .data('verses', chapter.verses || [])
                );
            });
            $chapterSelect.prop('disabled', false);
        }
    });

    // Populate verses when a chapter is selected
    $('#chapter_id').on('change', function () {
        var $verseSelect = $('#verse_id');
        $verseSelect.html('<option value="">Select a Verse</option>').prop('disabled', true);
        $('#verse_preview').hide().text('');

        var verses = $(this).find(':selected').data('verses') || [];
        $('#verse_preview').hide().text('');
        if (verses.length && $('#type').val() === 'verse') {
            verses.forEach(function (verse) {
                $verseSelect.append(
                    $('<option>').val(verse.id).text('Verse ' + verse.number).data('text', verse.text || '')
                );
            });
            $verseSelect.prop('disabled', false);
        }
    });

    // Show verse text below the select when a verse is chosen
    $('#verse_id').on('change', function () {
        var text = $(this).find(':selected').data('text');
        if (text) {
            $('#verse_preview').text(text).show();
        } else {
            $('#verse_preview').hide().text('');
        }
    });

    // AJAX submit
    $('#commentary-form').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#save-btn').prop('disabled', true).text('Saving…');

        $.ajax({
            url:  $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Your commentary has been saved.',
                    timer: 1800,
                    showConfirmButton: false,
                }).then(function () {
                    window.location.href = '{{ route('commentary.index') }}';
                });
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Save Commentary');
                var errors = xhr.responseJSON && xhr.responseJSON.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                    : 'Something went wrong. Please try again.';
                Swal.fire({ icon: 'error', title: 'Error', text: errors });
            }
        });
    });

});
</script>
@endpush
