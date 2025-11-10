<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    die("Access Denied.");
}

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$processor_id = $_SESSION['user_id'];

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$myActiveCount = $requestsObj->getCountByProcessorIdAndStatuses($processor_id, [RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$myCompletedInRange = $requestsObj->getCountCompletedTodayByProcessor($processor_id, $start_date, $end_date);
$readyForPickupCount = $requestsObj->getCountByStatus(RequestStatus::Ready);
$topSystemItems = $requestsObj->getTopRequestedItemsSystemWide(5, $start_date, $end_date);

$dateRangeStr = ($start_date || $end_date) ? (htmlspecialchars($start_date ?: 'Start') . " to " . htmlspecialchars($end_date ?: 'End')) : "All Time / Today";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processor Dashboard Report</title>
    <link rel="stylesheet" href="../../assets/print-style.css">
</head>
<body>
    <div id="requests-report-container">
        <header id="report-header">
            <h1>Processor Dashboard Report</h1>
            <p><strong>Date Range:</strong> <?= $dateRangeStr ?></p>
            <p><strong>Report Generated On:</strong> <?= date("Y-m-d H:i:s") ?></p>
        </header>

        <h2>Key Performance Indicators</h2>
        <div class="kpi-summary-container">
            <div class="kpi-summary-card"><span>Pending (All)</span><strong><?= $pendingCount ?></strong></div>
            <div class="kpi-summary-card"><span>My Active</span><strong><?= $myActiveCount ?></strong></div>
            <div class="kpi-summary-card"><span>My Completed</span><strong><?= $myCompletedInRange ?></strong></div>
            <div class="kpi-summary-card"><span>Ready for Pickup</span><strong><?= $readyForPickupCount ?></strong></div>
        </div>

        <h2>Top 5 Most Requested Items (System-Wide)</h2>
        <table>
            <thead><tr><th>Item Name</th><th>Total Quantity Requested</th></tr></thead>
            <tbody>
                <?php if (empty($topSystemItems)): ?>
                    <tr><td colspan="2" style="text-align:center;">No items requested in this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($topSystemItems as $item): ?>
                    <tr><td><?= htmlspecialchars($item['name']) ?></td><td><?= htmlspecialchars($item['total_quantity']) ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="no-print"><button onclick="window.print()">Print Report</button></div>
    <script>window.onload = () => window.print();</script>
</body>
</html>