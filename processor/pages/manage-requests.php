<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/request_supplies.php";
require_once __DIR__ . "/../../classes/users.php";
require_once __DIR__ . "/../../classes/departments.php";
require_once __DIR__ . "/../../classes/supplies.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$requestSuppliesObj = new RequestSupplies($pdoConnection);
$usersObj = new Users($pdoConnection);
$departmentsObj = new Departments($pdoConnection);
$suppliesObj = new Supplies($pdoConnection);
$errorRelease = "";
$current_processor_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["request_id"])) 
{
    $request_id = $_POST["request_id"];
    $action = $_POST["action"];

    if ($action === "claim") 
    {
        $requestsObj->setProcessorId($request_id, $current_processor_id);
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Claimed);
        header("Location: index.php?page=manage-requests#claimed-requests");
        exit();
    } 
    elseif ($action === "ready") 
    {
        $originalSupplies = $requestSuppliesObj->getSuppliesByRequestId($request_id);
        $submittedSupplies = $_POST['supplies'] ?? [];

        $isValid = true;
        $errorMessage = "";

        if (empty($submittedSupplies)) {
            $isValid = false;
            $errorMessage = "You must prepare at least one supply. Deny the request if none are available.";
        } 
        else 
        {
            $originalQuantitiesMap = [];
            foreach ($originalSupplies as $supply) 
            {
                $originalQuantitiesMap[$supply['supplies_id']] = (int)$supply['supply_quantity'];
            }

            foreach ($submittedSupplies as $submittedId => $details) 
            {
                $submittedQuantity = (int)$details['quantity'];

                if (!isset($originalQuantitiesMap[$submittedId])) {
                    $isValid = false;
                    $errorMessage = "An invalid supply was submitted that was not part of the original request.";
                    break;
                }

                $originalQuantity = $originalQuantitiesMap[$submittedId];

                if ($submittedQuantity <= 0) {
                    $isValid = false;
                    $errorMessage = "Supply quantity must be greater than zero.";
                    break;
                }

                if ($submittedQuantity > $originalQuantity) {
                    $isValid = false;
                    $errorMessage = "A submitted quantity exceeds the amount originally requested.";
                    break;
                }
            }
        }
        
        if (!$isValid) 
        {
            $_SESSION['form_error'] = $errorMessage;
            header("Location: index.php?page=manage-requests#claimed-requests");
            exit();
        }

        foreach ($originalSupplies as $original) 
        {
            $originalId = $original['supplies_id'];

            if (isset($submittedSupplies[$originalId]['enabled'])) 
            {
                $newQuantity = $submittedSupplies[$originalId]['quantity'];
                $requestSuppliesObj->updateSupplyQuantity($request_id, $originalId, $newQuantity);
            } 
            else 
            {
                $requestSuppliesObj->removeSupplyFromRequest($request_id, $originalId);
            }
        }

        $requestsObj->updateRequestStatus($request_id, RequestStatus::Ready);
        $_SESSION['form_success'] = "Request #{$request_id} has been marked as Ready for Pickup.";
        header("Location: index.php?page=manage-requests#ready-requests");
        exit();

    }
    elseif ($action === "deny") 
    {
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Denied);
        header("Location: index.php?page=manage-requests#finished-requests");
        exit();
    }
    elseif ($action === "release") 
    {
        if (empty(trim($_POST["released_to"])))
        {
            $errorRelease = "Please enter the receiver's name.";
        }
        else
        {
            $suppliesToRelease = $requestSuppliesObj->getSuppliesByRequestId($request_id);
    
            foreach ($suppliesToRelease as $supply) 
            {
                $suppliesObj->deductStock($supply['supplies_id'], $supply['supply_quantity'], $request_id);
            }
        
            $requestsObj->setReleasedTo($request_id, $_POST["released_to"]);
            $requestsObj->updateRequestStatus($request_id, RequestStatus::Released);

            header("Location: index.php?page=manage-requests#finished-requests");
            exit();
        }
    }
}

$records_per_page = 5;

// Pagination for Pending
$page_pending = isset($_GET['page_pending']) && is_numeric($_GET['page_pending']) ? (int)$_GET['page_pending'] : 1;
$offset_pending = ($page_pending - 1) * $records_per_page;
$total_pending = $requestsObj->getCountByStatus(RequestStatus::Pending);
$total_pages_pending = $total_pending > 0 ? ceil($total_pending / $records_per_page) : 1;
$pending_requests = $requestsObj->getPaginatedRequestsByStatus(RequestStatus::Pending, $records_per_page, $offset_pending);

