const openButtons = document.querySelectorAll('.open-button');
const modalContainers = document.querySelectorAll('.modal-container');
const closeButtons = document.querySelectorAll('.close-button');

openButtons.forEach(button => {
    button.addEventListener('click', () => {
        const modalId = button.dataset.target;
        const modalToShow = document.querySelector(modalId);

        if (modalToShow) {
            const requestId = button.dataset.requestId;
            if (requestId) {
                const hiddenRequestIdInput = modalToShow.querySelector('#release-request-id');
                if (hiddenRequestIdInput) {
                    hiddenRequestIdInput.value = requestId;
                }
            }

            modalToShow.classList.add('show');
        }
    });
});

closeButtons.forEach(button => {
    button.addEventListener('click', () => {
        const modalToClose = button.closest('.modal-container');
        if (modalToClose) {
            modalToClose.classList.remove('show');
        }
    });
});

modalContainers.forEach(modalContainer => {
    modalContainer.addEventListener('click', (e) => {
        if (e.target === modalContainer) {
            modalContainer.classList.remove('show');
        }
    });
});