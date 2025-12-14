        </main>
    </div>

<script src="../assets/chart.min.js"></script>
<script src="../assets/adminManagement.js"></script>
<script src="../assets/handleAlerts.js"></script>
<script src="../assets/notifications.js" defer></script>
<script src="../assets/handleDeleteModal.js" defer></script>

<?php
    $currentPage = $_GET['page'] ?? 'dashboard';

    if ($currentPage === 'dashboard') {
        echo '<script src="../assets/adminDashboardCharts.js"></script>';
        echo '<script src="../assets/handleReportModal.js"></script>';
    }
?>

</body>
</html>