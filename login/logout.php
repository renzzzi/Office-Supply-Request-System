<?php

session_start();
require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/logs.php";

if (isset($_SESSION['user_id'])) {
    try {
        $pdoConnection = (new Database())->connect();
        $logsObj = new Logs($pdoConnection);
        $logsObj->logAction($_SESSION['user_id'], 'LOGOUT', "User logged out.");
    } catch (Exception $e) {
        // Continue to logout even if logging fails
    }
}

session_unset();
session_destroy();

header("Location: ../login");
exit();

?>