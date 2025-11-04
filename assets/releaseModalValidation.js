const releaseForm = document.getElementById('release-form');
if (releaseForm) {
    releaseForm.addEventListener('submit', function(event) {
        const receiverInput = document.getElementById('receiver-input');
        const errorMessage = document.getElementById('release-error-message');

        if (receiverInput.value.trim() === '') {
            event.preventDefault();
            errorMessage.textContent = "Please enter the receiver's name.";
            errorMessage.style.display = 'block';
        } else {
            errorMessage.style.display = 'none';
        }
    });
}

const openReleaseButtons = document.querySelectorAll('button[data-target="#release-modal"]');
openReleaseButtons.forEach(button => {
    button.addEventListener('click', function() {
        const errorMessage = document.getElementById('release-error-message');
        const receiverInput = document.getElementById('receiver-input');
            
        if(errorMessage) errorMessage.style.display = 'none';
        if(receiverInput) receiverInput.value = '';
    });
});