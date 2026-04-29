<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Home') | Sword</title>

	<link rel="shortcut icon" href="/images/logo.png" />
    
    <!-- jQuery loaded synchronously so inline Blade scripts can call $() at parse time.
         Bootstrap, DataTables, and select2 are bundled by Vite (no CDN dependency). -->
    <script src="/js/vendor/jquery.min.js"></script>

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

    @stack('js')

    @if(request()->has('debug'))
    <div id="__dbg" style="position:fixed;top:0;left:0;right:0;background:#1a1a2e;color:#e0e0e0;font-size:12px;font-family:monospace;padding:6px 10px;z-index:2147483647;white-space:pre-wrap;max-height:60vh;overflow:auto;border-bottom:2px solid #c9a84c;">
[SWORD DEBUG] page={{ request()->path() }} env={{ app()->environment() }}</div>
    <script>
    !function(){
        var d=document.getElementById('__dbg');
        var t0=Date.now();
        function ts(){return '+'+(Date.now()-t0)+'ms';}
        function add(m,col){
            var line=document.createElement('div');
            if(col)line.style.color=col;
            line.textContent=ts()+' '+m;
            d.appendChild(line);
            d.scrollTop=d.scrollHeight;
        }
        function chk(label,val){
            add(label+': '+(val?'OK  ('+val+')':'MISSING')+'', val?'#7ec8a0':'#f87171');
        }

        // Catch all JS errors
        window.onerror=function(m,s,l,c,e){
            add('JS ERROR: '+m, '#f87171');
            add('  @ '+(s||'?')+':'+l+':'+(c||'?'), '#f87171');
            if(e&&e.stack){
                e.stack.split('\n').slice(1,4).forEach(function(ln){add('  '+ln.trim(),'#f87171');});
            }
            return false;
        };
        window.addEventListener('unhandledrejection',function(e){
            add('UNHANDLED PROMISE: '+(e.reason&&e.reason.message||String(e.reason)),'#fb923c');
        });

        // Catch script/link 404s
        window.addEventListener('error',function(e){
            if(e.target&&(e.target.tagName==='SCRIPT'||e.target.tagName==='LINK')){
                add('ASSET FAILED: '+(e.target.src||e.target.href),'#f87171');
            }
        },true);

        // At parse time — jQuery is synchronous in <head>
        chk('jQuery (parse)', typeof $!=='undefined'?('v'+($.fn&&$.fn.jquery||'?')):null);
        chk('bootstrap (parse)', typeof bootstrap!=='undefined'?'yes':null);
        chk('Swal (parse)', typeof Swal!=='undefined'?'yes':null);

        document.addEventListener('DOMContentLoaded',function(){
            add('--- DOMContentLoaded ---','#c9a84c');
            chk('jQuery', typeof $!=='undefined'?('v'+($.fn&&$.fn.jquery||'?')):null);
            chk('bootstrap', typeof bootstrap!=='undefined'?'yes':null);
            chk('Swal', typeof Swal!=='undefined'?'yes':null);
            chk('$.fn.DataTable', (typeof $!=='undefined'&&$.fn&&$.fn.DataTable)?'yes':null);
            chk('$.fn.select2', (typeof $!=='undefined'&&$.fn&&$.fn.select2)?'yes':null);

            // List all <script src> tags and their status
            add('--- script tags ---','#c9a84c');
            document.querySelectorAll('script[src]').forEach(function(s){
                add('  '+s.src.replace(window.location.origin,''));
            });

            // Detect tab panes that are invisible (topics page symptom)
            var panes=document.querySelectorAll('.tab-pane');
            if(panes.length){
                var visible=0;
                panes.forEach(function(p){
                    if(p.classList.contains('active')&&p.classList.contains('show'))visible++;
                });
                add('tab-panes: '+panes.length+' total, '+visible+' visible', visible===0?'#f87171':'#7ec8a0');
            }
        });

        window.addEventListener('load',function(){
            add('--- window.load ---','#c9a84c');
            chk('bootstrap', typeof bootstrap!=='undefined'?'yes':null);
        });
    }();
    </script>
    @endif

</body>
</html>