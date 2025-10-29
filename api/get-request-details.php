<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/request_supplies.php';
require_once __DIR__ . '/../classes/supplies.php';

if (!isset($_GET['request_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID is required.']);
    exit();
}

$requestId = $_GET['request_id'];

try {
    $pdo = (new Database())->connect();
    $requestSuppliesObj = new RequestSupplies($pdo);
    
    $supplies = $requestSuppliesObj->getSuppliesByRequestId($requestId);

    echo json_encode($supplies);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching request details.']);
}

exit();

?>