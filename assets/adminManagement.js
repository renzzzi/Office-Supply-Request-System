const adminContentArea = document.querySelector('.content-area');

if (adminContentArea) {
    adminContentArea.addEventListener('click', (event) => {
        const button = event.target.closest('.open-button[data-entity-data]');
        if (!button) return;

        const targetModalSelector = button.dataset.target;
        if (!targetModalSelector) return;

        const modal = document.querySelector(targetModalSelector);
        if (!modal) return;

        const form = modal.querySelector('form');
        const entityId = button.dataset.entityId;
        const entityData = JSON.parse(button.dataset.entityData);

        if (form.querySelector('input[name="entity_id"]')) {
            form.querySelector('input[name="entity_id"]').value = entityId;
        }

        for (const key in entityData) {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = entityData[key];
            }
        }
        
        if (targetModalSelector === '#adjust-stock-modal') {
            const stockSpan = modal.querySelector('[data-current-stock]');
            if (stockSpan) {
                stockSpan.textContent = entityData.current_stock;
            }
        }
    });
}