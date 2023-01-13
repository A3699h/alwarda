/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import $ from "jquery";

import '../css/app.scss';
// const $ = require('jquery');

// const dt = require('datatables.net-bs4');
import 'datatables.net-bs4';

// const toggle = require('bootstrap4-toggle');
import 'bootstrap4-toggle';

require('bootstrap');

import 'chart.js';
import 'select2/dist/js/select2.full.min'
import 'bootstrap-datepicker/dist/js/bootstrap-datepicker.min';
import 'bootstrap-datetimepicker-4.17.37/bootstrap-datetimepicker.min' ;
import 'chartjs-chart-geo/build/Chart.Geo.min';
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
//
// require('bootstrap');

$(function () {
    $('.data-table').DataTable({
        order: []
    });
    $('.basic-select2').select2({
        theme: "bootstrap"
    });
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
    // $('.js-timepicker').datetimepicker({
    //     format: 'HH:mm'
    // });
});
