<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "Admin") 
{
    header("Location: ../login");
    exit();
}

// Header
require_once "partials/header.php";

// Main
$page = $_GET["page"] ?? "user-management";
$pageToShow = "pages/" . $page . ".php";
require_once $pageToShow;

// Footer
require_once "partials/footer.php";
?>
