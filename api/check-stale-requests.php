<?php
session_start();

$now = time();
$last_check = $_SESSION['stale_check_time'] ?? 0;

if (($now - $last_check) < 3600) {
    echo json_encode(['status' => 'skipped', 'message' => 'Check already performed recently.']);
    exit;
}

require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/requests.php";
require_once __DIR__ . "/../classes/users.php";
require_once __DIR__ . "/../classes/notification.php";
require_once __DIR__ . "/../config.php";

header('Content-Type: application/json');

try {
    $pdoConnection = (new Database())->connect();
    $requestsObj = new Requests($pdoConnection);
    $usersObj = new Users($pdoConnection);
    $notification = new Notification($pdoConnection);

    $stale_requests = $requestsObj->getOldPendingRequests(REQUEST_STALE_HOURS);
    
    $notifications_sent = 0;

    if (!empty($stale_requests)) {
        $processors = $usersObj->getUsersByRole('Processor');
        
        $batch_limit = 5; 
        $count = 0;

        foreach ($stale_requests as $stale_id) {
            if ($count >= $batch_limit) break;

            $db_message = "Reminder: Request #{$stale_id} has been pending for over " . REQUEST_STALE_HOURS . " hours.";
            $link = "processor/index.php?page=manage-requests#pending-requests";
            $email_subject = "Stale Request Alert";
            $email_body = "<h2>Stale Request Alert</h2><p>{$db_message}</p><p><a href='http://localhost/Office-Supply-Request-System/{$link}'>View Pending Requests</a></p>";

            foreach ($processors as $processor) {
                $notification->createNotification($processor['id'], $db_message, $link, $processor['email'], $email_subject, $email_body);
            }
            
            $count++;
        }
        $notifications_sent = $count;
    }

    $_SESSION['stale_check_time'] = $now;

    echo json_encode([
        'status' => 'success', 
        'processed_count' => $notifications_sent,
        'total_stale' => count($stale_requests)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>