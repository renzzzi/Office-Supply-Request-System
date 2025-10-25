const entryForm = document.querySelector('.new-request-form');
const addSupplyButton = document.querySelector('.add-supply-name-button');
const tableBody = document.querySelector('#request-table-body');
const hiddenInputsContainer = document.querySelector('#hidden-inputs-container');
const itemNameInput = document.querySelector('#item-name');
const quantityInput = document.querySelector('#quantity');

let supplyIndex = 0;

addSupplyButton.addEventListener('click', () => {
    const itemName = itemNameInput.value.trim();
    const quantity = quantityInput.value.trim();
    if (itemName === '' || quantity === '') {
        alert('Please fill out both supply name and quantity.');
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