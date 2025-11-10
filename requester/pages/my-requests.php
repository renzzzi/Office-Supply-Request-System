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

$records_per_page = 5;

$page_ongoing = isset($_GET['page_ongoing']) && is_numeric($_GET['page_ongoing']) ? (int)$_GET['page_ongoing'] : 1;
$offset_ongoing = ($page_ongoing - 1) * $records_per_page;
$ongoing_statuses = [RequestStatus::Pending->value, RequestStatus::Claimed->value, RequestStatus::Ready->value];
$total_ongoing = $requestsObj->getRequestCountByRequesterId($_SESSION['user_id'], $ongoing_statuses);
$total_pages_ongoing = $total_ongoing > 0 ? ceil($total_ongoing / $records_per_page) : 1;
$ongoing_requests = $requestsObj->getAllRequestsByRequesterId($_SESSION['user_id'], $ongoing_statuses, $records_per_page, $offset_ongoing);

$page_finished = isset($_GET['page_finished']) && is_numeric($_GET['page_finished']) ? (int)$_GET['page_finished'] : 1;
$offset_finished = ($page_finished - 1) * $records_per_page;
$finished_statuses = [RequestStatus::Released->value, RequestStatus::Denied->value];
$total_finished = $requestsObj->getRequestCountByRequesterId($_SESSION['user_id'], $finished_statuses);
$total_pages_finished = $total_finished > 0 ? ceil($total_finished / $records_per_page) : 1;
$finished_requests = $requestsObj->getAllRequestsByRequesterId($_SESSION['user_id'], $finished_statuses, $records_per_page, $offset_finished);

$errors = [];

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

<div class="modal-container" id="add-request-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>New Request</h2>

        <form class="new-request-form" onsubmit="return false;">
            <div class="form-group">
                <label for="item-name">Supply Name</label>
                <div id="supply-name-error" class="error-message error"></div>
                <input type="text" id="item-name" name="item-name" required autocomplete="off" placeholder="Start typing a supply name...">
                <div id="supply-search-results"></div>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity (Per Unit)</label>
                <div id="quantity-error" class="error-message error"></div>
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
                <tbody id="request-table-body"></tbody>
            </table>
            <div id="hidden-inputs-container"></div>
            <button type="submit" class="submit-request-button">Submit Request</button>
            <p id="main-request-error" class="error-message error"><?= $errors["supplies"] ?? "" ?></p>
        </form>
    </div>
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Request Report</h2>

        <form id="report-form" method="GET" target="_blank">
            <div class="form-group">
                <label for="report-type">Request Type</label>
                <select id="report-type" name="report_type">
                    <option value="all">All Requests</option>
                    <option value="in_progress">Ongoing Requests</option>
                    <option value="finished">Finished Requests</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start-date">Start Date (Optional)</label>
                <input type="date" id="start-date" name="start_date">
            </div>

            <div class="form-group">
                <label for="end-date">End Date (Optional)</label>
                <input type="date" id="end-date" name="end_date">
            </div>

            <div class="report-buttons">
                <button type="submit" id="print-report-btn" class="btn" data-action="pages/print-report.php">Print Report</button>
                <button type="submit" id="download-csv-btn" class="btn" data-action="../api/generate-my-requests-csv.php">Download CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="page-controls">
    <button class="open-button" data-target="#add-request-modal">Add New Request</button>
    <button class="open-button" data-target="#report-modal">Generate Request Report</button>
</div>

<div id="requests-tables-container">
    <h2>Ongoing Requests</h2>
    <table border=0>
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
            <?php if (empty($ongoing_requests)): ?>
                <tr class="empty-table-message">
                    <td colspan="6">No ongoing requests to display.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ongoing_requests as $request): ?>
                    <?php
                        $processorName = "N/A";
                        if (!empty($request["processors_id"])) {
                            $processor = $usersObj->getUserById($request["processors_id"]);
                            $processorName = $processor ? htmlspecialchars($processor["first_name"] . " " . $processor["last_name"]) : "N/A";
                        }
                    ?>
                    <tr class="<?= strtolower(str_replace(' ', '-', $request["status"])) ?>-status">
                        <td><?= htmlspecialchars($request["id"]) ?></td>
                        <td><?= $processorName ?></td>
                        <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                        <td><?= htmlspecialchars($request["claimed_date"] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($request["ready_date"] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($request["status"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages_ongoing > 1): ?>
    <div class="pagination-controls">
        <?php if ($page_ongoing <= 1): ?>
            <a class="btn disabled">&laquo; Prev</a>
        <?php else: ?>
            <a href="?page=my-requests&page_ongoing=<?= $page_ongoing - 1 ?>&page_finished=<?= $page_finished ?>" class="btn">&laquo; Prev</a>
        <?php endif; ?>
        <span>Page <?= $page_ongoing ?> of <?= $total_pages_ongoing ?></span>
        <?php if ($page_ongoing >= $total_pages_ongoing): ?>
            <a class="btn disabled">Next &raquo;</a>
        <?php else: ?>
            <a href="?page=my-requests&page_ongoing=<?= $page_ongoing + 1 ?>&page_finished=<?= $page_finished ?>" class="btn">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h2>Finished Requests</h2>
    <table border=0>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Processor Name</th>
                <th>Date Requested</th>
                <th>Date Finished</th>
                <th>Released To</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($finished_requests)): ?>
                <tr class="empty-table-message">
                    <td colspan="6">No finished requests to display.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($finished_requests as $request): ?>
                     <?php
                        $processorName = "N/A";
                        if (!empty($request["processors_id"])) {
                            $processor = $usersObj->getUserById($request["processors_id"]);
                            $processorName = $processor ? htmlspecialchars($processor["first_name"] . " " . $processor["last_name"]) : "N/A";
                        }
                    ?>
                    <tr class="<?= strtolower(str_replace(' ', '-', $request["status"])) ?>-status">
                        <td><?= htmlspecialchars($request["id"]) ?></td>
                        <td><?= $processorName ?></td>
                        <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                        <td><?= htmlspecialchars($request["finished_date"] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($request["released_to"] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($request["status"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages_finished > 1): ?>
    <div class="pagination-controls">
        <?php if ($page_finished <= 1): ?>
            <a class="btn disabled">&laquo; Prev</a>
        <?php else: ?>
            <a href="?page=my-requests&page_ongoing=<?= $page_ongoing ?>&page_finished=<?= $page_finished - 1 ?>" class="btn">&laquo; Prev</a>
        <?php endif; ?>
        <span>Page <?= $page_finished ?> of <?= $total_pages_finished ?></span>
        <?php if ($page_finished >= $total_pages_finished): ?>
            <a class="btn disabled">Next &raquo;</a>
        <?php else: ?>
            <a href="?page=my-requests&page_ongoing=<?= $page_ongoing ?>&page_finished=<?= $page_finished + 1 ?>" class="btn">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>