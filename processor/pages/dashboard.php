<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/users.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$usersObj = new Users($pdoConnection);
$processor_id = $_SESSION['user_id'];

$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$claimedCount = $requestsObj->getCountByProcessorIdAndStatus($processor_id, RequestStatus::Claimed);
$finishedToday = $requestsObj->getCountCompletedTodayByProcessor($processor_id);
$myReadyCount = $requestsObj->getCountByProcessorIdAndStatus($processor_id, RequestStatus::Ready);

$performanceLevel = $pendingCount > 0 ? round(($finishedToday / ($finishedToday + $pendingCount)) * 100) : ($finishedToday > 0 ? 100 : 0);

$recentActions = $requestsObj->getRecentActionsByProcessor($processor_id);

$workflowData = json_encode([
    'claimed' => $claimedCount, 
    'ready' => $myReadyCount, 
    'finished_today' => $finishedToday
]);

$rawWeeklyActivity = $requestsObj->getProcessorWeeklyActivity($processor_id);

$weeklyLabels = [];
$weeklyData = [];

for ($i = 6; $i >= 0; $i--) {
    $dateKey = date('Y-m-d', strtotime("-$i days"));
    $dateLabel = date('M d', strtotime("-$i days"));
    
    $weeklyLabels[] = $dateLabel;
    $weeklyData[] = $rawWeeklyActivity[$dateKey] ?? 0;
}

$weeklyLabelsJson = json_encode($weeklyLabels);
$weeklyDataJson = json_encode($weeklyData);
?>

<div id="dashboard-data"
     data-workflow-data='<?= $workflowData ?>'
     data-weekly-labels='<?= $weeklyLabelsJson ?>'
     data-weekly-data='<?= $weeklyDataJson ?>'
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
    <div class="kpi-card ongoing">
        <div class="kpi-title">Pending Requests (System-Wide)</div>
        <div class="kpi-value"><?= $pendingCount ?></div>
    </div>
    <div class="kpi-card claimed">
        <div class="kpi-title">Claimed Requests</div>
        <div class="kpi-value"><?= $claimedCount ?></div>
    </div>
    <div class="kpi-card finished">
        <div class="kpi-title">Finished Today</div>
        <div class="kpi-value"><?= $finishedToday ?></div>
    </div>
    <div class="kpi-card performance-level">
        <div class="kpi-title">Performance Level (Finished Today / (Finished Today + Pending))</div>
        <div class="kpi-value"><?= $performanceLevel ?>%</div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Request Status Breakdown</h3>
        <div class="chart-container">
            <canvas id="workflowBarChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>My Weekly Request Throughput (Last 7 Days)</h3>
        <div class="chart-container">
            <canvas id="weeklyThroughputChart"></canvas>
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