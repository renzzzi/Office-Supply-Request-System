        </main>
    </div>

<script src="../assets/chart.min.js"></script>
<script src="../assets/notifications.js" defer></script>

<?php
    $currentPage = $_GET['page'] ?? 'dashboard';

    if ($currentPage === 'dashboard') {
        echo '<script src="../assets/requesterDashboardCharts.js"></script>';
        echo '<script src="../assets/handleReportModal.js"></script>';
    }
    elseif ($currentPage === 'my-requests') {
        echo '<script src="../assets/viewRequestSuppliesDetails.js"></script>';
        echo '<script src="../assets/handleNewRequestForm.js"></script>';
        echo '<script src="../assets/handleReportModal.js"></script>';
        echo '<script src="../assets/handleRequestCancellation.js"></script>';
    }
?>

</body>
</html>