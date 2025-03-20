@extends('base.layout')

@section('title', 'Translation')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Translations</h3>
                <h6 class="font-weight-normal mb-2">Last login was 23 hours ago. View details</h6>
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
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Feedback
                    <i class="mdi mdi-message-outline btn-icon-append"></i>                          
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Help
                    <i class="mdi mdi-help-circle-outline btn-icon-append"></i>                          
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Print
                    <i class="mdi mdi-printer btn-icon-append"></i>                          
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">List of Translations</h4>
                </div>
                <div class="row">
                    <div class="col-4">
                        <select class="form-control" id="translation_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}">{{ $translation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-control" id="book_select">
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}">{{ $book->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-control" id="chapter_select">
                            <option value=1>1</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter_content"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Compare Translation</h4>
                </div>
                <div class="row">
                    <div class="col-4">
                        <select class="form-control" id="translation2_select">
                            @foreach ($translations as $translation)
                                <option value="{{ $translation->id }}">{{ $translation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-control" id="book2_select">
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}">{{ $book->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select class="form-control" id="chapter2_select">
                            <option value=1>1</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="chapter2_content"></div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('js')
<script>

setTimeout(function() {

    // When translation changes, update book options
    $('#translation_select').change(function() {
        translation_id = $(this).val();
        book_id = $('#book_select').val();
        console.log(translation_id);
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
            }
        });
    });
    
    // When book changes, update chapter options
    $('#book_select').change(function() {
        book_id = $(this).val();
        translation_id = $('#translation_select').val();
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
            }
        });
    });

    // When chapter changes, update verses in chapter_content
    $('#chapter_select').change(function() {
        lookupVerses();
    });

    // When translation changes, update book options
    $('#translation2_select').change(function() {
        translation_id = $(this).val();
        book_id = $('#book2_select').val();
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter2_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter2_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
            }
        });
    });
    
    // When book changes, update chapter options
    $('#book2_select').change(function() {
        book_id = $(this).val();
        translation_id = $('#translation2_select').val();
        $.ajax({
            url: '/chapters/lookup?book_id='+book_id,
            type: 'GET',
            success: function(response) {
                $('#chapter2_select').empty();
                response.forEach(function(chapter) {
                    $('#chapter2_select').append('<option value="' + chapter.number + '">' + chapter.number + '</option>');
                });
            }
        });
    });

    // When chapter changes, update verses in chapter_content
    $('#chapter2_select').change(function() {
        lookupVerses(2);
    });

    lookupVerses('');

}, 2000);

    function lookupVerses(side)
    {
        translation_id = $('#translation'+side+'_select').val();
        chapter_id = $('#chapter'+side+'_select').val();
        $.ajax({
            url: '/translations/verses?translation_id='+translation_id+'&chapter_id='+chapter_id,
            type: 'GET',
            success: function(response) {
                $('#chapter'+side+'_content').empty();
                response.forEach(function(verse) {
                    $('#chapter'+side+'_content').append('<p>'+verse.number +' '+verse.text+'</p>');
                });
            }
        });
    }
</script>
@endpush