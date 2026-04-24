<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Home') | Sword</title>

	<link rel="shortcut icon" href="/images/logo.png" />
    
    <!-- Load jQuery and Bootstrap before Vite so they're immediately available -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    @vite(['resources/js/app.js', 'resources/css/sword.css'])

    @stack('css')
    <style>
    .select2-container--default .select2-selection--single {
        border: 1px solid rgba(14,22,40,0.2);
        border-radius: 6px;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        color: #212529;
        padding-left: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6b7280;
    }
    .select2-dropdown {
        border: 1px solid rgba(14,22,40,0.2);
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(14,22,40,0.1);
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: var(--sword-navy);
        color: var(--sword-gold);
    }
    .select2-results__group {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--sword-gold);
        font-weight: 700;
        padding: 8px 12px 4px;
        background: rgba(14,22,40,0.03);
        border-bottom: 1px solid rgba(201,168,76,0.15);
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid rgba(14,22,40,0.2);
        border-radius: 4px;
        padding: 4px 8px;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--sword-gold);
        outline: none;
        box-shadow: 0 0 0 0.15rem rgba(201,168,76,0.2);
    }
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--sword-gold);
        box-shadow: 0 0 0 0.15rem rgba(201,168,76,0.2);
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        color: #9ca3af;
        font-weight: 400;
        font-size: 1.1rem;
        margin-right: 4px;
    }
    </style>

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

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>
    @stack('js')
    <script>
    $(document).ready(function () {
        $('.select2-books').each(function () {
            var $sel = $(this);
            var placeholder = $sel.find('option[value=""]').first().text() || 'Select a Book';
            $sel.select2({ placeholder: placeholder, allowClear: true, width: '100%' });
        });
    });
    </script>

</body>
</html>