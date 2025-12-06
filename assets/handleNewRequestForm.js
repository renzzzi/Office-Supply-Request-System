document.addEventListener("DOMContentLoaded", () => {
    const entryForm = document.querySelector('.new-request-form');
    const addSupplyButton = document.querySelector('.add-supply-name-button');
    const tableBody = document.querySelector('#request-table-body');
    const hiddenInputsContainer = document.querySelector('#hidden-inputs-container');
    const quantityInput = document.querySelector('#quantity');
    const supplyNameErrorDiv = document.querySelector('#supply-name-error');
    const quantityErrorDiv = document.querySelector('#quantity-error');
    const mainRequestForm = document.querySelector('#main-request-form');
    const mainRequestError = document.querySelector('#main-request-error');

    const dropdownContainer = document.querySelector('#custom-supply-dropdown');
    const dropdownTrigger = document.querySelector('#dropdown-trigger');
    const dropdownMenu = document.querySelector('#dropdown-menu');
    const hiddenInput = document.querySelector('#item-name');
    const internalSearch = document.querySelector('#internal-search');
    const internalCategory = document.querySelector('#internal-category');
    const optionsList = document.querySelector('#dropdown-list');
    const noResults = document.querySelector('#no-results');
    
    if (entryForm && dropdownContainer) {
        let supplyIndex = 0;

        dropdownTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
            internalSearch.focus();
        });

        document.addEventListener('click', (e) => {
            if (!dropdownContainer.contains(e.target)) {
                dropdownMenu.classList.remove('active');
            }
        });

        dropdownMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        function filterOptions() {
            const searchTerm = internalSearch.value.toLowerCase();
            const categoryId = internalCategory.value;
            const options = optionsList.querySelectorAll('.dropdown-option');
            let hasVisible = false;

            options.forEach(option => {
                const name = option.getAttribute('data-search-term');
                const cat = option.getAttribute('data-category-id');
                
                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = categoryId === "" || cat === categoryId;

                if (matchesSearch && matchesCategory) {
                    option.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    option.classList.add('hidden');
                }
            });

            noResults.style.display = hasVisible ? 'none' : 'block';
        }

        internalSearch.addEventListener('input', filterOptions);
        internalCategory.addEventListener('change', filterOptions);

        optionsList.addEventListener('click', (e) => {
            const option = e.target.closest('.dropdown-option');
            if (option) {
                const value = option.getAttribute('data-value');
                const text = option.textContent.trim();
                
                hiddenInput.value = value;
                dropdownTrigger.textContent = text;
                dropdownMenu.classList.remove('active');
                
                internalSearch.value = "";
                internalCategory.value = "";
                filterOptions();
            }
        });

        addSupplyButton.addEventListener('click', () => {
            supplyNameErrorDiv.textContent = '';
            quantityErrorDiv.textContent = '';

            const itemName = hiddenInput.value;
            const quantity = quantityInput.value.trim();
            let isValid = true;

            if (!itemName) {
                supplyNameErrorDiv.textContent = 'Please select a supply.';
                isValid = false;
            }

            if (quantity === '') {
                quantityErrorDiv.textContent = 'Please enter a quantity.';
                isValid = false;
            } else if (parseInt(quantity) <= 0) {
                quantityErrorDiv.textContent = 'Quantity must be a positive number.';
                isValid = false;
            }

            if (!isValid) return;

            const existingRows = tableBody.querySelectorAll('tr');
            for (let row of existingRows) {
                const existingName = row.cells[0].textContent.trim();
                if (existingName === itemName) {
                    alert('This supply is already in your list.');
                    return;
                }
            }

            mainRequestError.style.display = 'none';

            const newRow = document.createElement('tr');
            newRow.dataset.index = supplyIndex;
            newRow.innerHTML = `
                <td>${itemName}</td>
                <td>${quantity}</td>
                <td><button type="button" class="remove-button" style="color: white; background-color: #dc3545; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Remove</button></td>
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
            
            hiddenInput.value = "";
            dropdownTrigger.textContent = "-- Select a Supply --";
            quantityInput.value = "";
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
});