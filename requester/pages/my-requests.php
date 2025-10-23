<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/supplies.php";

if (isset($_GET["term"])) 
{
    
    $database = new Database();
    $pdo = $database->connect();
    
    $suggestions = [];
    $searchTerm = $_GET["term"] ?? "";

    if ($searchTerm !== "") {
        $suppliesManager = new Supplies($pdo);
        $suggestions = $suppliesManager->searchSupplyNames($searchTerm);
    }

    header("Content-Type: application/json");
    echo json_encode($suggestions);
    exit();
}

?>

<button class="open-button">Add New Request</button>

<div class="modal-container">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>New Request</h2>
        <form action="" method="POST" class="new-request-form">
            <div class="form-group">
                <label for="item_name">Supply Name</label>
                <input type="text" id="item_name" name="item_name" required
                list="supply-suggestions" autocomplete="off">
                <datalist id="supply-suggestions">
                    <!-- Populate Here -->
                </datalist>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
        </form>
    </div>
</div>

<table border=1>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Item Name</th>
            <th>Request Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        
    </tbody>
</table>

<script>
    const supplyInput = document.querySelector('#item_name');
    const suggestionsList = document.querySelector('#supply-suggestions');

    supplyInput.addEventListener('input', function() {
        // call the handler directly so we don't get the site HTML wrapper from index.php
        const term = encodeURIComponent(supplyInput.value);
        fetch('pages/my-requests.php?term=' + term)
            .then(response => response.json())
            .then(data => {
                suggestionsList.innerHTML = '';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item;
                    suggestionsList.appendChild(option);
                });
            });
    });
</script>