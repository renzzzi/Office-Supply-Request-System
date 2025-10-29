const entryForm = document.querySelector('.new-request-form');
const addSupplyButton = document.querySelector('.add-supply-name-button');
const tableBody = document.querySelector('#request-table-body');
const hiddenInputsContainer = document.querySelector('#hidden-inputs-container');
const itemNameInput = document.querySelector('#item-name');
const quantityInput = document.querySelector('#quantity');
const supplyNameErrorDiv = document.querySelector('#supply-name-error');
const quantityErrorDiv = document.querySelector('#quantity-error');
const searchResultsDiv = document.querySelector('#supply-search-results');
const mainRequestForm = document.querySelector('#main-request-form');
const mainRequestError = document.querySelector('#main-request-error');

if (entryForm) {
    let supplyIndex = 0;

    itemNameInput.addEventListener('input', async () => {
        const searchTerm = itemNameInput.value.trim();
        searchResultsDiv.innerHTML = '';

        if (searchTerm.length < 1) {
            return;
        }
        try {
            const response = await fetch(`/Office-Supply-Request-System/api/search-supplies.php?term=${searchTerm}`);
            if (!response.ok) throw new Error('Network response was not ok');
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
            itemNameInput.value = event.target.textContent;
            searchResultsDiv.innerHTML = '';
            quantityInput.focus();
        }
    });

    addSupplyButton.addEventListener('click', () => {
        supplyNameErrorDiv.textContent = '';
        quantityErrorDiv.textContent = '';

        const itemName = itemNameInput.value.trim();
        const quantity = quantityInput.value.trim();
        let isValid = true;

        if (itemName === '') {
            supplyNameErrorDiv.textContent = 'Please enter a supply name.';
            isValid = false;
        }

        if (quantity === '') {
            quantityErrorDiv.textContent = 'Please enter a quantity.';
            isValid = false;
        } else if (parseInt(quantity) <= 0) {
            quantityErrorDiv.textContent = 'Quantity must be a positive number.';
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        mainRequestError.style.display = 'none';

        const newRow = document.createElement('tr');
        newRow.dataset.index = supplyIndex;
        newRow.innerHTML = `
            <td>${itemName}</td>
            <td>${quantity}</td>
            <td><button type="button" class="remove-button">&times;</button></td>
        `;

        const hiddenInputGroup = document.createElement('div');
        hiddenInputGroup.dataset.index = supplyIndex;
        hiddenInputGroup.innerHTML = `
            <input type="hidden" name="supplies[${supplyIndex}][name]" value="${itemName}">
            <input type="hidden" name="supplies[${supplyIndex}][quantity]" value="${quantity}">
        `;

        tableBody.appendChild(newRow);
        hiddenInputsContainer.appendChild(hiddenInputGroup);
        supplyIndex++;
        entryForm.reset();
        itemNameInput.focus();
    });

    tableBody.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-button')) {
            const rowToRemove = event.target.closest('tr');
            const indexToRemove = rowToRemove.dataset.index;
            rowToRemove.remove();
            const hiddenInputsToRemove = hiddenInputsContainer.querySelector(`div[data-index='${indexToRemove}']`);
            if (hiddenInputsToRemove) {
                hiddenInputsToRemove.remove();
            }
        }
    });

    mainRequestForm.addEventListener('submit', (event) => {
        mainRequestError.style.display = 'none';
        mainRequestError.textContent = '';
        
        if (hiddenInputsContainer.children.length === 0) {
            event.preventDefault();
            mainRequestError.textContent = 'Please add at least one supply to the request.';
            mainRequestError.style.display = 'block'; 
        }
    });
}