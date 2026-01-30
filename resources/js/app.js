// Import dependencies from npm packages
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// Import Bootstrap (must be after jQuery is set on window)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import 'datatables.net-dt';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import moment from 'moment';

// Register the datalabels plugin
Chart.register(ChartDataLabels);

// Make Chart and moment available globally if needed
window.Chart = Chart;
window.moment = moment;

import '../css/app.css'; 