<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    die("Access Denied. You must be logged in to view this page.");
}

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);

$report_type = $_GET['report_type'] ?? 'all';
$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$requests = $requestsObj->getFilteredRequestsForRequester($_SESSION["user_id"], $report_type, $start_date, $end_date);

$reportTitle = "All My Requests";
if ($report_type === 'in_progress') {
    $reportTitle = "My Ongoing Requests";
} elseif ($report_type === 'finished') {
    $reportTitle = "My Finished Requests";
}

$dateRangeStr = "All Time";
if ($start_date && $end_date) {
    $dateRangeStr = htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date);
} elseif ($start_date) {
    $dateRangeStr = "From " . htmlspecialchars($start_date);
} elseif ($end_date) {
    $dateRangeStr = "Until " . htmlspecialchars($end_date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report: <?= htmlspecialchars($reportTitle) ?></title>
    <link rel="stylesheet" href="../../assets/print-style.css">
</head>
<body>
    <div id="requests-report-container">
        <header id="report-header">
            <h1><?= htmlspecialchars($reportTitle) ?></h1>
            <p><strong>Date Range:</strong> <?= $dateRangeStr ?></p>
            <p><strong>Report Generated On:</strong> <?= date("Y-m-d H:i:s") ?></p>
        </header>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Processor</th>
                    <th>Requested</th>
                    <th>Finished</th>
                    <th>Released To</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No records found for the selected criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['id']) ?></td>
                            <td><?= (!empty($request["proc_first_name"])) ? htmlspecialchars($request["proc_first_name"] . " " . $request["proc_last_name"]) : "N/A" ?></td>
                            <td><?= htmlspecialchars(date("Y-m-d", strtotime($request['requested_date']))) ?></td>
                            <td><?= $request['finished_date'] ? htmlspecialchars(date("Y-m-d", strtotime($request['finished_date']))) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($request['released_to'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($request['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print Report</button>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>