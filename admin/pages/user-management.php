<?php

require_once '../classes/users.php';
require_once '../classes/roles.php';
require_once '../classes/departments.php';

$pdoConnection = (new Database())->connect();

$userObj = new Users($pdoConnection);
$users = $userObj->getAllUsers();

$roleObj = new Roles($pdoConnection);
$departmentObj = new Departments($pdoConnection);
?>

<button class="add-user-button" onclick="location.href='index.php?page=add-user'">Add User</button>
<table border="1" class="user-table">
    <thead>
        <tr>
            <th scope="col">User ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Department</th>
            <th scope="col">Role</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($users as $user) 
        {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user["id"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["first_name"]) . " " . htmlspecialchars($user["last_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["email"]) . "</td>";
            echo "<td>" . htmlspecialchars($departmentObj->getDepartmentById($user["departments_id"])["name"]) . "</td>";
            echo "<td>" . htmlspecialchars($roleObj->getRoleById($user["roles_id"])["name"]) . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<span class="user-count">Total Users: <?= count($users) ?></span>