// Pagination for Claimed
$page_claimed = isset($_GET['page_claimed']) && is_numeric($_GET['page_claimed']) ? (int)$_GET['page_claimed'] : 1;
$offset_claimed = ($page_claimed - 1) * $records_per_page;
$total_claimed = $requestsObj->getCountByProcessorIdAndStatus($current_processor_id, RequestStatus::Claimed);
$total_pages_claimed = $total_claimed > 0 ? ceil($total_claimed / $records_per_page) : 1;
$claimed_requests = $requestsObj->getPaginatedRequestsByProcessorIdAndStatus($current_processor_id, RequestStatus::Claimed, $records_per_page, $offset_claimed);

// Pagination for Ready
$page_ready = isset($_GET['page_ready']) && is_numeric($_GET['page_ready']) ? (int)$_GET['page_ready'] : 1;
$offset_ready = ($page_ready - 1) * $records_per_page;
$total_ready = $requestsObj->getCountByProcessorIdAndStatus($current_processor_id, RequestStatus::Ready);
$total_pages_ready = $total_ready > 0 ? ceil($total_ready / $records_per_page) : 1;
$ready_requests = $requestsObj->getPaginatedRequestsByProcessorIdAndStatus($current_processor_id, RequestStatus::Ready, $records_per_page, $offset_ready);

// Unified Pagination for Finished
$page_finished = isset($_GET['page_finished']) && is_numeric($_GET['page_finished']) ? (int)$_GET['page_finished'] : 1;
$offset_finished = ($page_finished - 1) * $records_per_page;
$finished_statuses = [RequestStatus::Released->value, RequestStatus::Denied->value];
$total_finished = $requestsObj->getCountByProcessorIdAndStatuses($current_processor_id, $finished_statuses);
$total_pages_finished = $total_finished > 0 ? ceil($total_finished / $records_per_page) : 1;
$finished_requests = $requestsObj->getPaginatedRequestsByProcessorIdAndStatuses($current_processor_id, $finished_statuses, $records_per_page, $offset_finished);
?>

<div class="modal-container" id="supply-details-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2 id="supply-details-title">Full Supply List</h2>
        <table border="1" style="width: 100%;">
            <thead>
                <tr>
                    <th>Supply Name</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody id="supply-details-tbody"></tbody>
        </table>
    </div>
</div>

<div class="modal-container" id="prepare-supplies-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Prepare Supplies for Request</h2>
        <p>Uncheck items that are unavailable or adjust quantities as needed.</p>
        <form action="index.php?page=manage-requests" method="POST" id="prepare-supplies-form">
            <div id="prepare-supplies-list"></div>
            <p id="prepare-form-error" class="error-message error prepare-supplies-error" style="display: none;"></p>
            <input type="hidden" name="request_id" id="prepare-request-id">
            <input type="hidden" name="action" value="ready">
            <button type="submit" class="submit-button">Mark as Ready for Pickup</button>
        </form>
    </div>
</div>

<div class="modal-container" id="release-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Who are you releasing this to?</h2>
        <form action="index.php?page=manage-requests" method="POST" id="release-form">
            <div class="form-group">
                <label for="receiver-input">Name of the receiver</label>
                <input type="text" id="receiver-input" name="released_to" placeholder="Name">
            </div>
            <p class="error-message error" id="release-error-message" style="display: none;"></p>
            <input type="hidden" name="request_id" id="release-request-id">
            <input type="hidden" name="action" value="release">
            <button type="submit" class="submit-button">Release</button>
        </form>
    </div>
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Processor Request Report</h2>
        <form id="report-form" method="GET" target="_blank">
            <div class="form-group">
                <label for="report-type">Request Status</label>
                <select id="report-type" name="report_type">
                    <option value="all">Pending & Processed (Claimed, Ready For Pickup, Finished)</option>
                    <option value="pending">Pending</option>
                    <option value="processed">Processed (Claimed, Ready For Pickup, Finished)</option>
                    <option value="claimed">Claimed</option>
                    <option value="ready">Ready For Pickup</option>
                    <option value="finished">Finished</option>
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
                <button type="submit" id="print-report-btn" class="btn" data-action="pages/print-processor-report.php">Print Report</button>
                <button type="submit" id="download-csv-btn" class="btn" data-action="../api/generate-processor-requests-csv.php">Download CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="page-controls">
    <button class="open-button" data-target="#report-modal">Generate Request Report</button>
</div>

