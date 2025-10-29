<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/supplies.php';

$term = $_GET['term'] ?? '';

// Don't search if the term is too short
if (strlen($term) < 1) { 
    echo json_encode([]);
    exit();
}

try {
    $pdo = (new Database())->connect();
    $suppliesObj = new Supplies($pdo);
    
    // Use the existing search method from your Supplies class
    // The fetchAll(PDO::FETCH_COLUMN) is perfect for this.
    $supplyNames = $suppliesObj->searchSupplyNames($term);

    // Output the array of names as a JSON string
    echo json_encode($supplyNames);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while searching for supplies.']);
}

exit();

?>