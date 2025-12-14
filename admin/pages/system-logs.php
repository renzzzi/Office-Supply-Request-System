<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/logs.php";

$pdoConnection = (new Database())->connect();
$logsObj = new Logs($pdoConnection);

$records_per_page = 5;

$page_stock = isset($_GET['page_stock']) && is_numeric($_GET['page_stock']) ? (int)$_GET['page_stock'] : 1;
$offset_stock = ($page_stock - 1) * $records_per_page;
$total_stock = $logsObj->getStockLogsCount();
$total_pages_stock = $total_stock > 0 ? ceil($total_stock / $records_per_page) : 1;
$stockLogs = $logsObj->getStockLogs($records_per_page, $offset_stock);

$page_activity = isset($_GET['page_activity']) && is_numeric($_GET['page_activity']) ? (int)$_GET['page_activity'] : 1;
$offset_activity = ($page_activity - 1) * $records_per_page;
$total_activity = $logsObj->getActivityLogsCount();
$total_pages_activity = $total_activity > 0 ? ceil($total_activity / $records_per_page) : 1;
$activityLogs = $logsObj->getActivityLogs($records_per_page, $offset_activity);
?>

<h2 id="stock-logs">Stock Logs</h2>
<table>
    <thead>
        <tr>
            <th>Stock Log ID</th>
            <th>Supply Name (Per Unit)</th>
            <th>Change Amount</th>
            <th>New Quantity</th>
            <th>Reason</th>
            <th>Related Request ID</th>
            <th>Date Executed</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stockLogs as $log): ?>
            <tr>
                <td><?= $log['id'] ?></td>
                <td><?= htmlspecialchars($log['supply_name']) . ' (' . htmlspecialchars($log['unit_of_supply']) . ')' ?></td>
                <td><?= $log['change_amount'] > 0 ? '+' . $log['change_amount'] : $log['change_amount'] ?></td>
                <td><?= $log['new_quantity'] ?></td>
                <td><?= htmlspecialchars($log['reason']) ?></td>
                <td><?= $log['requests_id'] ? $log['requests_id'] : 'N/A' ?></td>
                <td><?= htmlspecialchars($log['changed_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($stockLogs)): ?>
            <tr class="empty-table-message"><td colspan="7">No stock logs found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages_stock > 1): ?>
<div class="pagination-controls">
    <a href="?page=system-logs&page_stock=<?= $page_stock - 1 ?>&page_activity=<?= $page_activity ?>#stock-logs" class="btn <?= $page_stock <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_stock ?> of <?= $total_pages_stock ?></span>
    <a href="?page=system-logs&page_stock=<?= $page_stock + 1 ?>&page_activity=<?= $page_activity ?>#stock-logs" class="btn <?= $page_stock >= $total_pages_stock ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>

<h2 id="activity-logs" style="margin-top: 40px;">Activity Logs</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action Type</th>
            <th>Message</th>
            <th>IP Address</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($activityLogs as $log): ?>
            <tr>
                <td><?= $log['id'] ?></td>
                <td>
                    <?php 
                        if ($log['users_id']) {
                            echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']);
                        } else {
                            echo 'System / Deleted User';
                        }
                    ?>
                </td>
                <td><?= htmlspecialchars($log['action_type']) ?></td>
                <td><?= htmlspecialchars($log['message']) ?></td>
                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($activityLogs)): ?>
            <tr class="empty-table-message"><td colspan="6">No activity logs found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages_activity > 1): ?>
<div class="pagination-controls">
    <a href="?page=system-logs&page_stock=<?= $page_stock ?>&page_activity=<?= $page_activity - 1 ?>#activity-logs" class="btn <?= $page_activity <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
    <span>Page <?= $page_activity ?> of <?= $total_pages_activity ?></span>
    <a href="?page=system-logs&page_stock=<?= $page_stock ?>&page_activity=<?= $page_activity + 1 ?>#activity-logs" class="btn <?= $page_activity >= $total_pages_activity ? 'disabled' : '' ?>">Next &raquo;</a>
</div>
<?php endif; ?>