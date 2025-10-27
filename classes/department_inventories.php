<?php

class DepartmentInventories 
{
    private $pdo;

    public $departments_id = "";
    public $supplies_id = "";
    public $supply_quantity = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addInventoryRecord()
    {
        $sql = "INSERT INTO department_inventories (department_id, supplies_id, supply_quantity) 
                VALUES (:department_id, :supplies_id, :supply_quantity)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":department_id", $this->departments_id);
        $query->bindParam(":supplies_id", $this->supplies_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);

        return $query->execute();
    }

    public function getInventoryByDepartmentId(int $departmentId): array
    {
        $sql = "SELECT * FROM department_inventories WHERE departments_id = :departments_id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":departments_id", $departmentId, PDO::PARAM_INT);
        
        $query->execute();
        return $query->fetchAll();
    }
}

?>