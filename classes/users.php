<?php

require_once __DIR__ . '/database.php';

class Users
{
    private $pdo;

    public $id = "";
    public $first_name = "";
    public $last_name = "";
    public $email = "";
    public $password_hash = "";
    public $departments_id = "";
    public $role = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function hasRequests(int $userId): bool
    {
        $sql = "SELECT 1 FROM requests WHERE requesters_id = ? OR processors_id = ? LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->execute([$userId, $userId]);
        return $query->fetchColumn() !== false;
    }

    public function isEmailTakenByAnotherUser(string $email, int $currentUserId): bool
    {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->execute([$email, $currentUserId]);
        return $query->fetchColumn() !== false;
    }

    public function addUser()
    {
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, departments_id, role) 
                VALUES (:first_name, :last_name, :email, :password_hash, :departments_id, :role)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":first_name", $this->first_name);
        $query->bindParam(":last_name", $this->last_name);  
        $query->bindParam(":email", $this->email);
        $query->bindParam(":password_hash", $this->password_hash);
        $query->bindParam(":departments_id", $this->departments_id);
        $query->bindParam(":role", $this->role);

        return $query->execute();
    }

    public function updateUser(int $userId, string $firstName, string $lastName, string $email, int $departmentId, string $role): bool
    {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, departments_id = ?, role = ? WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$firstName, $lastName, $email, $departmentId, $role, $userId]);
    }

    public function deleteUser(int $userId): bool
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$userId]);
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users";

        $query = $this->pdo->prepare($sql);
        $query->execute();
        
        return $query->fetchAll();
    }

    public function getUserByEmail($userEmail)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":email", $userEmail);
        $query->execute();
        
        return $query->fetch();
    }

    public function getUserById(int $userId): ?array
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$userId]);
        $result = $query->fetch();
        return $result ?: null;
    }

    public function getUsersByName($search)
    {
        $searchTerm = '%' . $search . '%';

        $sql = "SELECT id, first_name, last_name FROM users 
                WHERE first_name LIKE :term 
                   OR last_name LIKE :term 
                   OR CONCAT(first_name, ' ', last_name) LIKE :term 
                LIMIT 10";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":term", $searchTerm);
        $query->execute();

        return $query->fetchAll();
    }

    public function getUsersByRole(string $role): array
    {
        $sql = "SELECT id, email FROM users WHERE role = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$role]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>