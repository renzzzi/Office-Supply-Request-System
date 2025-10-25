const supplyInput = document.querySelector('#item-name');
const suggestionsList = document.querySelector('#supply-suggestions');

supplyInput.addEventListener('input', function() {
    const term = encodeURIComponent(supplyInput.value);
    if (term.length < 1) {
        suggestionsList.innerHTML = '';
        return;
    }
    fetch('pages/my-requests.php?term=' + term)
        .then(response => response.json())
        .then(data => {
            suggestionsList.innerHTML = '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                suggestionsList.appendChild(option);
            });
        });
});
    