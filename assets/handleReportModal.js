const reportForm = document.getElementById('report-form');
const printBtn = document.getElementById('print-report-btn');
const csvBtn = document.getElementById('download-csv-btn');

if (printBtn) {
    printBtn.addEventListener('click', function() {
        reportForm.action = 'pages/print-report.php';
        reportForm.target = '_blank';
    });
}

if (csvBtn) {
    csvBtn.addEventListener('click', function() {
        reportForm.action = '../api/generate-my-requests-csv.php';
        reportForm.target = '_self';
    });
}