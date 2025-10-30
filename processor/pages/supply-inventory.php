<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";
require_once __DIR__ . "/../../classes/supply_categories.php";

$pdoConnection = (new Database())->connect();
$suppliesObj = new Supplies($pdoConnection);
$categoryObj = new SupplyCategories($pdoConnection);

$allSupplies = $suppliesObj->viewAllSupply();

$lowStockThreshold = 5;

?>

<h2>Supply Inventory</h2>
<p>This table shows the current inventory of all supplies in the system.</p>

<table border="1">
    <thead>
        <tr>
            <th>Supply Name</th>
            <th>Category</th>
            <th>Unit Of Supply</th>
            <th>Price per Unit</th>
            <th>Current Stock</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allSupplies as $supply): ?>
            <?php
                $category = $categoryObj->getSupplyCategoryById($supply["supply_categories_id"]);
                $categoryName = $category ? $category["name"] : "N/A";
                $stockClass = ($supply["stock_quantity"] <= $lowStockThreshold) ? "low-stock" : "";
            ?>
            <tr>
                <td><?= htmlspecialchars($supply["name"]) ?></td>
                <td><?= htmlspecialchars($categoryName) ?></td>
                <td><?= htmlspecialchars($supply["unit_of_supply"]) ?></td>
                <td><?= htmlspecialchars("₱" . number_format($supply["price_per_unit"], 2)) ?></td>
                <td class="<?= $stockClass ?>"><?= htmlspecialchars($supply["stock_quantity"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>