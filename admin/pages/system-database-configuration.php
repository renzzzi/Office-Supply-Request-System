<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";
require_once __DIR__ . "/../../classes/supply_categories.php";

$pdoConnection = (new Database())->connect();
$supplyObj = new Supplies($pdoConnection);
$categoryObj = new SupplyCategories($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplyObj->name = $_POST["name"];
    $supplyObj->supply_categories_id = $_POST["category_id"];
    $supplyObj->unit_of_supply = $_POST["unit_of_supply"];
    $supplyObj->price_per_unit = $_POST["price_per_unit"];

    if ($supplyObj->addSupply()) {
        header("Location: index.php?page=system-database-configuration");
        exit();
    } else {
        echo "<script>alert('Error adding supply, please try again.');</script>";
    }
}

$supplies = $supplyObj->viewAllSupply();
?>

<div class="modal-container">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Supply</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <div class="form-group">
                <label for="name">Supply Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id" required>
                    <?php foreach ($categoryObj->getAllSupplyCategories() as $category) { ?>
                        <option value="<?= $category["id"]; ?>"><?= $category["name"]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="unit_of_supply">Unit Of Supply (e.g., box, piece, ream)</label>
                <input type="text" id="unit_of_supply" name="unit_of_supply" required>
            </div>
            <div class="form-group">
                <label for="price_per_unit">Price Per Unit</label>
                <input type="number" step="0.01" id="price_per_unit" name="price_per_unit" required>
            </div>
            <button type="submit" class="submit-button">Add Supply</button>
        </form>
    </div>
</div>

<button class="open-button">Add Supply</button>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Unit Of Supply</th>
            <th>Price Per Unit</th>
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
            </tr>
        <?php } ?>
    </tbody>
</table>