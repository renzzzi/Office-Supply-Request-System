<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Desk | Requester</title>
    <link rel="stylesheet" href="../assets/universal-style.css">
    <script src="../assets/modal.js" defer></script>
</head>
<body class="panel-body">
    <aside class="side-panel">
        <div class="logo-container">
            <a href="index.php?page=manage-requests">Supply Desk</a>
        </div>
        
        <nav class="side-navigation">
            <ul>
                <li><a href="index.php?page=manage-requests">Manage Requests</a></li>
                <li><a href="index.php?page=supply-inventory">Supply Inventory</a></li>
                <li><a href="index.php?page=settings">Settings</a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content-wrapper">
        <header class="top-bar">
            <div class="page-title">
                <h1>Processor Panel</h1>
            </div>
            <div>
                <span>Welcome, <?= htmlspecialchars($_SESSION["user_first_name"]); ?></span>
            </div>
            <div class="user-actions">
                <a href="../login/logout.php">Logout</a>
            </div>
        </header>
        <main class="content-area">