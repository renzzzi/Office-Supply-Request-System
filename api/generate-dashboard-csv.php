<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("Access Denied.");
}

require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/requests.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$requester_id = $_SESSION['user_id'];

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$filename = "dashboard_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

$statusCounts = $requestsObj->getRequestCountsByStatusForRequester($requester_id, $start_date, $end_date);
$topItems = $requestsObj->getTopRequestedItemsForRequester($requester_id, 5, $start_date, $end_date);
$allRequests = $requestsObj->getAllRequestsInDateRangeForRequester($requester_id, $start_date, $end_date);

$ongoingCount = $statusCounts[RequestStatus::Pending->value] + $statusCounts[RequestStatus::Claimed->value] + $statusCounts[RequestStatus::Ready->value];
$totalFinished = $statusCounts[RequestStatus::Released->value] + $statusCounts[RequestStatus::Denied->value];
$successRate = $totalFinished > 0 ? round(($statusCounts[RequestStatus::Released->value] / $totalFinished) * 100) : 0;
$dateRangeStr = ($start_date || $end_date) ? (($start_date ?: 'Start') . " to " . ($end_date ?: 'End')) : "All Time";

fputcsv($output, ['KPI Summary Report']);
fputcsv($output, ['Date Range', $dateRangeStr]);
fputcsv($output, []); 
fputcsv($output, ['Metric', 'Value']);
fputcsv($output, ['Ongoing Requests', $ongoingCount]);
fputcsv($output, ['Awaiting Pickup', $statusCounts[RequestStatus::Ready->value]]);
fputcsv($output, ['Total Finished', $totalFinished]);
fputcsv($output, ['Success Rate (%)', $successRate]);
fputcsv($output, []);

fputcsv($output, ['Request Status Breakdown']);
fputcsv($output, ['Status', 'Count']);
foreach ($statusCounts as $status => $count) {
    fputcsv($output, [$status, $count]);
}
fputcsv($output, []);

fputcsv($output, ['Top 5 Most Requested Items']);
fputcsv($output, ['Item Name', 'Total Quantity Requested']);
foreach ($topItems as $item) {
    fputcsv($output, [$item['name'], $item['total_quantity']]);
}
fputcsv($output, []); 

fputcsv($output, ['All Requests In Period']);
fputcsv($output, ['Request ID', 'Processor Name', 'Date Requested', 'Date Finished', 'Status']);
foreach ($allRequests as $request) {
    $processorName = (!empty($request["proc_first_name"])) ? $request["proc_first_name"] . " " . $request["proc_last_name"] : "N/A";
    fputcsv($output, [$request['id'], $processorName, $request['requested_date'], $request['finished_date'] ?? 'N/A', $request['status']]);
}

fclose($output);
exit();