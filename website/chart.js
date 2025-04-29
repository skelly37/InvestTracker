document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('priceChart').getContext('2d');

    const mockLabels = [
        "2025-04-20", "2025-04-21", "2025-04-22", "2025-04-23", "2025-04-24", "2025-04-25"
    ];
    const mockPrices = [
        220.54, 222.10, 219.30, 223.00, 225.40, 224.00
    ];

    const priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: mockLabels,
            datasets: [{
                data: mockPrices,
                borderWidth: 2,
                borderColor: 'blue',
                backgroundColor: 'lightblue',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: 'white',
                pointBorderColor: 'blue',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Price (USD)'
                    },
                    beginAtZero: false
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
        }
    });
});
