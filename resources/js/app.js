import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'datatables.net-dt';
import select2 from 'select2';
import Swal from 'sweetalert2';

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import moment from 'moment';

// Make libraries globally available for inline Blade scripts.
// window.$ override ensures dev-mode module jQuery and prod-mode vendor jQuery are the same instance.
window.$ = window.jQuery = $;
select2(window, $); // attach $.fn.select2 to this jQuery instance
window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;
window.moment = moment;

Chart.register(ChartDataLabels);

// Initialise select2 immediately — module scripts run after DOM is parsed but before
// DOMContentLoaded fires, so elements exist and this runs before any jQuery ready callbacks.
function initSelect2() {
    $('.select2-books').each(function () {
        const $sel = $(this);
        const placeholder = $sel.find('option[value=""]').first().text() || 'Select a Book';
        $sel.select2({ placeholder, allowClear: true, width: '100%' });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelect2);
} else {
    initSelect2();
}

// CSRF header for all jQuery AJAX requests
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
});

import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'select2/dist/css/select2.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/app.css'; 