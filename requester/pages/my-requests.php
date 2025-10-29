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

$errors = [];

if (isset($_GET["term"])) {
    header("Content-Type: application/json");
    $searchTerm = $_GET["term"] ?? "";
    $suggestions = ($searchTerm !== "") ? $suppliesObj->searchSupplyNames($searchTerm) : [];
    echo json_encode($suggestions);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["supplies"]) || empty($_POST["supplies"])) {
        $errors["supplies"] = "Please add at least one supply to the request.";
    }

    if (empty(array_filter($errors))) {
        $userInfo = $usersObj->getUserById($_SESSION["user_id"]);
        $departmentId = $userInfo["departments_id"];

        $requestsObj->requesters_id = $_SESSION["user_id"];
        $requestsObj->requested_date = date("Y-m-d H:i:s");
        if ($requestsObj->addRequest()) {
            $newRequestId = $pdoConnection->lastInsertId();
        } else {
            echo "<script>alert('Error adding request, please try again.');</script>";
        }

        foreach ($_POST["supplies"] as $supply) {
            $itemName = $supply["name"];
            $quantity = $supply["quantity"];
            $supplyDetails = $suppliesObj->getSupplyByName($itemName);

            $supplyId = $supplyDetails["id"];
            $requestSupplyObj->requests_id = $newRequestId;
            $requestSupplyObj->supplies_id = $supplyId;
            $requestSupplyObj->supply_quantity = $quantity;
            $requestSupplyObj->addRequestSupply();
        }

        header("Location: index.php?page=my-requests"); 
        exit();
    }
}

?>

<!-- Add New Request Modal -->
<div class="modal-container" id="add-request-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>New Request</h2>

        <form class="new-request-form" onsubmit="return false;">
            <div class="form-group">
                <label for="item-name">Supply Name</label>
                <div id="supply-name-error" class="error-message"></div>
                <input type="text" id="item-name" name="item-name" required 
                list="supply-suggestions" autocomplete="off">
                <datalist id="supply-suggestions">
                    <!-- Suggestions will be appear here -->
                </datalist>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity (Per Unit)</label>
                <div id="quantity-error" class="error-message"></div>
                <input type="number" id="quantity" name="quantity" required 
                min="1">
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
            <p><?= $errors["supplies"] ?? "" ?></p>
        </form>
    </div>
</div>

<!-- Ongoing Requests Table -->
<h2>Ongoing Requests</h2>
<button class="open-button" data-target="#add-request-modal">Add New Request</button>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Processor Name</th>
            <th>Date Requested</th>
            <th>Date Claimed</th>
            <th>Date Ready For Pickup</th>
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
                <td><?= htmlspecialchars($request["claimed_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["ready_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Finished Requests Table -->
<h2>Finished Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Processor Name</th>
            <th>Date Requested</th>
            <th>Date Claimed</th>
            <th>Date Ready For Pickup</th>
            <th>Date Finished</th>
            <th>Released To</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByRequesterId($_SESSION["user_id"]) as $request): 
            if (!in_array($request["status"], [RequestStatus::Released->value, RequestStatus::Denied->value]))
                continue;
        ?>
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
                <td><?= htmlspecialchars($request["claimed_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["ready_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["finished_date"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["released_to_id"] ?? "N/A") ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="../assets/addRequestLiveTable.js"></script>
<script src="../assets/addRequestSupplySuggestion.js"></script>