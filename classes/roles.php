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

    public function getAllRoles()
    {
        $sql = "SELECT * FROM roles";
        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>