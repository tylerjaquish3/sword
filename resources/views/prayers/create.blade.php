@extends('base.layout')

@section('title', 'Prayers')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Create Prayer Journal Entry</h3>
                <h6 class="font-weight-normal mb-2">Last entry was 23 hours ago. View details</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                <a href="{{ route('prayers.index') }}" type="button" class="btn btn-outline-inverse-info btn-icon-text">
                    Index                         
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
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <form class="forms-sample">

                    @csrf
                    <div class="form-group row">
                        <label for="date" class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="date" value="{{ $today }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="adoration" class="col-sm-3 col-form-label">Adoration</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="adoration" rows="8"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="confession" class="col-sm-3 col-form-label">Confession</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="confession" rows="8"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="thanksgiving" class="col-sm-3 col-form-label">Thanksgiving</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="thanksgiving" rows="8"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="supplication" class="col-sm-3 col-form-label">Supplication</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="supplication" rows="8"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                    <button class="btn btn-light">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>

setTimeout(function() {

    // When submitted, send form contents to controller
    $('form').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '/prayers',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                date: $('#date').val(),
                type1: $('#adoration').val(),
                type2: $('#confession').val(),
                type3: $('#thanksgiving').val(),
                type4: $('#supplication').val(),
            },
            success: function(response) {
                // redirect to prayers.index
                window.location.href = '/prayers';
            }
        });
    });

}, 2000);

</script>
@endpush