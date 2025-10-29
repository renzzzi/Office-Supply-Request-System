const entryForm = document.querySelector('.new-request-form');
    const addSupplyButton = document.querySelector('.add-supply-name-button');
    const tableBody = document.querySelector('#request-table-body');
    const hiddenInputsContainer = document.querySelector('#hidden-inputs-container');
    const itemNameInput = document.querySelector('#item-name');
    const quantityInput = document.querySelector('#quantity');
    const supplyNameErrorDiv = document.querySelector('#supply-name-error');
    const quantityErrorDiv = document.querySelector('#quantity-error');

    let supplyIndex = 0;

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