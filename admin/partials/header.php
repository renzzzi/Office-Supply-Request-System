<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Desk | Admin</title>
    <link rel="stylesheet" href="../assets/universal-style.css">
    <script src="../assets/modal.js" defer></script>
</head>
<body class="panel-body">
    <aside class="side-panel">
        <div class="logo-container">
            <a href="index.php?page=inventory">Supply Desk</a>
        </div>
        
        <nav class="side-navigation">
            <ul>
                <li><a href="index.php?page=view-system-activity">View System Activity</a></li>
                <li><a href="index.php?page=user-management">User Management</a></li>
                <li><a href="index.php?page=system-database-configuration">System Database Configuration</a></li>
                <li><a href="index.php?page=settings">Settings</a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content-wrapper">
        <header class="top-bar">
            <div class="page-title">
                <h1>Admin Panel</h1>
            </div>
            <div>
                <span>Welcome, <?= htmlspecialchars($_SESSION["user_first_name"]); ?></span>
            </div>
            <div class="user-actions">
                <a href="#">Switch Account</a>
                <a href="../login">Logout</a>
            </div>
        </header>
        <main class="content-area">