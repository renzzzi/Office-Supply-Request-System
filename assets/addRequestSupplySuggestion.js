const itemNameInput = document.querySelector('#item-name');
const searchResultsDiv = document.querySelector('#supply-search-results');
const quantityInput = document.querySelector('#quantity');
    
if (itemNameInput && searchResultsDiv) {

    itemNameInput.addEventListener('input', async () => {
        const searchTerm = itemNameInput.value.trim();

        searchResultsDiv.innerHTML = '';

        if (searchTerm.length < 1) {
            return;
        }

        try {
            const response = await fetch(`/Office-Supply-Request-System/api/search-supplies.php?term=${searchTerm}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const supplyNames = await response.json();

            supplyNames.forEach(name => {
                const resultDiv = document.createElement('div');
                resultDiv.classList.add('search-result-item');
                resultDiv.textContent = name;
                searchResultsDiv.appendChild(resultDiv);
            });

        } catch (error) {
            console.error('Could not fetch supply suggestions:', error);
        }
    });

    searchResultsDiv.addEventListener('click', (event) => {
        if (event.target.classList.contains('search-result-item')) {
            const selectedName = event.target.textContent;

            itemNameInput.value = selectedName;

            searchResultsDiv.innerHTML = '';
                
            quantityInput.focus();
        }
    });
}