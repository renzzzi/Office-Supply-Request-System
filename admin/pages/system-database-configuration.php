<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";
require_once __DIR__ . "/../../classes/supply_categories.php";

$pdoConnection = (new Database())->connect();
$supplyObj = new Supplies($pdoConnection);
$categoryObj = new SupplyCategories($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === "add_supply") {
        if (empty(trim($_POST['name'])) || empty(trim($_POST['unit_of_supply']))) {
            $_SESSION['error_message'] = "Supply name and unit cannot be empty.";
        } elseif ((float)$_POST['price_per_unit'] <= 0) {
            $_SESSION['error_message'] = "Price per unit must be greater than zero.";
        } else {
            $supplyObj->name = trim($_POST["name"]);
            $supplyObj->supply_categories_id = $_POST["supply_categories_id"];
            $supplyObj->unit_of_supply = trim($_POST["unit_of_supply"]);
            $supplyObj->price_per_unit = $_POST["price_per_unit"];
            $supplyObj->stock_quantity = $_POST["stock_quantity"];
            if ($supplyObj->addSupply()) {
                $_SESSION['success_message'] = "Supply added successfully.";
            }
        }
        header("Location: index.php?page=system-database-configuration"); exit();
    } elseif ($action === "edit_supply") {
        $supplyName = trim($_POST['name']);
        $unit = trim($_POST['unit_of_supply']);
        $price = (float)$_POST['price_per_unit'];

        if (empty($supplyName) || empty($unit)) {
            $_SESSION['error_message'] = "Supply name and unit cannot be empty.";
        } elseif ($price <= 0) {
             $_SESSION['error_message'] = "Price per unit must be greater than zero.";
        } else {
            if($supplyObj->updateSupply($_POST['entity_id'], $supplyName, $_POST['supply_categories_id'], $unit, $price)) {
                 $_SESSION['success_message'] = "Supply updated successfully.";
            }
        }
        header("Location: index.php?page=system-database-configuration"); exit();
    } elseif ($action === "adjust_stock") {
        $supplyId = $_POST['entity_id'];
        $changeAmount = (int)$_POST['change_amount'];
        $supply = $supplyObj->getSupplyById($supplyId);
        
        if (($supply['stock_quantity'] + $changeAmount) < 0) {
            $_SESSION['error_message'] = "Adjustment failed. This change would result in a negative stock quantity.";
        } else {
            if($supplyObj->updateStock($supplyId, $changeAmount, trim($_POST['reason']))) {
                $_SESSION['success_message'] = "Stock adjusted successfully.";
            }
        }
        header("Location: index.php?page=system-database-configuration"); exit();
    } elseif ($action === "delete_supply") {
        if($supplyObj->deleteSupply($_POST['entity_id'])) {
            $_SESSION['success_message'] = "Supply deleted successfully.";
        }
        header("Location: index.php?page=system-database-configuration"); exit();
    } elseif ($action === "add_category") {
        if (empty(trim($_POST['category_name']))) {
            $_SESSION['error_message'] = "Category name cannot be empty.";
        } else {
            $categoryObj->name = trim($_POST["category_name"]);
            if ($categoryObj->addSupplyCategory()) {
                 $_SESSION['success_message'] = "Category added successfully.";
            }
        }
        header("Location: index.php?page=system-database-configuration#categories-table"); exit();
    } elseif ($action === "edit_category") {
        $catName = trim($_POST['name']);
        if (empty($catName)) {
            $_SESSION['error_message'] = "Category name cannot be empty.";
        } else {
            if($categoryObj->updateSupplyCategory($_POST['entity_id'], $catName)) {
                $_SESSION['success_message'] = "Category updated successfully.";
            }
        }
        header("Location: index.php?page=system-database-configuration#categories-table"); exit();
    } elseif ($action === "delete_category") {
        if ($categoryObj->hasSupplies($_POST['entity_id'])) {
            $_SESSION['error_message'] = "Cannot delete category. Supplies are still assigned to it.";
        } else {
            $categoryObj->deleteSupplyCategory($_POST['entity_id']);
            $_SESSION['success_message'] = "Category deleted successfully.";
        }
        header("Location: index.php?page=system-database-configuration#categories-table"); exit();
    }
}

$supplies = $supplyObj->viewAllSupply();
$categories = $categoryObj->getAllSupplyCategories();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message']; ?>
        <span class="close-button">&times;</span>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?= $_SESSION['error_message']; ?>
        <span class="close-button">&times;</span>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="modal-container" id="add-supply-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Supply</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="add_supply">
            <div class="form-group"><label>Supply Name</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Supply Category</label>
                <select name="supply_categories_id" required><?php foreach ($categories as $c) { echo "<option value=\"{$c['id']}\">{$c['name']}</option>"; } ?></select>
            </div>
            <div class="form-group"><label>Unit (e.g. Box, Piece)</label><input type="text" name="unit_of_supply" required></div>
            <div class="form-group"><label>Price Per Unit</label><input type="number" step="0.01" name="price_per_unit" required></div>
            <div class="form-group"><label>Initial Stock</label><input type="number" name="stock_quantity" required></div>
            <button type="submit">Add Supply</button>
        </form>
    </div>
