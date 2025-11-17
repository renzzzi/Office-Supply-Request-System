<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/supplies.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$suppliesObj = new Supplies($pdoConnection);

$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$activeCount = $requestsObj->getRequestCountByStatuses([RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$completedToday = $requestsObj->getRequestCountCompletedToday();
$lowStockCount = $suppliesObj->getLowStockCount(5);

$volumeByDept = $requestsObj->getRequestVolumeByDepartment();
$inventoryValue = $suppliesObj->getInventoryValueByCategory();
$recentActivity = $requestsObj->getRecentSystemActivity(5);

$deptLabels = json_encode(array_column($volumeByDept, 'name'));
$deptData = json_encode(array_column($volumeByDept, 'request_count'));
$invCategoryLabels = json_encode(array_column($inventoryValue, 'name'));
$invCategoryData = json_encode(array_column($inventoryValue, 'total_value'));
?>

<div id="dashboard-data"
     data-dept-labels='<?= $deptLabels ?>'
     data-dept-data='<?= $deptData ?>'
     data-inv-category-labels='<?= $invCategoryLabels ?>'
     data-inv-category-data='<?= $invCategoryData ?>'
     style="display: none;">
</div>

<div class="modal-container" id="report-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Generate Admin Dashboard Report</h2>
        <p>Select a date range to generate a report. Some KPIs like inventory value are always real-time.</p>
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
        <div class="kpi-title">Total Pending Request</div>
        <div class="kpi-value"><?= $pendingCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Active Requests</div>
        <div class="kpi-value"><?= $activeCount ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Requests Completed Today</div>
        <div class="kpi-value"><?= $completedToday ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Low-Stock Items</div>
        <div class="kpi-value"><?= $lowStockCount ?></div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Request Volume by Department</h3>
        <div class="chart-container">
            <canvas id="deptVolumeChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Inventory Value by Category</h3>
        <div class="chart-container">
            <canvas id="invValueChart"></canvas>
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
                    $requesterName = htmlspecialchars($activity['req_first_name'] . ' ' . $activity['req_last_name']);
                    $processorName = $activity['proc_first_name'] ? htmlspecialchars($activity['proc_first_name'] . ' ' . $activity['proc_last_name']) : 'N/A';
                ?>
                <tr class="<?= strtolower(str_replace(' ', '-', $activity["status"])) ?>-status">
                    <td><?= htmlspecialchars($activity["id"]) ?></td>
                    <td><?= $requesterName ?></td>
                    <td><?= $processorName ?></td>
                    <td><?= htmlspecialchars($activity["updated_at"]) ?></td>
                    <td><?= htmlspecialchars($activity["status"]) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>