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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["request_id"])) 
{
    $request_id = $_POST["request_id"];
    $action = $_POST["action"];

    if ($action === "claim") 
    {
        $requestsObj->setProcessorId($request_id, $_SESSION["user_id"]);
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Claimed);
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
        } else {
            $originalQuantitiesMap = [];
            foreach ($originalSupplies as $supply) {
                $originalQuantitiesMap[$supply['supplies_id']] = (int)$supply['supply_quantity'];
            }

            foreach ($submittedSupplies as $submittedId => $details) {
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
        
        if (!$isValid) {
            $_SESSION['form_error'] = $errorMessage;
            header("Location: index.php?page=manage-requests");
            exit();
        }

        foreach ($originalSupplies as $original) {
            $originalId = $original['supplies_id'];

            if (isset($submittedSupplies[$originalId]['enabled'])) {
                $newQuantity = $submittedSupplies[$originalId]['quantity'];
                $requestSuppliesObj->updateSupplyQuantity($request_id, $originalId, $newQuantity);
            } else {
                $requestSuppliesObj->removeSupplyFromRequest($request_id, $originalId);
            }
        }

        $requestsObj->updateRequestStatus($request_id, RequestStatus::Ready);
        $_SESSION['form_success'] = "Request #{$request_id} has been marked as Ready for Pickup.";

    }
    elseif ($action === "deny") 
    {
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Denied);
    }
    elseif ($action === "release") 
    {
        $suppliesToRelease = $requestSuppliesObj->getSuppliesByRequestId($request_id);

        foreach ($suppliesToRelease as $supply) {
            $suppliesObj->deductStock($supply['supplies_id'], $supply['supply_quantity']);
        }

        $requestsObj->setReleasedToId($request_id, $_POST["released_to_id"]);
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Released);
    }

    header("Location: index.php?page=manage-requests");
    exit();
}
?>

<!-- Supply Details Modal -->
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
            <tbody id="supply-details-tbody">
                <!-- Supply details will appear here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Prepare Supplies Modal -->
<div class="modal-container" id="prepare-supplies-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Prepare Supplies for Request</h2>
        <p>Uncheck items that are unavailable or adjust quantities as needed.</p>

        <form action="index.php?page=manage-requests" method="POST" id="prepare-supplies-form">
            <div id="prepare-supplies-list">
                <!-- Supplies will appear here -->
            </div>

            <p id="prepare-form-error" class="error-message error prepare-supplies-error" style="display: none;"></p>

            <input type="hidden" name="request_id" id="prepare-request-id">
            <input type="hidden" name="action" value="ready">

            <button type="submit" class="submit-button">Mark as Ready for Pickup</button>
        </form>
    </div>
</div>

<!-- Release Modal -->
<div class="modal-container" id="release-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Who are you releasing this to?</h2>

        <form action="index.php?page=manage-requests" method="POST">
            <div class="form-group">
                <label for="user-search">Search for User by Name</label>
                <input type="text" id="user-search" autocomplete="off" placeholder="Start typing a name...">
                <div id="user-search-results">
                    <!-- Search results will appear here -->
                </div>
            </div>

            <input type="hidden" id="released-to-user-id" name="released_to_id" required>
            <input type="hidden" name="request_id" id="release-request-id">
            <input type="hidden" name="action" value="release">

            <button type="submit" class="submit-button" id="release-submit-button" disabled>Release</button>
        </form>
    </div>
</div>

