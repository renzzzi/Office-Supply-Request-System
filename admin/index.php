<?php

// Header
require_once "partials/header.php";

// Main
$page = $_GET["page"] ?? "inventory-panel";
$pageToShow = "pages/" . $page . ".php";
require_once $pageToShow;

// Footer
require_once "partials/footer.php";
?>
