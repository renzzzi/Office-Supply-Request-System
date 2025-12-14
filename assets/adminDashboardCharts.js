document.addEventListener('DOMContentLoaded', function () {
    const dataContainer = document.getElementById('dashboard-data');
    
    if (!dataContainer) return;

    // Parse Data
    const procLabels = JSON.parse(dataContainer.dataset.procLabels);
    const procData = JSON.parse(dataContainer.dataset.procData);
    const deptLabels = JSON.parse(dataContainer.dataset.deptLabels);
    const deptData = JSON.parse(dataContainer.dataset.deptData);
    
    const textColor = 'rgba(228, 228, 231, 0.8)';
    const gridColor = 'rgba(63, 63, 70, 0.5)';

    // Chart 1: Processor Performance Level (Vertical Bar)
    const procCtx = document.getElementById('procPerformanceChart');
    if (procCtx) {
        new Chart(procCtx, {
            type: 'bar',
            data: {
                labels: procLabels,
                datasets: [{
                    label: 'Performance Level (%)',
                    data: procData,
                    backgroundColor: 'rgba(127, 90, 240, 0.6)', // Purple
                    borderColor: 'rgba(127, 90, 240, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'x', // 'x' makes it vertical bars
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { 
                        ticks: { color: textColor }, 
                        grid: { display: false } 
                    },
                    y: { 
                        // Settings for 0-100% with 20% increments
                        beginAtZero: true,
                        min: 0,
                        max: 100,
                        ticks: { 
                            stepSize: 20,
                            color: textColor,
                            callback: function(value) {
                                return value + "%"; // Add % sign to numbers
                            }
                        }, 
                        grid: { color: gridColor },
                        title: {
                            display: true,
                            color: textColor
                        }
                    }
                }
            }
        });
    }

    // Chart 2: Top 5 Departments
    const deptCtx = document.getElementById('deptVolumeChart');
    if (deptCtx) {
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Request Volume',
                    data: deptData,
                    backgroundColor: 'rgba(44, 182, 125, 0.6)', // Green
                    borderColor: 'rgba(44, 182, 125, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { 
                        ticks: { color: textColor }, 
                        grid: { display: false } 
                    },
                    y: { 
                        ticks: { color: textColor, stepSize: 2 }, 
                        grid: { color: gridColor } 
                    }
                }
            }
        });
    }
});