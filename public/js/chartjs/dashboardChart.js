$(function () {
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
    var purchasePPECount = $('#pieChart').data('ppe');
    var purchaseHighCount = $('#pieChart').data('high');
    var purchaseLowCount = $('#pieChart').data('low');
    var pieData = {
        labels: [
            'PPE',
            'High Value',
            'Low Value',
        ],
        datasets: [
            {
                data: [purchasePPECount, purchaseHighCount, purchaseLowCount ],
                backgroundColor: ['#ffc107', '#00a65a', '#90ee90']
            }
        ]
    }
    var pieOptions = {
        legend: {
            display: false
        }
    }
    var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: pieData,
        options: pieOptions
    });
});