$(function () {

    var ticksStyle = {
        fontColor: '#495057',
        fontStyle: 'bold'
    }

    var mode = 'index';
    var intersect = true;

    var $salesChartMain = $('#sales-chartMain');
    // eslint-disable-next-line no-unused-vars
    var salesChartMain = new Chart($salesChartMain, {
        type: 'bar',
        data: {
            labels: ['Main'],
            datasets: [
                {
                    backgroundColor: '#ffc107',
                    borderColor: '#ced4da',
                    data: [
                        $salesChartMain.data('main'),
                    ],
                },
                {
                    backgroundColor: '#00a65a',
                    borderColor: '#ced4da',
                    data: [
                        $salesChartMain.data('main-high'),
                    ],
                },
                {
                    backgroundColor: '#90ee90',
                    borderColor: '#ced4da',
                    data: [
                        $salesChartMain.data('main-low'),
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