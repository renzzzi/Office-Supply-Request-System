<?php

require_once '../classes/database.php';
require_once '../classes/users.php';
require_once '../classes/departments.php';
require_once '../classes/roles.php';

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

?>

<form action="" method="POST" class="add-user-form">
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
    <input type="submit" value="Add User" class="submit-button">
</form>