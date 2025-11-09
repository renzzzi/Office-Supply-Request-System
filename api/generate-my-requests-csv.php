<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("You must be logged in to download this report.");
}

require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);

$report_type = $_GET['report_type'] ?? 'all';
$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$filename = "my_requests_report_{$report_type}_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Request ID', 'Processor Name', 'Date Requested', 'Date Claimed',
    'Date Ready For Pickup', 'Date Finished', 'Released To', 'Status'
]);

$requests = $requestsObj->getFilteredRequestsForRequester($_SESSION["user_id"], $report_type, $start_date, $end_date);

foreach ($requests as $request) {
    $processorName = (!empty($request["proc_first_name"])) ? $request["proc_first_name"] . " " . $request["proc_last_name"] : "N/A";
    
    $row = [
        $request["id"],
        $processorName,
        $request["requested_date"],
        $request["claimed_date"] ?? "N/A",
        $request["ready_date"] ?? "N/A",
        $request["finished_date"] ?? "N/A",
        $request["released_to"] ?? "N/A",
        $request["status"]
    ];

    fputcsv($output, $row);
}

fclose($output);
exit();