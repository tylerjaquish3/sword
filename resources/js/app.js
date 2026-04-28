import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'datatables.net-dt';
import 'select2';
import Swal from 'sweetalert2';

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import moment from 'moment';

// Make libraries globally available for inline Blade scripts.
// window.$ override ensures dev-mode module jQuery and prod-mode vendor jQuery are the same instance.
window.$ = window.jQuery = $;
window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;
window.moment = moment;

Chart.register(ChartDataLabels);

// CSRF header for all jQuery AJAX requests
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
});

import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'select2/dist/css/select2.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/app.css'; 