<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/users.php";

$term = $_GET["term"] ?? "";

if (strlen($term) < 1) 
{
    echo json_encode([]);
    exit();
}

$pdo = (new Database())->connect();
$usersObj = new Users($pdo);

$users = $usersObj->getUsersByName($term);
echo json_encode($users);

exit();

?>