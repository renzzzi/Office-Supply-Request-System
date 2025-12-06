document.addEventListener('DOMContentLoaded', function () {
    const dataContainer = document.getElementById('dashboard-data');
    
    if (!dataContainer) return;

    const workflowData = JSON.parse(dataContainer.dataset.workflowData);
    const topSystemItemsLabels = JSON.parse(dataContainer.dataset.topSystemItemsLabels);
    const topSystemItemsData = JSON.parse(dataContainer.dataset.topSystemItemsData);
    
    const textColor = 'rgba(228, 228, 231, 0.8)';
    const gridColor = 'rgba(63, 63, 70, 0.5)';

    const workflowBarCtx = document.getElementById('workflowBarChart');
    if (workflowBarCtx) {
        new Chart(workflowBarCtx, {
            type: 'bar',
            data: {
                labels: ['Claimed', 'Ready For Pickup'],
                datasets: [{
                    label: 'My Active Requests',
                    data: [workflowData.claimed, workflowData.ready],
                    backgroundColor: ['#d39237', '#26ac74'],
                    borderColor: ['#e3a851', '#2cb67d'],
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
                        ticks: { color: textColor, stepSize: 1 }, 
                        grid: { color: gridColor } 
                    },
                    y: { 
                        ticks: { color: textColor }, 
                        grid: { display: false }
                    }
                }
            }
        });
    }

    const systemTopItemsCtx = document.getElementById('systemTopItemsChart');
    if (systemTopItemsCtx) {
        new Chart(systemTopItemsCtx, {
            type: 'bar',
            data: {
                labels: topSystemItemsLabels,
                datasets: [{
                    label: 'Total Quantity Requested',
                    data: topSystemItemsData,
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
                        grid: { display: false } 
                    }
                }
            }
        });
    }
});