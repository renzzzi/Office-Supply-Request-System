document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('delete-confirm-modal');
    const deleteInput = document.getElementById('delete-request-id-input');
    const deleteButtons = document.querySelectorAll('.delete-request-btn');
    const cancelModalBtn = deleteModal.querySelector('.close-modal-btn');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const requestId = this.getAttribute('data-id');
            
            if(deleteInput) {
                deleteInput.value = requestId;
            }

            if(deleteModal) {
                deleteModal.classList.add('show');
            }
        });
    });

    if (cancelModalBtn) {
        cancelModalBtn.addEventListener('click', function() {
            deleteModal.classList.remove('show');
        });
    }
});