<!-- Pending Requests Table -->
<h2>Pending Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requested At</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByStatus(RequestStatus::Pending) as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) 
                {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) 
                    {
                        $departmentName = $department["name"];
                    }
                }

                $statusClass = "";
                switch ($request["status"]) 
                {
                    case RequestStatus::Pending->value:
                        $statusClass = "pending-status";
                        break;
                    case RequestStatus::Claimed->value:
                        $statusClass = "claimed-status";
                        break;
                    case RequestStatus::Ready->value:
                        $statusClass = "ready-status";
                        break;
                    case RequestStatus::Released->value:
                        $statusClass = "released-status";
                        break;
                    case RequestStatus::Denied->value:
                        $statusClass = "denied-status";
                        break;
                }

                /*
                    For displaying supply summary:
                    - If there are 2 or fewer supplies, list them all with quantities.
                    - If there are more than 2 supplies, list both supplies with quantity and indicate
                    ". . . and x more" where x is the number of the remaining supplies.
                */
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 

                if ($totalCount === 0) 
                {
                    $finalSummary = "No supplies";
                } 
                else 
                {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                
                    if ($totalCount > count($supplySummary)) 
                    {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>"
                    title="Click to view all supplies">
                        <?= $finalSummary ?>
                    </td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="claim" class="claim-button">Claim</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- Claimed Requests Table -->
<h2>My Claimed Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requested At</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Claimed At</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByProcessorIdAndStatus($_SESSION["user_id"], RequestStatus::Claimed) as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) 
                {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) 
                    {
                        $departmentName = $department["name"];
                    }
                }

                $statusClass = "";
                switch ($request["status"]) 
                {
                    case RequestStatus::Pending->value:
                        $statusClass = "pending-status";
                        break;
                    case RequestStatus::Claimed->value:
                        $statusClass = "claimed-status";
                        break;
                    case RequestStatus::Ready->value:
                        $statusClass = "ready-status";
                        break;
                    case RequestStatus::Released->value:
                        $statusClass = "released-status";
                        break;
                    case RequestStatus::Denied->value:
                        $statusClass = "denied-status";
                        break;
                }

                /*
                    For displaying supply summary:
                    - If there are 2 or fewer supplies, list them all with quantities.
                    - If there are more than 2 supplies, list both supplies with quantity and indicate
                    ". . . and x more" where x is the number of the remaining supplies.
                */
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 

                if ($totalCount === 0) 
                {
                    $finalSummary = "No supplies";
                } 
                else 
                {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                
                    if ($totalCount > count($supplySummary)) 
                    {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>"
                    title="Click to view all supplies">
                        <?= $finalSummary ?>
                    </td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <button type="button" class="open-button" 
                    data-target="#prepare-supplies-modal" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>">
                        Finalize Supply List
                    </button>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="deny" class="deny-button">Deny</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- Ready For Pickup Requests Table -->
<h2>Ready For Pickup Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requested At</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Claimed At</th>
            <th>Ready To Pickup At</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByProcessorIdAndStatus($_SESSION["user_id"], RequestStatus::Ready) as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) 
                {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) 
                    {
                        $departmentName = $department["name"];
                    }
                }

                $statusClass = "";
                switch ($request["status"]) 
                {
                    case RequestStatus::Pending->value:
                        $statusClass = "pending-status";
                        break;
                    case RequestStatus::Claimed->value:
                        $statusClass = "claimed-status";
                        break;
                    case RequestStatus::Ready->value:
                        $statusClass = "ready-status";
                        break;
                    case RequestStatus::Released->value:
                        $statusClass = "released-status";
                        break;
                    case RequestStatus::Denied->value:
                        $statusClass = "denied-status";
                        break;
                }

                /*
                    For displaying supply summary:
                    - If there are 2 or fewer supplies, list them all with quantities.
                    - If there are more than 2 supplies, list both supplies with quantity and indicate
                    ". . . and x more" where x is the number of the remaining supplies.
                */
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 

                if ($totalCount === 0) 
                {
                    $finalSummary = "No supplies";
                } 
                else 
                {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                
                    if ($totalCount > count($supplySummary)) 
                    {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>"
                    title="Click to view all supplies">
                        <?= $finalSummary ?>
                    </td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["ready_date"]) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($request["status"]) ?></td>
                <td><button class="open-button" data-target="#release-modal" 
                data-request-id="<?= htmlspecialchars($request["id"]) ?>">Release To</button></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Released Requests Table -->
<h2>Released Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requested At</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Claimed At</th>
            <th>Ready To Pickup At</th>
            <th>Finished At</th>
            <th>Released To</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByProcessorIdAndStatus($_SESSION["user_id"], RequestStatus::Released) as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) 
                {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) 
                    {
                        $departmentName = $department["name"];
                    }
                }

                $statusClass = "";
                switch ($request["status"]) 
                {
                    case RequestStatus::Pending->value:
                        $statusClass = "pending-status";
                        break;
                    case RequestStatus::Claimed->value:
                        $statusClass = "claimed-status";
                        break;
                    case RequestStatus::Ready->value:
                        $statusClass = "ready-status";
                        break;
                    case RequestStatus::Released->value:
                        $statusClass = "released-status";
                        break;
                    case RequestStatus::Denied->value:
                        $statusClass = "denied-status";
                        break;
                }

                /*
                    For displaying supply summary:
                    - If there are 2 or fewer supplies, list them all with quantities.
                    - If there are more than 2 supplies, list both supplies with quantity and indicate
                    ". . . and x more" where x is the number of the remaining supplies.
                */
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 

                if ($totalCount === 0) 
                {
                    $finalSummary = "No supplies";
                } 
                else 
                {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                
                    if ($totalCount > count($supplySummary)) 
                    {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>"
                    title="Click to view all supplies">
                        <?= $finalSummary ?>
                    </td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["ready_date"]) ?></td>
                <td><?= htmlspecialchars($request["finished_date"]) ?></td>
                <td><?= htmlspecialchars($usersObj->getUserById($request["released_to_id"])["first_name"] . " " . $usersObj->getUserById($request["released_to_id"])["last_name"]) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Denied Requests Table -->
<h2>Denied Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requested At</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Claimed At</th>
            <th>Finished At</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllRequestsByProcessorIdAndStatus($_SESSION["user_id"], RequestStatus::Denied) as $request): ?>
            <?php
                $requester = $usersObj->getUserById($request["requesters_id"]);
                $departmentName = "N/A";
                if ($requester) 
                {
                    $department = $departmentsObj->getDepartmentById($requester["departments_id"]);
                    if ($department) 
                    {
                        $departmentName = $department["name"];
                    }
                }

                $statusClass = "";
                switch ($request["status"]) 
                {
                    case RequestStatus::Pending->value:
                        $statusClass = "pending-status";
                        break;
                    case RequestStatus::Claimed->value:
                        $statusClass = "claimed-status";
                        break;
                    case RequestStatus::Ready->value:
                        $statusClass = "ready-status";
                        break;
                    case RequestStatus::Released->value:
                        $statusClass = "released-status";
                        break;
                    case RequestStatus::Denied->value:
                        $statusClass = "denied-status";
                        break;
                }

                /*
                    For displaying supply summary:
                    - If there are 2 or fewer supplies, list them all with quantities.
                    - If there are more than 2 supplies, list both supplies with quantity and indicate
                    ". . . and x more" where x is the number of the remaining supplies.
                */
                $supplySummary = $requestSuppliesObj->getSupplySummaryByRequestId($request["id"]);
                $totalCount = $requestSuppliesObj->getSupplyCountByRequestId($request["id"]);
                $finalSummary = ""; 

                if ($totalCount === 0) 
                {
                    $finalSummary = "No supplies";
                } 
                else 
                {
                    $summaryParts = [];
                    foreach ($supplySummary as $supply):
                        $summaryParts[] = htmlspecialchars($supply['name']) . ' (x' . htmlspecialchars($supply['supply_quantity']) . ')';
                    endforeach;
                    $finalSummary = implode("<br>", $summaryParts);
                
                    if ($totalCount > count($supplySummary)) 
                    {
                        $remainingCount = $totalCount - count($supplySummary);
                        $finalSummary .= "<br>" . "&nbsp;...and&nbsp;" . $remainingCount . "&nbsp;more";
                    }
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($request["id"]) ?></td>
                <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <?php if ($totalCount > 2): ?>
                    <td class="view-supplies-trigger" 
                    data-request-id="<?= htmlspecialchars($request['id']) ?>"
                    title="Click to view all supplies">
                        <?= $finalSummary ?>
                    </td>
                <?php else: ?>
                    <td><?= $finalSummary ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["finished_date"]) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="../assets/searchForUsers.js"></script>
<script src="../assets/prepareSupplyListRequest.js"></script>
<script src="../assets/viewRequestSuppliesDetails.js"></script>