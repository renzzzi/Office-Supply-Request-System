const userSearchInput = document.querySelector('#user-search');
const searchResultsDiv = document.querySelector('#user-search-results');
const hiddenUserIdInput = document.querySelector('#released-to-user-id');
const releaseSubmitButton = document.querySelector('#release-submit-button');
    
if (userSearchInput) {
    userSearchInput.addEventListener('input', async () => {
        const searchTerm = userSearchInput.value.trim();

        searchResultsDiv.innerHTML = '';
        releaseSubmitButton.disabled = true;
        if(hiddenUserIdInput) hiddenUserIdInput.value = '';

        if (searchTerm.length < 2) {
            return;
        }

        const response = await fetch(`/Office-Supply-Request-System/api/search-users.php?term=${searchTerm}`);
        const users = await response.json();

        users.forEach(user => {
            const resultDiv = document.createElement('div');
            resultDiv.classList.add('search-result-item');
            resultDiv.textContent = `${user.first_name} ${user.last_name}`;
            resultDiv.dataset.userId = user.id;
            searchResultsDiv.appendChild(resultDiv);
        });
    });

    searchResultsDiv.addEventListener('click', (event) => {
        if (event.target.classList.contains('search-result-item')) {
            const selectedName = event.target.textContent;
            const selectedId = event.target.dataset.userId;

            userSearchInput.value = selectedName;
            if(hiddenUserIdInput) hiddenUserIdInput.value = selectedId;

            searchResultsDiv.innerHTML = '';
            releaseSubmitButton.disabled = false;
        }
    });
}