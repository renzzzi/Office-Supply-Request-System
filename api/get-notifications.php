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

$query = "SELECT id, message, link, is_read, created_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unread_count_query = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = :user_id AND is_read = 0";
$unread_stmt = $db->prepare($unread_count_query);
$unread_stmt->bindParam(':user_id', $userId);
$unread_stmt->execute();
$unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);