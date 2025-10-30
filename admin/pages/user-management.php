<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/users.php";
require_once __DIR__ . "/../../classes/departments.php";

$pdoConnection = (new Database())->connect();
$userObj = new Users($pdoConnection);
$departmentObj = new Departments($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "add_user") {
        $userObj->first_name = $_POST["first_name"];
        $userObj->last_name = $_POST["last_name"];
        $userObj->email = $_POST["email"];
        $userObj->password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $userObj->departments_id = $_POST["department"];
        $userObj->role = $_POST["role"];

        if ($userObj->addUser()) {
            header("Location: index.php?page=user-management");
            exit();
        } else {
            echo "<script>alert('Error adding user, please try again.');</script>";
        }
    } elseif (isset($_POST["action"]) && $_POST["action"] === "add_department") {
        $departmentObj->name = $_POST["department_name"];
        if ($departmentObj->addDepartment()) {
            header("Location: index.php?page=user-management");
            exit();
        } else {
            echo "<script>alert('Error adding department, please try again.');</script>";
        }
    }
}

$users = $userObj->getAllUsers();
$departments = $departmentObj->getAllDepartments();
?>

<!-- Add User Modal -->
<div class="modal-container" id="add-user-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New User</h2>
        <form action="index.php?page=user-management" method="POST" class="add-user-form">
            <input type="hidden" name="action" value="add_user">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <select name="department" id="department" required>
                    <?php foreach ($departments as $department) { ?>
                        <option value="<?= $department["id"]; ?>"><?= $department["name"]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="Requester">Requester</option>
                    <option value="Processor">Processor</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="submit-button">Add User</button>
        </form>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal-container" id="add-department-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Department</h2>
        <form action="index.php?page=user-management" method="POST" class="add-department-form">
            <input type="hidden" name="action" value="add_department">
            <div class="form-group">
                <label for="department_name">Department Name</label>
                <input type="text" id="department_name" name="department_name" required>
            </div>
            <button type="submit" class="submit-button">Add Department</button>
        </form>
    </div>
</div>

<!-- Users Table -->
<h2>Users</h2>
<button class="open-button" data-target="#add-user-modal">Add User</button>
<table border="1" class="user-table">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <?php
                $fullName = $user["first_name"] . " " . $user["last_name"];
                $departmentName = $departmentObj->getDepartmentById($user["departments_id"])["name"];
            ?>
            <tr>
                <td><?= htmlspecialchars($user["id"]) ?></td>
                <td><?= htmlspecialchars($fullName) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <td><?= htmlspecialchars($user["role"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<span class="user-count">Total Users: <?= count($users) ?></span>


<!-- Departments Modal -->
<h2>Departments</h2>
<button class="open-button" data-target="#add-department-modal">Add Department</button>
 <table border=1>
    <thead>
        <tr>
            <th>Department ID</th>
            <th>Department Name</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($departments as $department): ?>
            <tr>
                <td><?= htmlspecialchars($department["id"]) ?></td>
                <td><?= htmlspecialchars($department["name"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
 </table>