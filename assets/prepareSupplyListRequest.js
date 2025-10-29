const prepareModal = document.querySelector('#prepare-supplies-modal');

if (prepareModal) {
    const suppliesListDiv = prepareModal.querySelector('#prepare-supplies-list');
    const hiddenRequestIdInput = prepareModal.querySelector('#prepare-request-id');
    const prepareForm = prepareModal.querySelector('#prepare-supplies-form');
    const prepareErrorDiv = prepareModal.querySelector('#prepare-form-error');

    document.body.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-target="#prepare-supplies-modal"]');
        
        if (!button) {
            return;
        }

        prepareErrorDiv.style.display = 'none';

        const requestId = button.dataset.requestId;
        if (!requestId) {
            return;
        }

        hiddenRequestIdInput.value = requestId;

        suppliesListDiv.innerHTML = '<p>Loading supplies...</p>';

        try {
            const response = await fetch(`/Office-Supply-Request-System/api/get-request-details.php?request_id=${requestId}`);
            if (!response.ok) {
                throw new Error('Failed to fetch request details.');
            }
            
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

                const itemHtml = `
                    <div class="form-group supply-item">
                        <input type="checkbox" name="supplies[${supplyId}][enabled]" id="supply-${supplyId}" checked>
                        <label for="supply-${supplyId}">${supplyName}</label>
                        <input type="number" name="supplies[${supplyId}][quantity]" value="${requestedQuantity}" min="1" max="${requestedQuantity}">
                    </div>
                `;
                suppliesListDiv.insertAdjacentHTML('beforeend', itemHtml);
            });

        } catch (error) {
            console.error(error);
            suppliesListDiv.innerHTML = '<p>Error loading supplies. Please try again.</p>';
        }
    });

    prepareForm.addEventListener('submit', (event) => {
        const checkedBoxes = suppliesListDiv.querySelectorAll('input[type="checkbox"]:checked');

        if (checkedBoxes.length === 0) {
            event.preventDefault();

            prepareErrorDiv.textContent = 'You must leave at least one supply checked. If none are available, deny the request instead.';
            
            prepareErrorDiv.style.display = 'block';
        } else {
            prepareErrorDiv.style.display = 'none';
        }
    });

    suppliesListDiv.addEventListener('change', (event) => {
        if (event.target.type === 'checkbox') {
            const checkedBoxes = suppliesListDiv.querySelectorAll('input[type="checkbox"]:checked');
            if (checkedBoxes.length > 0) {
                prepareErrorDiv.style.display = 'none';
            }
        }
    });
}