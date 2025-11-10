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
$processor_id = $_SESSION['user_id'];

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$filename = "processor_dashboard_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$myActiveCount = $requestsObj->getCountByProcessorIdAndStatuses($processor_id, [RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$myCompletedInRange = $requestsObj->getCountCompletedTodayByProcessor($processor_id, $start_date, $end_date);
$readyForPickupCount = $requestsObj->getCountByStatus(RequestStatus::Ready);
$topSystemItems = $requestsObj->getTopRequestedItemsSystemWide(5, $start_date, $end_date);
$allRequests = $requestsObj->getFilteredRequestsForProcessor($processor_id, 'all', $start_date, $end_date);

$dateRangeStr = ($start_date || $end_date) ? (($start_date ?: 'Start') . " to " . ($end_date ?: 'End')) : "All Time / Today";

fputcsv($output, ['KPI Summary Report']);
fputcsv($output, ['Date Range', $dateRangeStr]);
fputcsv($output, []); 
fputcsv($output, ['Metric', 'Value']);
fputcsv($output, ['Pending Requests (All)', $pendingCount]);
fputcsv($output, ['My Active Requests', $myActiveCount]);
fputcsv($output, ['My Completed in Range', $myCompletedInRange]);
fputcsv($output, ['Ready for Pickup (All)', $readyForPickupCount]);
fputcsv($output, []);

fputcsv($output, ['Top 5 Most Requested Items (System-Wide)']);
fputcsv($output, ['Item Name', 'Total Quantity Requested']);
foreach ($topSystemItems as $item) {
    fputcsv($output, [$item['name'], $item['total_quantity']]);
}
fputcsv($output, []); 

fputcsv($output, ['All Relevant Requests In Period']);
fputcsv($output, ['Request ID', 'Requester Name', 'Department', 'Processor Name', 'Date Requested', 'Date Finished', 'Status']);
foreach ($allRequests as $request) {
    $requesterName = $request['req_first_name'] . ' ' . $request['req_last_name'];
    $processorName = (!empty($request["proc_first_name"])) ? $request["proc_first_name"] . " " . $request["proc_last_name"] : "N/A";
    fputcsv($output, [$request['id'], $requesterName, $request['department_name'] ?? 'N/A', $processorName, $request['requested_date'], $request['finished_date'] ?? 'N/A', $request['status']]);
}

fclose($output);
exit();