<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "Admin") {
    header("HTTP/1.1 401 Unauthorized");
    exit("Access Denied.");
}

require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/requests.php";
require_once __DIR__ . "/../classes/supplies.php";

$pdoConnection = (new Database())->connect();
$requestsObj = new Requests($pdoConnection);
$suppliesObj = new Supplies($pdoConnection);

$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$filename = "admin_dashboard_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

// Fetch Data
$pendingCount = $requestsObj->getCountByStatus(RequestStatus::Pending);
$activeCount = $requestsObj->getRequestCountByStatuses([RequestStatus::Claimed->value, RequestStatus::Ready->value]);
$completedToday = $requestsObj->getRequestCountCompletedToday();
$lowStockCount = $suppliesObj->getLowStockCount(5);
$volumeByDept = $requestsObj->getRequestVolumeByDepartment($start_date, $end_date);
$inventoryValue = $suppliesObj->getInventoryValueByCategory();
$dateRangeStr = ($start_date || $end_date) ? (($start_date ?: 'Start') . " to " . ($end_date ?: 'End')) : "All Time / Real-Time";

fputcsv($output, ['Admin Dashboard Report']);
fputcsv($output, ['Date Range for Volume Report', $dateRangeStr]);
fputcsv($output, []);

fputcsv($output, ['System Health KPIs (Real-Time)']);
fputcsv($output, ['Metric', 'Value']);
fputcsv($output, ['Total Pending Requests', $pendingCount]);
fputcsv($output, ['Total Active Requests', $activeCount]);
fputcsv($output, ['Requests Completed Today', $completedToday]);
fputcsv($output, ['Low-Stock Items', $lowStockCount]);
fputcsv($output, []);

fputcsv($output, ['Request Volume by Department']);
fputcsv($output, ['Department', 'Total Requests']);
foreach ($volumeByDept as $dept) {
    fputcsv($output, [$dept['name'], $dept['request_count']]);
}
fputcsv($output, []);

fputcsv($output, ['Inventory Value by Category (Real-Time)']);
fputcsv($output, ['Category', 'Total Value (PHP)']);
foreach ($inventoryValue as $cat) {
    fputcsv($output, [$cat['name'], $cat['total_value']]);
}

fclose($output);
exit();