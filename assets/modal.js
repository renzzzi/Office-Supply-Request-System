const modalContainer = document.querySelector('.modal-container');
const modal = document.querySelector('.modal');
const closeButton = document.querySelector('.close-button');
const openButton = document.querySelector('.open-button');

openButton.addEventListener('click', () => {
    modalContainer.classList.add('show');
});

closeButton.addEventListener('click', () => {
    modalContainer.classList.remove('show');
});

modalContainer.addEventListener('click', (event) => {
    if (event.target === modalContainer) {
        modalContainer.classList.remove('show');
    }
});