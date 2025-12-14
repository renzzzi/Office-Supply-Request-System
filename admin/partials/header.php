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
            <a href="index.php?page=dashboard">Supply Desk</a>
        </div>
        
        <nav class="side-navigation">
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=user-management">User Management</a></li>
                <li><a href="index.php?page=system-database-configuration">System Database Configuration</a></li>
                <li><a href="index.php?page=system-logs">System Logs</a></li>
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
                <div class="notification-container">
                    <div class="notification-bell" id="notification-bell">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-badge" id="notification-badge"></span>
                    </div>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="dropdown-header">
                            <h3>Notifications</h3>
                        </div>
                        <div class="dropdown-body" id="notification-list">
                            <div class="notification-item-placeholder">Loading...</div>
                        </div>
                    </div>
                </div>
                <a href="../login/logout.php">Logout</a>
            </div>
        </header>
        <main class="content-area">