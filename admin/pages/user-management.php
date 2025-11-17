<?php
require_once __DIR__ . "/../../classes/database.php";
require_once __DIR__ . "/../../classes/users.php";
require_once __DIR__ . "/../../classes/departments.php";
require_once __DIR__ . "/../../classes/notification.php";

$pdoConnection = (new Database())->connect();
$userObj = new Users($pdoConnection);
$departmentObj = new Departments($pdoConnection);

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $action = $_POST["action"] ?? '';
    
    if ($action === "add_user") 
    {
        if (empty(trim($_POST['first_name'])) || empty(trim($_POST['last_name'])) 
            || empty(trim($_POST['email'])) || empty($_POST['password'])) 
        {
            $_SESSION['error_message'] = "All fields are required when adding a user.";
        } 
        else 
        {
            $userObj->first_name = $_POST["first_name"];
            $userObj->last_name = $_POST["last_name"];
            $userObj->email = $_POST["email"];
            $userObj->password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $userObj->departments_id = $_POST["departments_id"];
            $userObj->role = $_POST["role"];
            if ($userObj->addUser()) 
            {
                $_SESSION['success_message'] = "User added successfully.";
            }
        }
        header("Location: index.php?page=user-management"); exit();
    } 
    elseif ($action === "edit_user") 
    {
        $userId = $_POST['entity_id'];
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $newRole = $_POST['role'];

        $oldUser = $userObj->getUserById($userId);
        $oldRole = $oldUser ? $oldUser['role'] : null;

        if (empty($firstName) || empty($lastName) || empty($email)) 
        {
            $_SESSION['error_message'] = "First name, last name, and email cannot be empty.";
        } 
        elseif ($userObj->isEmailTakenByAnotherUser($email, $userId)) 
        {
            $_SESSION['error_message'] = "The email '{$email}' is already in use by another user.";
        } 
        else 
        {
            if ($userObj->updateUser($userId, $firstName, $lastName, $email, $_POST['departments_id'], $_POST['role'])) 
            {
                $_SESSION['success_message'] = "User updated successfully.";
            
                if ($oldRole && $newRole !== $oldRole) 
                {
                    $notification = new Notification($pdoConnection);
                    $admins = $userObj->getUsersByRole('Admin');
                    $adminName = $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name'];
                    $userName = $firstName . ' ' . $lastName;
                    
                    $db_message = "User role for {$userName} changed from '{$oldRole}' to '{$newRole}' by {$adminName}.";
                    $link = "admin/index.php?page=user-management#users-table";
                    $email_subject = "User Role Change Alert";
                    $email_body = "<h2>User Role Change</h2><p>{$db_message}</p><p><a href='http://localhost/Office-Supply-Request-System/{$link}'>View Users</a></p>";

                    foreach ($admins as $admin) 
                    {
                        $notification->createNotification($admin['id'], $db_message, $link, $admin['email'], $email_subject, $email_body);
                    }
                }
            }
        }
        header("Location: index.php?page=user-management"); exit();
    } 
    elseif ($action === "delete_user") 
    {
        if ($userObj->hasRequests($_POST['entity_id'])) 
        {
            $_SESSION['error_message'] = "Cannot delete user. They are associated with existing requests.";
        } 
        else 
        {
            $userObj->deleteUser($_POST['entity_id']);
            $_SESSION['success_message'] = "User deleted successfully.";
        }
        header("Location: index.php?page=user-management"); exit();
    } 
    elseif ($action === "add_department") 
    {
        if (empty(trim($_POST['department_name']))) 
        {
            $_SESSION['error_message'] = "Department name cannot be empty.";
        } 
        else 
        {
            $departmentObj->name = trim($_POST["department_name"]);
            if ($departmentObj->addDepartment()) 
            {
                $_SESSION['success_message'] = "Department added successfully.";
            }
        }
        header("Location: index.php?page=user-management#departments-table"); exit();
    } 
    elseif ($action === "edit_department") 
    {
        $deptName = trim($_POST['name']);
        if (empty($deptName)) 
        {
            $_SESSION['error_message'] = "Department name cannot be empty.";
        } 
        else 
        {
            if($departmentObj->updateDepartment($_POST['entity_id'], $deptName)) 
            {
                $_SESSION['success_message'] = "Department updated successfully.";
            }
        }
        header("Location: index.php?page=user-management#departments-table"); exit();
    } 
    elseif ($action === "delete_department") 
    {
        if ($departmentObj->hasUsers($_POST['entity_id'])) 
        {
            $_SESSION['error_message'] = "Cannot delete department. Users are still assigned to it.";
        } 
        else 
        {
            $departmentObj->deleteDepartment($_POST['entity_id']);
            $_SESSION['success_message'] = "Department deleted successfully.";
        }
        header("Location: index.php?page=user-management#departments-table"); exit();
    }
}

