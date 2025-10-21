<button class="open-button">Add New Request</button>

<div class="modal-container">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>New Request</h2>
        <form action="" method="POST" class="new-request-form">
            <div class="form-group">
                <label for="item_name">Supply Name</label>
                <input type="text" id="item_name" name="item_name" required>
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