const userSearchInput = document.querySelector('#user-search');
    const searchResultsDiv = document.querySelector('#user-search-results');
    const hiddenUserIdInput = document.querySelector('#released-to-user-id');
    const releaseSubmitButton = document.querySelector('#release-submit-button');
    
    // Only run this code if the search input exists on the page
    if (userSearchInput) {
        
        // Event listener for typing in the search box
        userSearchInput.addEventListener('input', async () => {
            const searchTerm = userSearchInput.value.trim();

            searchResultsDiv.innerHTML = '';
            releaseSubmitButton.disabled = true;
            if(hiddenUserIdInput) hiddenUserIdInput.value = '';

            if (searchTerm.length < 2) {
                return; // Don't search for less than 2 characters
            }

            // Fetch results from our API
            const response = await fetch(`/Office-Supply-Request-System/api/search-users.php?term=${searchTerm}`);
            const users = await response.json();

            // Display the results
            users.forEach(user => {
                const resultDiv = document.createElement('div');
                resultDiv.classList.add('search-result-item');
                resultDiv.textContent = `${user.first_name} ${user.last_name}`;
                resultDiv.dataset.userId = user.id;
                searchResultsDiv.appendChild(resultDiv);
            });
        });

        // Event listener for clicking on a search result
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