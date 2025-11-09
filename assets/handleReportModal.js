const reportForm = document.getElementById('report-form');
const printBtn = document.getElementById('print-report-btn');
const csvBtn = document.getElementById('download-csv-btn');

if (reportForm && printBtn) {
    printBtn.addEventListener('click', function() {
        reportForm.action = this.dataset.action;
        reportForm.target = '_blank';
    });
}

if (reportForm && csvBtn) {
    csvBtn.addEventListener('click', function() {
        reportForm.action = this.dataset.action;
        reportForm.target = '_self';
    });
}