<h2 id="pending-requests">Pending Requests</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Requested At</th>
            <th>Requester</th>
            <th>Department</th>
            <th>Supplies (Summary)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($pending_requests)): ?>
            <tr class="empty-table-message"><td colspan="6">No pending requests.</td></tr>
        <?php else: ?>
        <?php foreach ($pending_requests as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) $departmentName = $department["name"];
                }
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 
                if ($totalCount === 0) {
                    $finalSummary = "No supplies";
                } else {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                    if ($totalCount > count($supplySummary)) {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr class="pending-status">
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" data-request-id="<?= htmlspecialchars($request['id']) ?>" title="Click to view all supplies"><?= $finalSummary ?></td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="claim" class="claim-button">Claim</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($total_pages_pending > 1): ?>
<div class="pagination-controls">
    <a href="?page=manage-requests&page_pending=<?= $page_pending - 1 ?>#pending-requests" class="btn <?= $page_pending <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_pending ?> of <?= $total_pages_pending ?></span>
    <a href="?page=manage-requests&page_pending=<?= $page_pending + 1 ?>#pending-requests" class="btn <?= $page_pending >= $total_pages_pending ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>


<h2 id="claimed-requests">My Claimed Requests</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Requested At</th>
            <th>Requester</th>
            <th>Supplies (Summary)</th>
            <th>Claimed At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($claimed_requests)): ?>
            <tr class="empty-table-message"><td colspan="6">You have no claimed requests.</td></tr>
        <?php else: ?>
        <?php foreach ($claimed_requests as $request): ?>
             <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 
                if ($totalCount === 0) {
                    $finalSummary = "No supplies";
                } else {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                    if ($totalCount > count($supplySummary)) {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr class="claimed-status">
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" data-request-id="<?= htmlspecialchars($request['id']) ?>" title="Click to view all supplies"><?= $finalSummary ?></td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td>
                    <button type="button" class="open-button" data-target="#prepare-supplies-modal" data-request-id="<?= htmlspecialchars($request['id']) ?>">Finalize</button>
                    <form action="index.php?page=manage-requests" method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="deny" class="deny-button">Deny</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($total_pages_claimed > 1): ?>
<div class="pagination-controls">
    <a href="?page=manage-requests&page_claimed=<?= $page_claimed - 1 ?>#claimed-requests" class="btn <?= $page_claimed <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_claimed ?> of <?= $total_pages_claimed ?></span>
    <a href="?page=manage-requests&page_claimed=<?= $page_claimed + 1 ?>#claimed-requests" class="btn <?= $page_claimed >= $total_pages_claimed ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>


<h2 id="ready-requests">My Ready For Pickup Requests</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Requester</th>
            <th>Supplies (Summary)</th>
            <th>Ready At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ready_requests)): ?>
            <tr class="empty-table-message"><td colspan="5">You have no ready for pickup requests.</td></tr>
        <?php else: ?>
        <?php foreach ($ready_requests as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 
                if ($totalCount === 0) {
                    $finalSummary = "No supplies";
                } else {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                    if ($totalCount > count($supplySummary)) {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr class="ready-for-pickup-status">
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" data-request-id="<?= htmlspecialchars($request['id']) ?>" title="Click to view all supplies"><?= $finalSummary ?></td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["ready_date"]) ?></td>
                <td><button class="open-button" data-target="#release-modal" data-request-id="<?= htmlspecialchars($request["id"]) ?>">Release</button></td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($total_pages_ready > 1): ?>
<div class="pagination-controls">
    <a href="?page=manage-requests&page_ready=<?= $page_ready - 1 ?>#ready-requests" class="btn <?= $page_ready <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_ready ?> of <?= $total_pages_ready ?></span>
    <a href="?page=manage-requests&page_ready=<?= $page_ready + 1 ?>#ready-requests" class="btn <?= $page_ready >= $total_pages_ready ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>


<h2 id="finished-requests">My Finished Requests</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Requester</th>
            <th>Supplies (Summary)</th>
            <th>Finished At</th>
            <th>Released To</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($finished_requests)): ?>
            <tr class="empty-table-message"><td colspan="6">You have no finished requests.</td></tr>
        <?php else: ?>
        <?php foreach ($finished_requests as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 
                if ($totalCount === 0) {
                    $finalSummary = "No supplies";
                } else {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                    if ($totalCount > count($supplySummary)) {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr class="<?= $request['status'] === 'Released' ? 'released-status' : 'denied-status' ?>">
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" data-request-id="<?= htmlspecialchars($request['id']) ?>" title="Click to view all supplies"><?= $finalSummary ?></td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["finished_date"]) ?></td>
                <td><?= htmlspecialchars($request["released_to"] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($total_pages_finished > 1): ?>
<div class="pagination-controls">
    <a href="?page=manage-requests&page_finished=<?= $page_finished - 1 ?>#finished-requests" class="btn <?= $page_finished <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_finished ?> of <?= $total_pages_finished ?></span>
    <a href="?page=manage-requests&page_finished=<?= $page_finished + 1 ?>#finished-requests" class="btn <?= $page_finished >= $total_pages_finished ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>