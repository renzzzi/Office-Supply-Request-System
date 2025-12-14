document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inventorySearch');
    const categorySelect = document.getElementById('inventoryCategoryFilter');
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tr');
    const noResultsRow = document.getElementById('noResultsRow');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const categoryText = categorySelect.value.toLowerCase();
        let hasVisibleRow = false;

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            if (row.id === 'noResultsRow') continue;

            const nameCell = row.querySelector('.searchable-name');
            const catCell = row.querySelector('.searchable-category');
            
            if (nameCell && catCell) {
                const name = nameCell.textContent.toLowerCase().trim();
                const cat = catCell.textContent.toLowerCase().trim();
                
                const matchesSearch = name.includes(searchText);
                const matchesCategory = categoryText === "" || cat === categoryText;

                if (matchesSearch && matchesCategory) {
                    row.style.display = "";
                    hasVisibleRow = true;
                } else {
                    row.style.display = "none";
                }
            }
        }

        noResultsRow.style.display = hasVisibleRow ? "none" : "";
    }

    searchInput.addEventListener('keyup', filterTable);
    categorySelect.addEventListener('change', filterTable);
});