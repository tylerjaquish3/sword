<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') | Sword</title>
    <link rel="shortcut icon" href="/images/logo.png" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js', 'resources/css/sword.css'])
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow w-100">
                    <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo text-center mb-4">
                                <a href="{{ route('login') }}">
                                    <img src="/images/logo.png" alt="Sword" style="height: 60px;">
                                </a>
                                <h4 class="mt-3 mb-1">Sword</h4>
                                <h6 class="font-weight-light">@yield('subtitle')</h6>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success mb-3">{{ session('status') }}</div>
                            @endif

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
