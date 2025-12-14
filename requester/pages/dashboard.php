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

$ongoingCount = $statusCounts[RequestStatus::Pending->value] 
              + $statusCounts[RequestStatus::Claimed->value] 
              + $statusCounts[RequestStatus::Ready->value];

$readyCount = $statusCounts[RequestStatus::Ready->value];

$finishedToday = $requestsObj->getCountCompletedTodayByRequester($requester_id);

$serviceLevel = 0;
if ($ongoingCount > 0) {
    $serviceLevel = round(($finishedToday / $ongoingCount) * 100);
} elseif ($finishedToday > 0) {
    $serviceLevel = 100;
}

$topItemLabels = json_encode(array_column($topItems, 'name'));
$topItemData = json_encode(array_column($topItems, 'total_quantity'));
?>

<div id="dashboard-data"
     data-status-counts='<?= json_encode($statusCounts) ?>'
     data-top-items-labels='<?= $topItemLabels ?>'
     data-top-items-data='<?= $topItemData ?>'
     style="display: none;">
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Dashboard Report</h2>
        <form id="report-form" method="GET" target="_blank">
            <div class="form-group">
                <label for="start-date">Start Date (Optional)</label>
                <input type="date" id="start-date" name="start_date">
            </div>
            <div class="form-group">
                <label for="end-date">End Date (Optional)</label>
                <input type="date" id="end-date" name="end_date">
            </div>
            <div class="report-buttons">
                <button type="submit" id="print-report-btn" class="btn" data-action="pages/print-dashboard-report.php">Print Report</button>
                <button type="submit" id="download-csv-btn" class="btn" data-action="../api/generate-dashboard-csv.php">Download CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="page-controls">
    <button class="open-button" data-target="#report-modal">Generate Report</button>
</div>

<div class="kpi-container">
    <div class="kpi-card ongoing">
        <div class="kpi-title">Ongoing Requests</div>
        <div class="kpi-value"><?= $ongoingCount ?></div>
    </div>
    <div class="kpi-card ready">
        <div class="kpi-title">Ready For Pickup</div>
        <div class="kpi-value"><?= $readyCount ?></div>
    </div>
    <div class="kpi-card finished">
        <div class="kpi-title">Finished Today</div>
        <div class="kpi-value"><?= $finishedToday ?></div>
    </div>
    <div class="kpi-card service-level">
        <div class="kpi-title">Service Level (Finished Today / Ongoing)</div>
        <div class="kpi-value"><?= $serviceLevel ?>%</div>
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

<h2>Recent Request Activity</h2>
<table>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Processor Name</th>
            <th>Date Requested</th>
            <th>Last Update</th>
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
                    $processorName = 'N/A';
                    if (!empty($request["first_name"])) {
                        $processorName = htmlspecialchars($request["first_name"] . " " . $request["last_name"]);
                    }
                ?>
                <tr class="<?= strtolower(str_replace(' ', '-', $request["status"])) ?>-status">
                    <td><?= htmlspecialchars($request["id"]) ?></td>
                    <td><?= $processorName ?></td>
                    <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                    <td><?= htmlspecialchars($request["updated_at"]) ?></td>
                    <td><?= htmlspecialchars($request["status"]) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>