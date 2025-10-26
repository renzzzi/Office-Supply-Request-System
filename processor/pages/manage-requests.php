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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["request_id"])) {
    $requestsObj->setProcessorId($_POST["request_id"], $_SESSION["user_id"]);
    $requestsObj->modifyRequestStatus($_POST["request_id"], "in_progress");
    $requestsObj->updateProcessedDate($_POST["request_id"]);

    header("Location: index.php?page=manage-requests");
    exit();
}

?>

<h2>Unclaimed Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Date Requested</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllUnclaimedRequests() as $request): ?>
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
                <td><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit">Claim</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<h2>My Claimed Requests</h2>
<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Date Requested</th>
            <th>Requester Name</th>
            <th>Department</th>
            <th>Supply Requested (Summary)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requestsObj->getAllClaimedRequestsByProcessorId($_SESSION["user_id"]) as $request): ?>
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
                <td><?= htmlspecialchars($request["status"]) ?></td>
                <td>
                    <form action="index.php?page=manage-requests" method="POST">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request["id"]) ?>">
                        <button type="submit">Approve</button>
                        <button type="submit">Cancel</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>