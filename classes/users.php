<?php

require_once "database.php";

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

    public function getUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $userId);
        $query->execute();
        
        return $query->fetch();
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
}

?>