        </main>
    </div>
<div class="modal-container" id="delete-confirm-modal">
    <div class="modal modal-sm">
        <span class="close-button">&times;</span>
        <h2>Confirm Deletion</h2>
        <p class="confirm-message"></p>
        <p class="error-message error modal-error-display" style="display: none; margin-top: 1rem;"></p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="entity_id" value="">
            <div class="modal-buttons">
                <button type="submit" class="deny-button">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/chart.min.js"></script>
<script src="../assets/adminManagement.js"></script>
<script src="../assets/handleAlerts.js"></script>

<?php
    $currentPage = $_GET['page'] ?? 'dashboard';

    if ($currentPage === 'dashboard') {
        echo '<script src="../assets/adminDashboardCharts.js"></script>';
        echo '<script src="../assets/handleReportModal.js"></script>';
    }
?>

</body>
</html>