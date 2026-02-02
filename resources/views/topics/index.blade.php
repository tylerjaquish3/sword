@extends('base.layout')

@section('title', 'Topics')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">All Topics</h3>
                <h6 class="font-weight-normal mb-2">{{ count($topics) }} topics found</h6>
            </div>
            <div class="ms-lg-5 d-lg-flex d-none">
                <button type="button" class="btn bg-white btn-icon view-toggle active" id="gridViewBtn" data-view="grid">
                    <i class="mdi mdi-view-grid text-success"></i>
                </button>
                <button type="button" class="btn bg-white btn-icon ms-2 view-toggle" id="listViewBtn" data-view="list">
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
        </div>
    </div>
</div>

@include('topics.partials.card-view')
@include('topics.partials.table-view')

@endsection