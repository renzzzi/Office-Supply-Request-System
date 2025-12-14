document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.delete-trigger-btn');
    const deleteModal = document.getElementById('delete-confirmation-modal');
    const modalActionInput = document.getElementById('delete-action-input');
    const modalIdInput = document.getElementById('delete-id-input');
    const modalText = document.getElementById('delete-modal-text');
    const closeBtns = document.querySelectorAll('.close-button');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const id = btn.dataset.id;
            const context = btn.dataset.context;

            if (modalActionInput) modalActionInput.value = action;
            if (modalIdInput) modalIdInput.value = id;
            if (modalText) modalText.innerText = `Are you sure you want to delete this ${context}? This action cannot be undone.`;

            if (deleteModal) deleteModal.classList.add('show');
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-container');
            if (modal) modal.classList.remove('show');
        });
    });
    
    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if(e.target === deleteModal) deleteModal.classList.remove('show');
        });
    }
});