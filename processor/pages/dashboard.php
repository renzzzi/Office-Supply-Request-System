<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$processor_id = $_SESSION['user_id'];

$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$myActiveCount = $requestsObj->getCountByProcessorIdAndStatuses($processor_id, [RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$myCompletedToday = $requestsObj->getCountCompletedTodayByProcessor($processor_id); // This defaults to today
$readyForPickupCount = $requestsObj->getCountByStatus(RequestStatus::Ready);
$recentActions = $requestsObj->getRecentActionsByProcessor($processor_id);
$topSystemItems = $requestsObj->getTopRequestedItemsSystemWide();

$myClaimedCount = $requestsObj->getCountByProcessorIdAndStatus($processor_id, RequestStatus::Claimed);
$myReadyCount = $requestsObj->getCountByProcessorIdAndStatus($processor_id, RequestStatus::Ready);
$workflowData = json_encode(['claimed' => $myClaimedCount, 'ready' => $myReadyCount]);

$topSystemItemLabels = json_encode(array_column($topSystemItems, 'name'));
$topSystemItemData = json_encode(array_column($topSystemItems, 'total_quantity'));
?>

<div id="dashboard-data"
     data-workflow-data='<?= $workflowData ?>'
     data-top-system-items-labels='<?= $topSystemItemLabels ?>'
     data-top-system-items-data='<?= $topSystemItemData ?>'
     style="display: none;">
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Dashboard Report</h2>
        <p>Select a date range to generate a report of the dashboard's data. Leave blank for default data.</p>
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
                <button type="submit" id="print-report-btn" class="btn" data-action="pages/print-processor-dashboard-report.php">Print Report</button>
                <button type="submit" id="download-csv-btn" class="btn" data-action="../api/generate-processor-dashboard-csv.php">Download CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="page-controls">
    <button class="open-button" data-target="#report-modal">Generate Report</button>
</div>

<div class="kpi-container">
    <div class="kpi-card">
        <div class="kpi-title">Pending Requests</div>
        <div class="kpi-value"><?= $pendingCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">My Active Requests</div>
        <div class="kpi-value"><?= $myActiveCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">My Completed Today</div>
        <div class="kpi-value"><?= $myCompletedToday ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Ready for Pickup</div>
        <div class="kpi-value"><?= $readyForPickupCount ?></div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>My Workflow Breakdown</h3>
        <div class="chart-container">
            <canvas id="workflowBarChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Top 5 Requested Supplies (System-Wide)</h3>
        <div class="chart-container">
            <canvas id="systemTopItemsChart"></canvas>
        </div>
    </div>
</div>

<h2>Recent Request Actions</h2>
<table>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requester Name</th>
            <th>Date Requested</th>
            <th>Last Update</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($recentActions)): ?>
            <tr class="empty-table-message">
                <td colspan="5">You have no recent activity.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($recentActions as $request): ?>
                <tr class="<?= strtolower(str_replace(' ', '-', $request["status"])) ?>-status">
                    <td><?= htmlspecialchars($request["id"]) ?></td>
                    <td><?= htmlspecialchars($request["first_name"] . " " . $request["last_name"]) ?></td>
                    <td><?= htmlspecialchars($request["requested_date"]) ?></td>
                    <td><?= htmlspecialchars($request["updated_at"]) ?></td>
                    <td><?= htmlspecialchars($request["status"]) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>