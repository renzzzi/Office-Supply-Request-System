<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/supplies.php';

$term = $_GET['term'] ?? '';

if (strlen($term) < 1) { 
    echo json_encode([]);
    exit();
}

try {
    $pdo = (new Database())->connect();
    $suppliesObj = new Supplies($pdo);
    
    $supplyNames = $suppliesObj->searchSupplyNames($term);

    echo json_encode($supplyNames);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while searching for supplies.']);
}

exit();

?>