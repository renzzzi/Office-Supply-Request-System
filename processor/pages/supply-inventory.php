<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";

$pdoConnection = (new Database())->connect();
$suppliesObj = new Supplies($pdoConnection);

$allSupplies = $suppliesObj->getAllSuppliesWithCategories();

$lowStockThreshold = 5;
?>

<div class="page-header" style="margin-bottom: 24px;">
    <h2>Supply Inventory</h2>
    
    <div class="custom-dropdown-container" style="max-width: 450px; margin-top: 16px;">
        <div class="dropdown-header" style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: 8px; padding: 12px; display: flex; gap: 10px;">
            <input type="text" id="inventorySearch" placeholder="Search supply name..." autocomplete="off" 
                   style="flex: 2; padding: 10px; background-color: var(--color-background); border: 1px solid var(--color-border); border-radius: 6px; color: var(--color-text-primary);">
            
            <select id="inventoryCategoryFilter" 
                    style="flex: 1; padding: 10px; background-color: var(--color-background); border: 1px solid var(--color-border); border-radius: 6px; color: var(--color-text-primary);">
                <option value="">All Categories</option>
                <?php 
                    $categories = array_unique(array_column($allSupplies, 'category_name'));
                    sort($categories);
                    foreach ($categories as $cat): 
                ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<table id="inventoryTable">
    <thead>
        <tr>
            <th style="text-align: left; padding-left: 20px;">Supply Name</th>
            <th>Category</th>
            <th>Unit Of Supply</th>
            <th>Price per Unit</th>
            <th>Current Stock</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allSupplies as $supply): ?>
            <?php
                $stock = (int)$supply["stock_quantity"];
                $isLowStock = $stock <= $lowStockThreshold;
                $isOutOfStock = $stock == 0;
                $categoryName = $supply["category_name"] ?? "Uncategorized";
                $price = "₱" . number_format($supply["price_per_unit"], 2);
                
                $rowClass = "";
                if ($isOutOfStock) {
                    $rowClass = "cancel-button";
                } elseif ($isLowStock) {
                    $rowClass = "pending-button";
                }
            ?>
            <tr class="<?= $rowClass ?>">
                <td class="searchable-name" style="text-align: left; padding-left: 20px; font-weight: 500;">
                    <?= htmlspecialchars($supply["name"]) ?>
                </td>
                <td class="searchable-category"><?= htmlspecialchars($categoryName) ?></td>
                <td><?= htmlspecialchars($supply["unit_of_supply"]) ?></td>
                <td><?= htmlspecialchars($price) ?></td>
                <td style="font-weight: 700; font-size: 1.1em;">
                    <?= $stock ?>
                </td>
                <td style="font-weight: 500;">
                    <?php if($isOutOfStock): ?>
                        Out of Stock
                    <?php elseif($isLowStock): ?>
                        Low Stock
                    <?php else: ?>
                        In Stock
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr class="empty-table-message" id="noResultsRow" style="display: none;">
            <td colspan="6">
                No supplies match your search criteria.
            </td>
        </tr>
    </tbody>
</table>