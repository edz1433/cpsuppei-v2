$(function () {
    var ticksStyle = {
        fontColor: '#495057',
        fontStyle: 'bold'
    }

    var mode = 'index';
    var intersect = true;

    var $salesChart = $('#sales-chart');
    // eslint-disable-next-line no-unused-vars
    var salesChart = new Chart($salesChart, {
        type: 'bar',
        data: {
            labels: ['Ilog', 'Cauayan', 'Sipalay', 'Hinobaan', 'Hinigaran', 'Moises', 'San Carlos', 'Victorias'],
            datasets: [
                {
                    backgroundColor: '#ffc107',
                    borderColor: '#ced4da',
                    data: [
                        // $salesChart.data('main'),
                        $salesChart.data('ilog'),
                        $salesChart.data('cauayan'),
                        $salesChart.data('siplay'),
                        $salesChart.data('hinobaan'),
                        $salesChart.data('hinigaran'),
                        $salesChart.data('moises'),
                        $salesChart.data('sancarlos'),
                        $salesChart.data('victorias'),
                    ],
                },
                {
                    backgroundColor: '#00a65a',
                    borderColor: '#ced4da',
                    data: [
                        // $salesChart.data('main-high'),
                        $salesChart.data('ilog-high'),
                        $salesChart.data('cauayan-high'),
                        $salesChart.data('siplay-high'),
                        $salesChart.data('hinobaan-high'),
                        $salesChart.data('hinigaran-high'),
                        $salesChart.data('moises-high'),
                        $salesChart.data('sancarlos-high'),
                        $salesChart.data('victorias-high'),
                    ],
                },
                {
                    backgroundColor: '#90ee90',
                    borderColor: '#ced4da',
                    data: [
                        // $salesChart.data('main-low'),
                        $salesChart.data('ilog-low'),
                        $salesChart.data('cauayan-low'),
                        $salesChart.data('siplay-low'),
                        $salesChart.data('hinobaan-low'),
                        $salesChart.data('hinigaran-low'),
                        $salesChart.data('moises-low'),
                        $salesChart.data('sancarlos-low'),
                        $salesChart.data('victorias-low'),
                    ],
                },
            ]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: mode,
                intersect: intersect,
                callbacks: {
                    title: function (tooltipItem, data) {
                        return data.labels[tooltipItem[0].index];
                    },
                    label: function (tooltipItem, data) {
                        return 'Count: ' + tooltipItem.value;
                    }
                }
            },
            hover: {
                mode: mode,
                intersect: intersect
            },
            legend: {
                display: false
            },
            scales: {
                // yAxes: [{
                //     display: true,
                //     gridLines: {
                //         display: true,
                //         lineWidth: '4px',
                //         color: 'rgba(0, 0, 0, .2)',
                //         zeroLineColor: 'transparent'
                //     },
                //     ticks: $.extend({
                //         beginAtZero: false,
                //         min: 1,
                //         // stepSize: 50 
                //     }, ticksStyle)
                // }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: true
                    },
                    ticks: ticksStyle
                }]
            }
        }
    });
});