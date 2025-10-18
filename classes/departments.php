<?php

class Departments
{
    private $pdo;

    public $id = "";
    public $name = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addDepartment()
    {
        $sql = "INSERT INTO department (name) VALUES (:name)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }
}

?>