<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>@yield('title', 'Sword of the Spirit')</title>

	<link rel="shortcut icon" href="images/favicon.png" />
    @vite('resources/js/app.js')

</head>
<body>
    <div class="container-scroller">
		
        @include('base.navbar')

        <div class="container-fluid page-body-wrapper">
			<div class="main-panel">
				<div class="content-wrapper">
                    @yield('content')

                    {{-- @include('base.footer') --}}
                </div>
            </div>
        </div>

    </div>

    <script src="{{ asset('js/all.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.js') }}"></script>

    @stack('js')

</body>
</html>