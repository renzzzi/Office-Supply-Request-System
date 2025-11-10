<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "Admin") {
    die("Access Denied.");
}

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";
require_once __DIR__ . "/../../classes/supplies.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$suppliesObj = new Supplies($pdoConnection);

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

// Fetch data for the report
$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$activeCount = $requestsObj->getRequestCountByStatuses([RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$completedToday = $requestsObj->getRequestCountCompletedToday();
$lowStockCount = $suppliesObj->getLowStockCount(5);
$volumeByDept = $requestsObj->getRequestVolumeByDepartment($start_date, $end_date);
$inventoryValue = $suppliesObj->getInventoryValueByCategory();

$dateRangeStr = ($start_date || $end_date) ? (htmlspecialchars($start_date ?: 'Start') . " to " . htmlspecialchars($end_date ?: 'End')) : "All Time / Real-Time";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard Report</title>
    <link rel="stylesheet" href="../../assets/print-style.css">
</head>
<body>
    <div id="requests-report-container">
        <header id="report-header">
            <h1>Admin Dashboard Report</h1>
            <p><strong>Date Range:</strong> <?= $dateRangeStr ?></p>
            <p><strong>Report Generated On:</strong> <?= date("Y-m-d H:i:s") ?></p>
        </header>

        <h2>System Health KPIs (Real-Time)</h2>
        <div class="kpi-summary-container">
            <div class="kpi-summary-card"><span>Total Pending</span><strong><?= $pendingCount ?></strong></div>
            <div class="kpi-summary-card"><span>Total Active</span><strong><?= $activeCount ?></strong></div>
            <div class="kpi-summary-card"><span>Completed Today</span><strong><?= $completedToday ?></strong></div>
            <div class="kpi-summary-card"><span>Low-Stock Items</span><strong><?= $lowStockCount ?></strong></div>
        </div>

        <h2>Request Volume by Department</h2>
        <table>
            <thead><tr><th>Department</th><th>Total Requests</th></tr></thead>
            <tbody>
                <?php if (empty($volumeByDept)): ?>
                    <tr><td colspan="2" style="text-align:center;">No requests in this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($volumeByDept as $dept): ?>
                    <tr><td><?= htmlspecialchars($dept['name']) ?></td><td><?= htmlspecialchars($dept['request_count']) ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Inventory Value by Category (Real-Time)</h2>
        <table>
            <thead><tr><th>Category</th><th>Total Inventory Value</th></tr></thead>
            <tbody>
                 <?php foreach ($inventoryValue as $cat): ?>
                    <tr><td><?= htmlspecialchars($cat['name']) ?></td><td><?= htmlspecialchars("₱" . number_format($cat['total_value'], 2)) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="no-print"><button onclick="window.print()">Print Report</button></div>
    <script>window.onload = () => window.print();</script>
</body>
</html>