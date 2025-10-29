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
        $sql = "INSERT INTO departments (name) VALUES (:name)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }

    public function getDepartmentById($departmentId)
    {
        $sql = "SELECT * FROM departments WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $departmentId);
        $query->execute();

        return $query->fetch();
    }

    public function getAllDepartments()
    {
        $sql = "SELECT * FROM departments";
        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }
}

?>