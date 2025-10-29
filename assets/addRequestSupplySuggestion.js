const itemNameInput = document.querySelector('#item-name');
    const searchResultsDiv = document.querySelector('#supply-search-results');
    const quantityInput = document.querySelector('#quantity');
    
    // Only run if the elements exist on the page
    if (itemNameInput && searchResultsDiv) {

        // Event listener for typing in the supply name input
        itemNameInput.addEventListener('input', async () => {
            const searchTerm = itemNameInput.value.trim();

            // Clear previous results
            searchResultsDiv.innerHTML = '';

            if (searchTerm.length < 1) {
                return; // Don't search for less than 1 character
            }

            try {
                // Fetch results from our new supply API
                const response = await fetch(`/Office-Supply-Request-System/api/search-supplies.php?term=${searchTerm}`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const supplyNames = await response.json();

                // Display the results
                supplyNames.forEach(name => {
                    const resultDiv = document.createElement('div');
                    resultDiv.classList.add('search-result-item'); // Use the same class for styling
                    resultDiv.textContent = name;
                    searchResultsDiv.appendChild(resultDiv);
                });

            } catch (error) {
                console.error('Could not fetch supply suggestions:', error);
            }
        });

        // Event listener for clicking on a search result
        searchResultsDiv.addEventListener('click', (event) => {
            if (event.target.classList.contains('search-result-item')) {
                const selectedName = event.target.textContent;

                // Put the selected name into the input box
                itemNameInput.value = selectedName;

                // Clear the search results
                searchResultsDiv.innerHTML = '';
                
                // For a better user experience, move focus to the quantity input
                quantityInput.focus();
            }
        });
    }