<?php

require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/users.php";
require_once __DIR__ . "/../../classes/departments.php";
require_once __DIR__ . "/../../classes/roles.php";

$pdoConnection = (new Database())->connect();
$userObj = new Users($pdoConnection);
$departmentObj = new Departments($pdoConnection);
$roleObj = new Roles($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userObj->first_name = $_POST["first_name"];
    $userObj->last_name = $_POST["last_name"];
    $userObj->email = $_POST["email"];
    $userObj->password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $userObj->departments_id = $_POST["department"];
    $userObj->roles_id = $_POST["role"];

    if ($userObj->addUser()) {
        header("Location: index.php?page=user-management");
        exit();
    } else {
        echo "<script>alert('Error adding user, please try again.');</script>";
    }
}

$users = $userObj->getAllUsers();
?>

<div class="modal-container">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New User</h2>
        <form action="index.php?page=user-management" method="POST" class="add-user-form">
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
                    <?php foreach ($departmentObj->getAllDepartments() as $department) { ?>
                        <option value="<?= $department["id"]; ?>"><?= $department["name"]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <?php foreach ($roleObj->getAllRoles() as $role) { ?>
                        <option value="<?= $role["id"]; ?>"><?= $role["name"]; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="submit-button">Add User</button>
        </form>
    </div>
</div>

<button class="open-button">Add User</button>
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
                $roleName = $roleObj->getRoleById($user["roles_id"])["name"];
            ?>
            <tr>
                <td><?= htmlspecialchars($user["id"]) ?></td>
                <td><?= htmlspecialchars($fullName) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars($departmentName) ?></td>
                <td><?= htmlspecialchars($roleName) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<span class="user-count">Total Users: <?= count($users) ?></span>