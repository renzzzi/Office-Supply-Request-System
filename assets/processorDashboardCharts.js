document.addEventListener('DOMContentLoaded', function () {
    const dataContainer = document.getElementById('dashboard-data');
    if (!dataContainer) return;

    const workflowData = JSON.parse(dataContainer.dataset.workflowData);
    
    const weeklyLabels = JSON.parse(dataContainer.dataset.weeklyLabels);
    const weeklyData = JSON.parse(dataContainer.dataset.weeklyData);

    const textColor = 'rgba(228, 228, 231, 0.8)';
    const gridColor = 'rgba(63, 63, 70, 0.5)';

    const ctx1 = document.getElementById('workflowBarChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Claimed', 'Ready for Pickup', 'Finished Today'],
                datasets: [{
                    label: 'Count',
                    data: [workflowData.claimed, workflowData.ready, workflowData.finished_today],
                    backgroundColor: [
                        '#faa415',
                        '#00d9ff',
                        '#2cb67d'
                    ],
                    borderColor: '#2a2a33',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, color: textColor }, 
                        grid: { color: gridColor } 
                    },
                    x: { 
                        ticks: { color: textColor }, 
                        grid: { display: false } 
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('weeklyThroughputChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Requests Finished',
                    data: weeklyData,
                    backgroundColor: 'rgba(127, 90, 240, 0.2)',
                    borderColor: 'rgba(127, 90, 240, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, color: textColor }, 
                        grid: { color: gridColor },
                        title: { display: true, text: 'Requests Completed', color: textColor }
                    },
                    x: { 
                        ticks: { color: textColor }, 
                        grid: { display: false } 
                    }
                }
            }
        });
    }
});