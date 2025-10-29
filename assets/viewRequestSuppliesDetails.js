const detailsModal = document.querySelector('#supply-details-modal');

if (detailsModal) {
    const modalTitle = detailsModal.querySelector('#supply-details-title');
    const modalTbody = detailsModal.querySelector('#supply-details-tbody');

    document.body.addEventListener('click', async (event) => {
        const trigger = event.target.closest('.view-supplies-trigger');

        if (!trigger) {
            return;
        }

        const requestId = trigger.dataset.requestId;
        if (!requestId) {
            return;
        }

        modalTitle.textContent = `Loading Supplies for Request #${requestId}...`;
        modalTbody.innerHTML = '<tr><td colspan="2">Fetching details...</td></tr>';
        detailsModal.classList.add('show');

        try {
            const response = await fetch(`/Office-Supply-Request-System/api/get-request-details.php?request_id=${requestId}`);
            if (!response.ok) {
                throw new Error('Failed to fetch request details.');
            }
            
            const supplies = await response.json();

            modalTbody.innerHTML = '';
            modalTitle.textContent = `Full Supply List for Request #${requestId}`;

            if (supplies.length === 0) {
                modalTbody.innerHTML = '<tr><td colspan="2">No supplies are associated with this request.</td></tr>';
                return;
            }

            let rowsHtml = '';
            supplies.forEach(supply => {
                rowsHtml += `
                    <tr>
                        <td>${supply.name}</td>
                        <td>${supply.supply_quantity}</td>
                    </tr>
                `;
            });
            modalTbody.innerHTML = rowsHtml;

        } catch (error) {
            console.error(error);
            modalTitle.textContent = `Error`;
            modalTbody.innerHTML = '<tr><td colspan="2">Could not load supply details. Please try again.</td></tr>';
        }
    });
}