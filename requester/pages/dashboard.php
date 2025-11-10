<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/users.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$usersObj = new Users($pdoConnection);
$requester_id = $_SESSION['user_id'];

$statusCounts = $requestsObj->getRequestCountsByStatusForRequester($requester_id);
$topItems = $requestsObj->getTopRequestedItemsForRequester($requester_id);
$recentRequests = $requestsObj->getRecentRequestsByRequesterId($requester_id);

$ongoingCount = $statusCounts[RequestStatus::Pending->value] + $statusCounts[RequestStatus::Claimed->value] + $statusCounts[RequestStatus::Ready->value];
$totalFinished = $statusCounts[RequestStatus::Released->value] + $statusCounts[RequestStatus::Denied->value];
$successRate = $totalFinished > 0 ? round(($statusCounts[RequestStatus::Released->value] / $totalFinished) * 100) : 0;

$topItemLabels = json_encode(array_column($topItems, 'name'));
$topItemData = json_encode(array_column($topItems, 'total_quantity'));
?>

<div id="dashboard-data"
     data-status-counts='<?= json_encode($statusCounts) ?>'
     data-top-items-labels='<?= $topItemLabels ?>'
     data-top-items-data='<?= $topItemData ?>'
     style="display: none;">
</div>

<div class="kpi-container">
    <div class="kpi-card">
        <div class="kpi-title">Ongoing Requests</div>
        <div class="kpi-value"><?= $ongoingCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Awaiting Pickup</div>
        <div class="kpi-value"><?= $statusCounts[RequestStatus::Ready->value] ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Total Finished</div>
        <div class="kpi-value"><?= $totalFinished ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Success Rate</div>
        <div class="kpi-value"><?= $successRate ?>%</div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Request Status Breakdown</h3>
        <div class="chart-container">
            <canvas id="statusPieChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Top 5 Most Requested Items</h3>
        <div class="chart-container">
            <canvas id="topItemsBarChart"></canvas>
        </div>
    </div>
</div>

<h2>Recent Activity</h2>
<table>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Processor Name</th>
            <th>Date Requested</th>
            <th>Date Finished</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($recentRequests)): ?>
            <tr class="empty-table-message">
                <td colspan="5">No recent requests to display.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($recentRequests as $request): ?>
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
                    <td><?= htmlspecialchars($request["status"]) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>