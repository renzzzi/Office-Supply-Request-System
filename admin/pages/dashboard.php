<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/departments.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$departmentsObj = new Departments($pdoConnection);

$totalRequests = 0;
foreach (RequestStatus::cases() as $status) {
    $totalRequests += $requestsObj->getCountByStatus($status);
}

$releasedCount = $requestsObj->getCountByStatus(RequestStatus::Released);

$deniedCount = $requestsObj->getCountByStatus(RequestStatus::Denied);
$totalFinished = $releasedCount + $deniedCount;

$approvalRate = $totalRequests > 0 ? round(($totalFinished / $totalRequests) * 100) : 0;

$procStats = $requestsObj->getProcessorPerformanceToday();
$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);

$procLabels = [];
$procData = [];

foreach ($procStats as $stat) {
    $displayName = $stat['last_name'] . ', ' . $stat['first_name'];
    $finished = (int)$stat['finished_today'];

    $denominator = $finished + $pendingCount;
    $percentage = $denominator > 0 ? round(($finished / $denominator) * 100) : 0;
    
    $procLabels[] = $displayName;
    $procData[] = $percentage;
}

$procLabelsJson = json_encode($procLabels);
$procDataJson = json_encode($procData);

$allDepts = $requestsObj->getAllDepartmentsVolume(); 

$deptLabels = json_encode(array_column($allDepts, 'name'));
$deptData = json_encode(array_column($allDepts, 'request_count'));

$recentActivity = $requestsObj->getRecentSystemActivity(5);
?>

<div id="dashboard-data"
     data-proc-labels='<?= $procLabelsJson ?>'
     data-proc-data='<?= $procDataJson ?>'
     data-dept-labels='<?= $deptLabels ?>'
     data-dept-data='<?= $deptData ?>'
     style="display: none;">
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Admin Dashboard Report</h2>
        <p>Select a date range to generate a report.</p>
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
                <button type="submit" id="print-report-btn" class="btn" data-action="pages/print-admin-dashboard-report.php">Print Report</button>
                <button type="submit" id="download-csv-btn" class="btn" data-action="../api/generate-admin-dashboard-csv.php">Download CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="page-controls">
    <button class="open-button" data-target="#report-modal">Generate Report</button>
</div>

<div class="kpi-container">
    <div class="kpi-card">
        <div class="kpi-title">Total Requests (System-Wide)</div>
        <div class="kpi-value"><?= $totalRequests ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Total Released (System-Wide)</div>
        <div class="kpi-value"><?= $releasedCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Total Finished (System-Wide)</div>
        <div class="kpi-value"><?= $totalFinished ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Fulfillment Rate (Finished / Total)</div>
        <div class="kpi-value"><?= $approvalRate ?>%</div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Performance Level By Processor (Daily)</h3>
        <div class="chart-container">
            <canvas id="procPerformanceChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Request Volume by Department (All Time)</h3>
        <div class="chart-container">
            <canvas id="deptVolumeChart"></canvas>
        </div>
    </div>
</div>

<h2>System-Wide Activity Log</h2>
<table>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Requester</th>
            <th>Processor</th>
            <th>Last Update</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($recentActivity)): ?>
            <tr class="empty-table-message">
                <td colspan="5">No system activity to display.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($recentActivity as $activity): ?>
                <?php
                    $reqName = $activity['req_first_name'] . ' ' . $activity['req_last_name'];
                    
                    $procName = 'N/A';
                    if (!empty($activity['proc_first_name'])) {
                        $procName = $activity['proc_first_name'] . ' ' . $activity['proc_last_name'];
                    }
                ?>
                <tr class="<?= strtolower(str_replace(' ', '-', $activity["status"] ?? 'pending')) ?>-status">
                    <td><?= htmlspecialchars($activity["id"] ?? '') ?></td>
                    <td><?= htmlspecialchars($reqName) ?></td>
                    <td><?= htmlspecialchars($procName) ?></td>
                    <td><?= htmlspecialchars($activity["updated_at"] ?? '') ?></td>
                    <td><?= htmlspecialchars($activity["status"] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>