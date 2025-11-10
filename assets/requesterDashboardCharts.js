document.addEventListener('DOMContentLoaded', function () {
    const dataContainer = document.getElementById('dashboard-data');
    if (!dataContainer) {
        console.error('Dashboard data container not found!');
        return;
    }

    const statusCounts = JSON.parse(dataContainer.dataset.statusCounts);
    const topItemLabels = JSON.parse(dataContainer.dataset.topItemsLabels);
    const topItemData = JSON.parse(dataContainer.dataset.topItemsData);
    const textColor = 'rgba(228, 228, 231, 0.8)';
    const gridColor = 'rgba(63, 63, 70, 0.5)';
    const statusPieCtx = document.getElementById('statusPieChart');

    if (statusPieCtx) {
        new Chart(statusPieCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Pending', 'Claimed', 'Ready For Pickup', 'Released', 'Denied'
                ],
                datasets: [{
                    label: 'Request Count',
                    data: [
                        statusCounts['Pending'],
                        statusCounts['Claimed'],
                        statusCounts['Ready For Pickup'],
                        statusCounts['Released'],
                        statusCounts['Denied']
                    ],
                    backgroundColor: [
                        '#946728ff', '#ffa41cff', '#2e923fff', '#2cc987ff', '#e45252'
                    ],
                    borderColor: '#2a2a33',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor }
                    }
                }
            }
        });
    }

    const topItemsBarCtx = document.getElementById('topItemsBarChart');
    if (topItemsBarCtx) {
        new Chart(topItemsBarCtx, {
            type: 'bar',
            data: {
                labels: topItemLabels,
                datasets: [{
                    label: 'Total Quantity Requested',
                    data: topItemData,
                    backgroundColor: 'rgba(127, 90, 240, 0.6)',
                    borderColor: 'rgba(127, 90, 240, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    }
                }
            }
        });
    }
});