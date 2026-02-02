// jQuery, Bootstrap, and DataTables are loaded via CDN in the layout for immediate availability

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import moment from 'moment';

// Register the datalabels plugin
Chart.register(ChartDataLabels);

// Make Chart and moment available globally if needed
window.Chart = Chart;
window.moment = moment;

import '../css/app.css'; 