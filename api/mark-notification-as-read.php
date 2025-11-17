<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../classes/Database.php';

$db = (new Database())->connect();

$userId = $_SESSION["user_id"];

$query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update notifications.']);
}