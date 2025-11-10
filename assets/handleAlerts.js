const contentArea = document.querySelector('.content-area');

if (contentArea) {
    contentArea.addEventListener('click', (event) => {
        if (event.target.matches('.alert .close-button')) {
            event.target.parentElement.style.display = 'none';
        }
    });
}