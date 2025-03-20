@extends('base.layout')

@section('title', 'Home')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Hi, welcome back!</h3>
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
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="line-chart-row-title">Eph. 6:17</div>
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-secondary font-weight-bold">
                        Take the helmet of salvation and the sword of the Spirit, which is the word of God.
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-danger font-weight-bold">839</h2>
                </div>
            </div>
            <div class="line-chart-row-title">All Prayer Entries</div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-info font-weight-bold">244</h2>
                    <i class="mdi mdi-file-document-outline mdi-18px text-dark"></i>
                </div>
            </div>
            <div class="line-chart-row-title">Commentary Entries</div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-warning font-weight-bold">259</h2>
                    <i class="mdi mdi-folder-outline mdi-18px text-dark"></i>
                </div>
            </div>
            <div class="line-chart-row-title">Verses Memorized</div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="text-success font-weight-bold">58</h2>
                    <i class="mdi mdi-cart-outline mdi-18px text-dark"></i>
                </div>
            </div>
            <div class="line-chart-row-title">Topics Studied</div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body pb-0">
                <a class="navbar-brand brand-logo" href="index.html"><img src="/bible-sword.png" alt="logo"/></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Support Tracker</h4>
                    <h4 class="text-success font-weight-bold">Tickets<span class="text-dark ms-3">163</span></h4>
                </div>
                <div id="support-tracker-legend" class="support-tracker-legend"></div>
                <canvas id="supportTracker"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title">Product Orders</h4>
                    <p class="text-dark">+5.2% vs last 7 days</p>
                </div>
                <div class="product-order-wrap padding-reduced">
                    <div id="productorder-gage" class="gauge productorder-gage"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