$users = $userObj->getAllUsers();
$departments = $departmentObj->getAllDepartments();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message']; ?>
        <span class="close-button">&times;</span>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?= $_SESSION['error_message']; ?>
        <span class="close-button">&times;</span>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="modal-container" id="add-user-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New User</h2>
        <form action="index.php?page=user-management" method="POST">
            <input type="hidden" name="action" value="add_user">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group">
                <label>Department</label>
                <select name="departments_id" required><?php foreach ($departments as $d) { echo "<option value=\"{$d['id']}\">{$d['name']}</option>"; } ?></select>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required><option value="Requester">Requester</option><option value="Processor">Processor</option><option value="Admin">Admin</option></select>
            </div>
            <button type="submit">Add User</button>
        </form>
    </div>
</div>

<div class="modal-container" id="edit-user-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Edit User</h2>
        <form action="index.php?page=user-management" method="POST">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="entity_id" value="">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group">
                <label>Department</label>
                <select name="departments_id" required><?php foreach ($departments as $d) { echo "<option value=\"{$d['id']}\">{$d['name']}</option>"; } ?></select>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required><option value="Requester">Requester</option><option value="Processor">Processor</option><option value="Admin">Admin</option></select>
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<div class="modal-container" id="add-department-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Add New Department</h2>
        <form action="index.php?page=user-management" method="POST">
            <input type="hidden" name="action" value="add_department">
            <div class="form-group"><label>Department Name</label><input type="text" name="department_name" required></div>
            <button type="submit">Add Department</button>
        </form>
    </div>
</div>

<div class="modal-container" id="edit-department-modal">
    <div class="modal">
        <span class="close-button">&times;</span>
        <h2>Edit Department</h2>
        <form action="index.php?page=user-management" method="POST">
            <input type="hidden" name="action" value="edit_department">
            <input type="hidden" name="entity_id" value="">
            <div class="form-group"><label>Department Name</label><input type="text" name="name" required></div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<h2 id="users-table">Users</h2>
<button class="open-button" data-target="#add-user-modal">Add User</button>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user["id"] ?></td>
                <td><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars($departmentObj->getDepartmentById($user["departments_id"])["name"]) ?></td>
                <td><?= htmlspecialchars($user["role"]) ?></td>
                <td>
                    <button class="open-button btn" data-target="#edit-user-modal" data-modal-type="edit-user" data-entity-id="<?= $user['id'] ?>" data-entity-data='<?= htmlspecialchars(json_encode(['first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'email' => $user['email'], 'departments_id' => $user['departments_id'], 'role' => $user['role']]), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                    <form action="index.php?page=user-management" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="action" value="delete_user"><input type="hidden" name="entity_id" value="<?= $user['id'] ?>">
                        <button type="submit" class="deny-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2 id="departments-table">Departments</h2>
<button class="open-button" data-target="#add-department-modal">Add Department</button>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($departments as $department): ?>
            <tr>
                <td><?= $department["id"] ?></td>
                <td><?= htmlspecialchars($department["name"]) ?></td>
                <td>
                    <button class="open-button btn" data-target="#edit-department-modal" data-modal-type="edit-department" data-entity-id="<?= $department['id'] ?>" data-entity-data='<?= htmlspecialchars(json_encode(['name' => $department['name']]), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                    <form action="index.php?page=user-management" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this department?');">
                        <input type="hidden" name="action" value="delete_department"><input type="hidden" name="entity_id" value="<?= $department['id'] ?>">
                        <button type="submit" class="deny-button">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>