<?php

class UserRoles
{
    private $pdo;

    public $users_id = "";
    public $roles_id = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function assignRoleToUser($userId, $roleId)
    {
        $sql = "INSERT INTO user_roles (users_id, roles_id) VALUES (:users_id, :roles_id)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":users_id", $userId);
        $query->bindParam(":roles_id", $roleId);

        return $query->execute();
    }

    // The higher the role ID, the higher the privilege (e.g., 1 = requester, 2 = processor, 3 = admin)
    public function getHighestRoleByUserId($userId)
    {
        $sql = "SELECT r.* FROM roles r
                JOIN user_roles ur ON r.id = ur.roles_id
                WHERE ur.users_id = :users_id
                ORDER BY r.id DESC LIMIT 1";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":users_id", $userId);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

?>