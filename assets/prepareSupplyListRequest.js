const prepareModal = document.querySelector('#prepare-supplies-modal');

if (prepareModal) {
    const suppliesListDiv = prepareModal.querySelector('#prepare-supplies-list');
    const hiddenRequestIdInput = prepareModal.querySelector('#prepare-request-id');
    const prepareForm = prepareModal.querySelector('#prepare-supplies-form');
    const prepareErrorDiv = prepareModal.querySelector('#prepare-form-error');

    const checkStockLevel = (quantityInput) => {
        const parentItem = quantityInput.closest('.supply-item');
        const warningElement = parentItem.querySelector('.low-stock-warning');
        
        const currentStock = parseInt(quantityInput.dataset.currentStock, 10);
        const preparedQuantity = parseInt(quantityInput.value, 10);

        if (isNaN(currentStock) || isNaN(preparedQuantity)) {
            warningElement.style.display = 'none';
            return;
        }

        const remainingStock = currentStock - preparedQuantity;

        if (remainingStock < 0) {
            warningElement.textContent = `Warning: This will result in a stock of ${remainingStock}.`;
            warningElement.style.display = 'block';
        } else {
            warningElement.style.display = 'none';
        }
    };

    document.body.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-target="#prepare-supplies-modal"]');
        
        if (!button) return;

        prepareErrorDiv.style.display = 'none';
        prepareErrorDiv.textContent = '';

        const requestId = button.dataset.requestId;
        if (!requestId) return;

        hiddenRequestIdInput.value = requestId;
        suppliesListDiv.innerHTML = '<p>Loading supplies...</p>';

        try {
            const response = await fetch(`/Office-Supply-Request-System/api/get-request-details.php?request_id=${requestId}`);
            if (!response.ok) throw new Error('Failed to fetch request details.');
            
            const supplies = await response.json();
            suppliesListDiv.innerHTML = '';

            if (supplies.length === 0) {
                suppliesListDiv.innerHTML = '<p>No supplies found for this request.</p>';
                return;
            }

            supplies.forEach(supply => {
                const supplyId = supply.supplies_id;
                const supplyName = supply.name;
                const requestedQuantity = supply.supply_quantity;
                const stockQuantity = supply.stock_quantity; 

                const itemHtml = `
                    <div class="form-group supply-item">
                        <div class="supply-details">
                            <span class="supply-name">${supplyName}</span>
                            <small class="low-stock-warning" style="display: none;"></small>
                        </div>
                        <div class="supply-controls">
                            <input type="number" name="supplies[${supplyId}][quantity]" 
                                   value="${requestedQuantity}" 
                                   min="1" 
                                   max="${requestedQuantity}" 
                                   data-original-quantity="${requestedQuantity}"
                                   data-current-stock="${stockQuantity}">
                            <input type="checkbox" name="supplies[${supplyId}][enabled]" id="supply-${supplyId}" checked>
                            <label class="toggle-switch" for="supply-${supplyId}"></label>
                        </div>
                    </div>
                `;
                suppliesListDiv.insertAdjacentHTML('beforeend', itemHtml);
            });
            
            suppliesListDiv.querySelectorAll('input[type="number"]').forEach(checkStockLevel);

        } catch (error) {
            console.error(error);
            suppliesListDiv.innerHTML = '<p>Error loading supplies. Please try again.</p>';
        }
    });

    prepareForm.addEventListener('submit', (event) => {
        let isValid = true;
        let errorMessage = '';

        const checkedItems = suppliesListDiv.querySelectorAll('.supply-item input[type="checkbox"]:checked');

        if (checkedItems.length === 0) {
            isValid = false;
            errorMessage = 'You must leave at least one supply checked. If none are available, deny the request instead.';
        } else {
            for (const checkbox of checkedItems) {
                const parentDiv = checkbox.closest('.supply-item');
                const quantityInput = parentDiv.querySelector('input[type="number"]');
                const supplyName = parentDiv.querySelector('.supply-name').textContent;

                const originalQuantity = parseInt(quantityInput.dataset.originalQuantity, 10);
                const currentQuantity = parseInt(quantityInput.value, 10);
                const currentStock = parseInt(quantityInput.dataset.currentStock, 10); // Get current stock

                if (isNaN(currentQuantity) || currentQuantity <= 0) {
                    isValid = false;
                    errorMessage = `The quantity for "${supplyName}" must be at least 1.`;
                    break;
                }

                if (currentQuantity > originalQuantity) {
                    isValid = false;
                    errorMessage = `The quantity for "${supplyName}" cannot exceed the originally requested amount of ${originalQuantity}.`;
                    break;
                }

                if ((currentStock - currentQuantity) < 0) {
                    isValid = false;
                    errorMessage = `Cannot prepare "${supplyName}". Stock would become negative.`;
                    break;
                }
            }
        }

        if (!isValid) {
            event.preventDefault();
            prepareErrorDiv.textContent = errorMessage;
            prepareErrorDiv.style.display = 'block';
        } else {
            prepareErrorDiv.style.display = 'none';
        }
    });
    
    suppliesListDiv.addEventListener('input', (event) => {
        const target = event.target;

        if (target.type === 'number') {
            checkStockLevel(target);
        }

        if (target.type === 'checkbox') {
            const parentItem = target.closest('.supply-item');
            const quantityInput = parentItem.querySelector('input[type="number"]');

            if (target.checked) {
                parentItem.classList.remove('item-disabled');
                quantityInput.disabled = false;
            } else {
                parentItem.classList.add('item-disabled');
                quantityInput.disabled = true;
            }
        }

        prepareErrorDiv.style.display = 'none';
    });
}