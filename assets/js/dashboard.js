const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


const map = require('../../public/json/map');
const regions = map.features;


$(function () {
    let monthData;
    let weekData;
    let yearData;
    let chartData;

    let ordersMonthData;
    let ordersWeekData;
    let ordersYearData;

    let ctx = $('#clientsChart');
    let chartConfig = {
            type: 'line',
            data: null,
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        }
    ;
    let clientsChart = new Chart(ctx, chartConfig);
    Routing.setRoutingData(routes);
    $('#loading-spinner').show();
    alert('test');
    $.post(
        Routing.generate('chart_data')
    ).then((res) => {
        monthData = generateChartData(res, 'users', 'month', 'rgba(254, 86, 120, 1)');
        weekData = generateChartData(res, 'users', 'week', 'rgba(182, 109, 255, 1)');
        yearData = generateChartData(res, 'users', 'year', 'rgba(21, 117, 193, 1)');
        chartData = weekData;

        ordersMonthData = generateMapData(res, 'month');
        ordersWeekData = generateMapData(res, 'week');
        ordersYearData = generateMapData(res, 'year');

        mapChart.data = ordersWeekData;
        mapChart.options.geo.colorScale.interpolate = 'purples';
        mapChart.update();

        clientsChart.data = weekData;
        clientsChart.update();
    }).catch((err) => {
        alert('Error occured, Please reload the page');
        console.log(err)
    }).always(() => {
        $('#loading-spinner').hide();
    });

    $('#weekChart').on('click', function () {
        clientsChart.data = weekData;
        clientsChart.update();
    });
    $('#monthChart').on('click', function () {
        clientsChart.data = monthData;
        clientsChart.update();
    });
    $('#yearChart').on('click', function () {
        clientsChart.data = yearData;
        clientsChart.update();
    });

    $('#weekOrdersChart').on('click', function () {
        mapChart.data = ordersWeekData;
        mapChart.options.geo.colorScale.interpolate = 'purples';
        mapChart.update();
    });
    $('#monthOrdersChart').on('click', function () {
        mapChart.data = ordersMonthData;
        mapChart.options.geo.colorScale.interpolate = 'reds';
        mapChart.update();
    });
    $('#yearOrdersChart').on('click', function () {
        mapChart.data = ordersYearData;
        mapChart.options.geo.colorScale.interpolate = 'blues';
        mapChart.update();
    });

    // Update mapChart data
    $('#updateMap').on('click', function () {
        mapChart.data.datasets[0].data = regions.map((d) => ({
            feature: d,
            value: Math.random() * 10,
        }))
        mapChart.update()
    })


    let mapChart = new Chart($('#mapChart'), {
        type: 'choropleth',
        data: null,
        options: {
            showOutline: false,
            showGraticule: false,
            legend: {
                display: true,
            },
            scale: {
                projection: 'equalEarth'
            },
            geo: {
                colorScale: {
                    display: false,
                    interpolate: 'purples'
                },
            },
        },
    });

});


function generateMapData(data, acc) {
    // let regionsNames = regions.map(el => el.properties.name);
    return {
        labels: regions.map((d) => d.properties.name),
        datasets: [
            {
                label: 'New orders last ' + acc,
                outline: regions,
                data: regions.map((d) => ({
                    feature: d,
                    value: data[acc]['orders'][d.properties.name] ? parseInt(data[acc]['orders'][d.properties.name]) : 0,
                })),
            },
        ],
    };
}

function generateChart(data) {
    let ctx = $('#clientsChart');
    let options = {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    };

    let clientsChart = new Chart(ctx, {
        type: 'line',
        data: data,
        options: options
    });
}

function generateChartData(data, type, acc, color) {
    return {
        labels: Object.keys(data[acc][type]),
        datasets: [{
            backgroundColor: color,
            borderColor: color,
            label: `New clients this ${acc}`,
            fill: true,
            lineTension: 0,
            data: Object.values(data[acc][type])
        }]
    };
}
