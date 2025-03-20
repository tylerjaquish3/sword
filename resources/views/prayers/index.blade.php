@extends('base.layout')

@section('title', 'Prayers')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Prayers</h3>
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
                <a type="button" href="{{ route('prayers.create') }}" class="btn btn-outline-inverse-info btn-icon-text">
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
    @foreach ($prayers as $date => $prayers)
        <div class="col-sm-3 grid-margin grid-margin-md-0 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center justify-content-between mb-4">
                        <h4 class="card-title">{{ $date }}</h4>
                        <p class="text-dark">{{ optional($prayers[0])->created_at }}</p>
                    </div>
                    <div class="product-order-wrap padding-reduced">
                        @foreach ($prayers as $prayer)
                            <div class="gauge">
                                {{ $prayer->type->name }}: 
                                {{ $prayer->content }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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

    lookupVerses();

}, 2000);

    function lookupVerses()
    {
        translation_id = $('#translation_select').val();
        chapter_id = $('#chapter_select').val();
        $.ajax({
            url: '/translations/verses?translation_id='+translation_id+'&chapter_id='+chapter_id,
            type: 'GET',
            success: function(response) {
                $('#chapter_content').empty();
                response.forEach(function(verse) {
                    $('#chapter_content').append('<p>'+verse.number +' '+verse.text+'</p>');
                });
            }
        });
    }
</script>
@endpush