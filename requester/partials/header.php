<?php

session_start()

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Desk | Requester</title>
    <link rel="stylesheet" href="../assets/universal-style.css">
</head>
<body class="panel-body">
    <aside class="side-panel">
        <div class="logo-container">
            <a href="index.php?page=dashboard">Supply Desk</a>
        </div>
        
        <nav class="side-navigation">
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=add-new-request">Add New Request</a></li>
                <li><a href="index.php?page=my-requests">My Requests</a></li>
                <li><a href="index.php?page=settings">Settings</a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content-wrapper">
        <header class="top-bar">
            <div class="page-title">
                <h1>Requester Panel</h1>
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