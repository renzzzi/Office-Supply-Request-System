<?php
session_start();

// Security check: ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Access Denied. You must be logged in to view this page.");
}

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);

// Determine the report type from the URL
$report_type = $_GET['report_type'] ?? 'all';
$requests = $requestsObj->getFilteredRequestsForRequester($_SESSION["user_id"], $report_type);

// Set a dynamic title for the report
$reportTitle = "All My Requests";
switch ($report_type) {
    case 'completed_90_days':
        $reportTitle = "My Finished Requests (Last 90 Days)";
        break;
    case 'in_progress':
        $reportTitle = "My Ongoing Requests";
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report: <?= htmlspecialchars($reportTitle) ?></title>
    <link rel="stylesheet" href="../../assets/print-style.css" media="print">
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
        .no-print { text-align: center; margin-top: 20px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>

    <div id="requests-report-container">
        <h1><?= htmlspecialchars($reportTitle) ?></h1>
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
                        <td colspan="6" style="text-align:center;">No records found for this report.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['id']) ?></td>
                            <td><?= (!empty($request["proc_first_name"])) ? htmlspecialchars($request["proc_first_name"] . " " . $request["proc_last_name"]) : "N/A" ?></td>
                            <td><?= htmlspecialchars($request['requested_date']) ?></td>
                            <td><?= htmlspecialchars($request['finished_date'] ?? 'N/A') ?></td>
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