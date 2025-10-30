<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";
require_once __DIR__ . "/../../classes/supply_categories.php";

$pdoConnection = (new Database())->connect();
$supplyObj = new Supplies($pdoConnection);
$categoryObj = new SupplyCategories($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "add_supply") {
        $supplyObj->name = $_POST["name"];
        $supplyObj->supply_categories_id = $_POST["category_id"];
        $supplyObj->unit_of_supply = $_POST["unit_of_supply"];
        $supplyObj->price_per_unit = $_POST["price_per_unit"];
        $supplyObj->stock_quantity = $_POST["stock_quantity"];

        if ($supplyObj->addSupply()) {
            header("Location: index.php?page=system-database-configuration");
            exit();
        } else {
            echo "<script>alert('Error adding supply, please try again.');</script>";
        }
    } elseif (isset($_POST["action"]) && $_POST["action"] === "add_category") {
        $categoryObj->name = $_POST["category_name"];
        if ($categoryObj->addSupplyCategory()) {
            header("Location: index.php?page=system-database-configuration");
            exit();
        } else {
            echo "<script>alert('Error adding category, please try again.');</script>";
        }
    }
}

$supplies = $supplyObj->viewAllSupply();
$categories = $categoryObj->getAllSupplyCategories();

?>

<!-- Add Supply Modal -->
<div class="modal-container" id="add-supply-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Supply</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="add_supply">
            <div class="form-group">
                <label for="name">Supply Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="category_id">Supply Category</label>
                <select name="category_id" id="category_id" required>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?= $category["id"]; ?>"><?= $category["name"]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="unit_of_supply">Unit Of Supply (e.g. Box, Piece, Ream)</label>
                <input type="text" id="unit_of_supply" name="unit_of_supply" required>
            </div>
            <div class="form-group">
                <label for="price_per_unit">Price Per Unit</label>
                <input type="number" step="0.01" id="price_per_unit" name="price_per_unit" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Initial Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" required>
            </div>
            <button type="submit" class="submit-button">Add Supply</button>
        </form>
    </div>
</div>

<!-- Add Supply Category Modal -->
<div class="modal-container" id="add-category-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Supply Category</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="add_category">
            <div class="form-group">
                <label for="category_name">Supply Category Name</label>
                <input type="text" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="submit-button">Add Supply Category</button>
        </form>
    </div>
</div>

<!-- Supplies Table -->
<h2>Supplies</h2>
<button class="open-button" data-target="#add-supply-modal">Add Supply</button>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Supply Category</th>
            <th>Unit Of Supply</th>
            <th>Price Per Unit</th>
            <th>Stock Quantity</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($supplies as $supply) { ?>
            <tr>
                <td><?= htmlspecialchars($supply["id"]); ?></td>
                <td><?= htmlspecialchars($supply["name"]); ?></td>
                <td><?= htmlspecialchars($categoryObj->getSupplyCategoryById($supply["supply_categories_id"])["name"]); ?></td>
                <td><?= htmlspecialchars($supply["unit_of_supply"]); ?></td>
                <td><?= htmlspecialchars("₱" . number_format($supply["price_per_unit"], 2)); ?></td>
                <td><?= htmlspecialchars($supply["stock_quantity"]); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Supply Categories Table -->
<h2>Supply Categories</h2>
<button class="open-button" data-target="#add-category-modal">Add Supply Category</button>
<table border=1>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category) { ?>
            <tr>
                <td><?= htmlspecialchars($category["id"]); ?></td>
                <td><?= htmlspecialchars($category["name"]); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>