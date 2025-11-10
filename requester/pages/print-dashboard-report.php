<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    die("Access Denied.");
}

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$requester_id = $_SESSION['user_id'];

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$statusCounts = $requestsObj->getRequestCountsByStatusForRequester($requester_id, $start_date, $end_date);
$topItems = $requestsObj->getTopRequestedItemsForRequester($requester_id, 5, $start_date, $end_date);

$ongoingCount = $statusCounts[RequestStatus::Pending->value] + $statusCounts[RequestStatus::Claimed->value] + $statusCounts[RequestStatus::Ready->value];
$totalFinished = $statusCounts[RequestStatus::Released->value] + $statusCounts[RequestStatus::Denied->value];
$successRate = $totalFinished > 0 ? round(($statusCounts[RequestStatus::Released->value] / $totalFinished) * 100) : 0;

$dateRangeStr = ($start_date || $end_date) ? (htmlspecialchars($start_date ?: 'Start') . " to " . htmlspecialchars($end_date ?: 'End')) : "All Time";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Report</title>
    <link rel="stylesheet" href="../../assets/print-style.css">
</head>
<body>
    <div id="requests-report-container">
        <header id="report-header">
            <h1>Requester Dashboard Report</h1>
            <p><strong>Date Range:</strong> <?= $dateRangeStr ?></p>
            <p><strong>Report Generated On:</strong> <?= date("Y-m-d H:i:s") ?></p>
        </header>

        <h2>Key Performance Indicators</h2>
        <div class="kpi-summary-container">
            <div class="kpi-summary-card"><span>Ongoing Requests</span><strong><?= $ongoingCount ?></strong></div>
            <div class="kpi-summary-card"><span>Awaiting Pickup</span><strong><?= $statusCounts[RequestStatus::Ready->value] ?></strong></div>
            <div class="kpi-summary-card"><span>Total Finished</span><strong><?= $totalFinished ?></strong></div>
            <div class="kpi-summary-card"><span>Success Rate</span><strong><?= $successRate ?>%</strong></div>
        </div>

        <h2>Request Status Breakdown</h2>
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                <?php foreach ($statusCounts as $status => $count): ?>
                <tr><td><?= htmlspecialchars($status) ?></td><td><?= $count ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Top 5 Most Requested Items</h2>
        <table>
            <thead><tr><th>Item Name</th><th>Total Quantity Requested</th></tr></thead>
            <tbody>
                <?php if (empty($topItems)): ?>
                    <tr><td colspan="2" style="text-align:center;">No items requested in this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($topItems as $item): ?>
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