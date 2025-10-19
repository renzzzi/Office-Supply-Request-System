<?php

require_once '../classes/users.php';
require_once '../classes/user_roles.php';

$pdoConnection = (new Database())->connect();

$userObj = new Users($pdoConnection);
$users = $userObj->getAllUsers();

$userRolesObj = new UserRoles($pdoConnection);


?>

<button class="add-user-button" onclick="location.href='index.php?page=add-user'">Add User</button>
<table border="1" class="user-table">
    <thead>
        <tr>
            <th scope="col">User ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Role</th>
        </tr>
    </thead>
    <tbody>
        <?php
        

        foreach ($users as $user) 
        {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            
            // highestRole stores the associative array of the highest role for the user
            $highestRole = $userRolesObj->getHighestRoleByUserId($user['id']);
            // roleName stores just the name of the highest role
            $roleName = $highestRole ? $highestRole["name"] : "No Role Assigned";
            echo "<td>" . htmlspecialchars($roleName) . "</td>";
            
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<span class="user-count">Total Users: <?= count($users) ?></span>