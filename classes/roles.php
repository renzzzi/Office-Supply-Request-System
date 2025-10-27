<?php

class Roles
{
    private $pdo;

    public $id = "";
    public $name = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRole()
    {
        $sql = "INSERT INTO roles (name) VALUES (:name)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }

    public function getRoleById($roleId)
    {
        $sql = "SELECT * FROM roles WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $roleId);
        $query->execute();

        return $query->fetch();
    }

    public function getAllRoles()
    {
        $sql = "SELECT * FROM roles";
        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }
}

?>