</div>

<div class="modal-container" id="edit-supply-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Edit Supply</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="edit_supply">
            <input type="hidden" name="entity_id" value="">
            <div class="form-group"><label>Supply Name</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Supply Category</label>
                <select name="supply_categories_id" required><?php foreach ($categories as $c) { echo "<option value=\"{$c['id']}\">{$c['name']}</option>"; } ?></select>
            </div>
            <div class="form-group"><label>Unit (e.g. Box, Piece)</label><input type="text" name="unit_of_supply" required></div>
            <div class="form-group"><label>Price Per Unit</label><input type="number" step="0.01" name="price_per_unit" required></div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<div class="modal-container" id="adjust-stock-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Adjust Stock</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="adjust_stock">
            <input type="hidden" name="entity_id" value="">
            <p><strong>Current Stock: </strong><span data-current-stock></span></p>
            <div class="form-group">
                <label>Change Amount (e.g., 50 to add, -10 to subtract)</label>
                <input type="number" name="change_amount" required>
            </div>
            <div class="form-group">
                <label>Reason for Adjustment</label>
                <input type="text" name="reason" placeholder="e.g., New shipment, Stock count correction" required>
            </div>
            <button type="submit">Apply Change</button>
        </form>
    </div>
</div>

<div class="modal-container" id="add-category-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add Supply Category</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="add_category">
            <div class="form-group"><label>Category Name</label><input type="text" name="category_name" required></div>
            <button type="submit">Add Category</button>
        </form>
    </div>
</div>

<div class="modal-container" id="edit-category-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Edit Supply Category</h2>
        <form action="index.php?page=system-database-configuration" method="POST">
            <input type="hidden" name="action" value="edit_category">
            <input type="hidden" name="entity_id" value="">
            <div class="form-group"><label>Category Name</label><input type="text" name="name" required></div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<h2 id="supplies-table">Supplies</h2>
<button class="open-button" data-target="#add-supply-modal">Add Supply</button>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($supplies as $supply): ?>
            <tr>
                <td><?= $supply["id"] ?></td>
                <td><?= htmlspecialchars($supply["name"]) ?></td>
                <td><?= htmlspecialchars($categoryObj->getSupplyCategoryById($supply["supply_categories_id"])["name"]) ?></td>
                <td><?= htmlspecialchars($supply["unit_of_supply"]) ?></td>
                <td><?= htmlspecialchars("₱" . number_format($supply["price_per_unit"], 2)) ?></td>
                <td><?= $supply["stock_quantity"] ?></td>
                <td>
                    <button class="open-button btn" data-target="#edit-supply-modal" data-modal-type="edit-supply" data-entity-id="<?= $supply['id'] ?>" data-entity-data='<?= htmlspecialchars(json_encode(['name' => $supply['name'], 'supply_categories_id' => $supply['supply_categories_id'], 'unit_of_supply' => $supply['unit_of_supply'], 'price_per_unit' => $supply['price_per_unit']]), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                    <button class="open-button btn" data-target="#adjust-stock-modal" data-modal-type="edit-adjust-stock" data-entity-id="<?= $supply['id'] ?>" data-entity-data='<?= htmlspecialchars(json_encode(['current_stock' => $supply['stock_quantity']]), ENT_QUOTES, 'UTF-8') ?>'>Adjust</button>
                    <form action="index.php?page=system-database-configuration" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this supply?');">
                        <input type="hidden" name="action" value="delete_supply"><input type="hidden" name="entity_id" value="<?= $supply['id'] ?>">
                        <button type="submit" class="deny-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2 id="categories-table">Supply Categories</h2>
<button class="open-button" data-target="#add-category-modal">Add Category</button>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= $category["id"] ?></td>
                <td><?= htmlspecialchars($category["name"]) ?></td>
                <td>
                    <button class="open-button btn" data-target="#edit-category-modal" data-modal-type="edit-category" data-entity-id="<?= $category['id'] ?>" data-entity-data='<?= htmlspecialchars(json_encode(['name' => $category['name']]), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                    <form action="index.php?page=system-database-configuration" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <input type="hidden" name="action" value="delete_category"><input type="hidden" name="entity_id" value="<?= $category['id'] ?>">
                        <button type="submit" class="deny-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>