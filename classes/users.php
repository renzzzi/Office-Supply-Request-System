<?php

require_once "database.php";

class Users
{
    private $pdo;

    public $id = "";
    public $name = "";
    public $email = "";
    public $department_id = "";
    public $role = ""; // default 'requester'

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addUser()
    {
        $sql = "INSERT INTO user (name, email, department_id) 
                VALUES (:name, :email, :department_id)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":department_id", $this->department_id);

        return $query->execute();
    }

    public function getUserByEmail($userEmail)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":email", $userEmail);
        $query->execute();
        
        return $query->fetch();
    }
}

?>