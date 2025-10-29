<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/users.php";
require_once __DIR__ . "/../../classes/request_supplies.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$usersObj = new Users($pdoConnection);
$suppliesObj = new Supplies($pdoConnection);
$requestSupplyObj = new RequestSupplies($pdoConnection);

if (isset($_GET["term"])) {
    header("Content-Type: application/json");
    $searchTerm = $_GET["term"] ?? "";
    $suggestions = ($searchTerm !== "") ? $suppliesObj->searchSupplyNames($searchTerm) : [];
    echo json_encode($suggestions);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userInfo = $usersObj->getUserById($_SESSION["user_id"]);
    $departmentId = $userInfo["departments_id"];

    $requestsObj->requesters_id = $_SESSION["user_id"];
    $requestsObj->requested_date = date('Y-m-d H:i:s');
    if ($requestsObj->addRequest()) {
        $newRequestId = $pdoConnection->lastInsertId();
    } else {
        echo "<script>alert('Error adding request, please try again.');</script>";
    }
    
    foreach ($_POST['supplies'] as $supply) {
        $itemName = $supply['name'];
        $quantity = $supply['quantity'];
        $supplyDetails = $suppliesObj->getSupplyByName($itemName);

        $supplyId = $supplyDetails['id'];
        $requestSupplyObj->requests_id = $newRequestId;
        $requestSupplyObj->supplies_id = $supplyId;
        $requestSupplyObj->supply_quantity = $quantity;
        $requestSupplyObj->addRequestSupply();
    }
    
    header("Location: index.php?page=my-requests"); 
    exit();
}

?>

<button class="open-button">Add New Request</button>

<div class="modal-container">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>New Request</h2>

        <form class="new-request-form" onsubmit="return false;">
            <div class="form-group">
                <label for="item-name">Supply Name</label>
                <input type="text" id="item-name" name="item-name" required list="supply-suggestions" autocomplete="off">
                <datalist id="supply-suggestions">
                    <!-- Suggestions will be appear here -->
                </datalist>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity (Per Unit)</label>
                <input type="number" id="quantity" name="quantity" required min="1">
            </div>
            <button type="button" class="add-supply-name-button">Add Supply to List</button>
        </form>

        <hr>

        <form action="index.php?page=my-requests" method="POST" id="main-request-form">
            <h3>Added Supplies List</h3>
            <table border=1 class="request-table">
                <thead>
                    <tr>
                        <th>Supply Name</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="request-table-body">
                    <!-- Added rows will appear here -->
                </tbody>
            </table>
            <div id="hidden-inputs-container">
                <!-- Hidden inputs for supplies will appear here -->
            </div>
            <button type="submit" class="submit-request-button">Submit Request</button>
        </form>
    </div>
</div>

<h2>Existing Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Processor Name</th>
            <th>Date Requested</th>
            <th>Date Processed</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByRequesterId($_SESSION["user_id"]) as $request): ?>
            <?php
                $processorName = "N/A";
                if (!empty($request["processors_id"])) {
                    $processor = $usersObj->getUserById($request["processors_id"]);
                    if ($processor) {
                        $processorName = $processor["first_name"] . " " . $processor["last_name"];
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($processorName) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($request["processed_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="../assets/addRequestLiveTable.js"></script>
<script src="../assets/addRequestSupplySuggestion.js"></script>