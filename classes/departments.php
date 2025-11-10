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

    public function hasUsers(int $departmentId): bool
    {
        $sql = "SELECT 1 FROM users WHERE departments_id = ? LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->execute([$departmentId]);
        return $query->fetchColumn() !== false;
    }

    public function addDepartment()
    {
        $sql = "INSERT INTO departments (name) VALUES (:name)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }

    public function updateDepartment(int $departmentId, string $name): bool
    {
        $sql = "UPDATE departments SET name = ? WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$name, $departmentId]);
    }

    public function deleteDepartment(int $departmentId): bool
    {
        $sql = "DELETE FROM departments WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$departmentId]);
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