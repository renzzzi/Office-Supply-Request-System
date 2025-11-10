        </main>
    </div>

<script src="../assets/chart.min.js"></script>

<?php
    $currentPage = $_GET['page'] ?? 'dashboard';

    if ($currentPage === 'dashboard') {
        echo '<script src="../assets/processorDashboardCharts.js"></script>';
    }

    if ($currentPage === 'manage-requests') {
        echo '<script src="../assets/prepareSupplyListRequest.js"></script>';
        echo '<script src="../assets/viewRequestSuppliesDetails.js"></script>';
        echo '<script src="../assets/releaseModalValidation.js"></script>';
        echo '<script src="../assets/handleReportModal.js"></script>';
    }
?>

</body>
</html>