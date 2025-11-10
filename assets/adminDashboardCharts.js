document.addEventListener('DOMContentLoaded', function () {
    const dataContainer = document.getElementById('dashboard-data');
    if (!dataContainer) {
        return;
    }

    const deptLabels = JSON.parse(dataContainer.dataset.deptLabels);
    const deptData = JSON.parse(dataContainer.dataset.deptData);
    const invCategoryLabels = JSON.parse(dataContainer.dataset.invCategoryLabels);
    const invCategoryData = JSON.parse(dataContainer.dataset.invCategoryData);

    const textColor = 'rgba(228, 228, 231, 0.8)';
    const gridColor = 'rgba(63, 63, 70, 0.5)';

    const deptVolumeCtx = document.getElementById('deptVolumeChart');
    if (deptVolumeCtx) {
        new Chart(deptVolumeCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Total Requests',
                    data: deptData,
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
                    x: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
    }

    const invValueCtx = document.getElementById('invValueChart');
    if (invValueCtx) {
        new Chart(invValueCtx, {
            type: 'bar',
            data: {
                labels: invCategoryLabels,
                datasets: [{
                    label: 'Total Value',
                    data: invCategoryData,
                    backgroundColor: 'rgba(44, 182, 125, 0.6)', 
                    borderColor: 'rgba(44, 182, 125, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.x !== null) {
                                    label += new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(context.parsed.x);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        ticks: { 
                            color: textColor,
                            callback: function(value, index, values) {
                                if (value >= 1000) {
                                    return '₱' + (value / 1000) + 'k';
                                }
                                return '₱' + value;
                            }
                        }, 
                        grid: { color: gridColor } 
                    },
                    y: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
    }
});