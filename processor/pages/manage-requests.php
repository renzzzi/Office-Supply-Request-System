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
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Ready);
    } 
    elseif ($action === "deny") 
    {
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Denied);
    }
    elseif ($action === "release") 
    {
        $requestsObj->setReleasedToId($request_id, $_POST["released_to_id"]);
        $requestsObj->updateRequestStatus($request_id, RequestStatus::Released);
    }

    header("Location: index.php?page=manage-requests");
    exit();
}

?>

<!-- Release Modal -->
<div class="modal-container" id="release-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Who are you releasing this to?</h2>

        <form action="index.php?page=manage-requests" method="POST">
            <div class="form-group">
                <label for="user-search">Search for User by Name</label>
                <input type="text" id="user-search" autocomplete="off" placeholder="Start typing a name...">
                <!-- Search results will appear here -->
                <div id="user-search-results"></div>
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
                <td><?= $finalSummary ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="claim">Claim</button>
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
                <td><?= $finalSummary ?></td>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit" name="action" value="ready">Ready For Pickup</button>
                        <button type="submit" name="action" value="deny">Deny</button>
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
                <td><?= $finalSummary ?></td>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["ready_date"]) ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
                <td><button class="open-button" data-target="#release-modal" 
                data-request-id="<?= htmlspecialchars($request["id"]) ?>">Release</button></td>
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
                <td><?= $finalSummary ?></td>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["ready_date"]) ?></td>
                <td><?= htmlspecialchars($request["finished_date"]) ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
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
                <td><?= htmlspecialchars($request["request_date"]) ?></td>
                <td><?= htmlspecialchars($requester ? $requester["first_name"] . " " . $requester["last_name"] : "N/A") ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <td><?= $finalSummary ?></td>
                <td><?= htmlspecialchars($request["claimed_date"]) ?></td>
                <td><?= htmlspecialchars($request["finished_date"]) ?></td>
                <td><?= htmlspecialchars($request["status"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="../assets/searchForUsers.js"></script>