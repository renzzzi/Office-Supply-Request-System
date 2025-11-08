const reportBtn = document.getElementById('report-menu-btn');
const dropdownMenu = document.getElementById('report-dropdown-menu');

if (reportBtn && dropdownMenu) {
    reportBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });
}

window.addEventListener('click', function(event) {
    if (dropdownMenu && dropdownMenu.classList.contains('show')) {
        if (!reportBtn.contains(event.target)) {
            dropdownMenu.classList.remove('show');
        }
    }
});