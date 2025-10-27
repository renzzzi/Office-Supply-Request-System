<?php

require_once "database.php";

class Supplies
{
    private $pdo;

    public $id = "";
    public $supply_categories_id = "";
    public $name = "";
    public $unit_of_supply = ""; // e.g box, piece, pack, ream
    public $price_per_unit = "";
    public $stock_quantity = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function addSupply()
    {
    $sql = "INSERT INTO supplies (supply_categories_id, name, unit_of_supply, price_per_unit) 
        VALUES (:supply_categories_id, :name, :unit_of_supply, :price_per_unit)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":supply_categories_id", $this->supply_categories_id);
        $query->bindParam(":unit_of_supply", $this->unit_of_supply);
        $query->bindParam(":price_per_unit", $this->price_per_unit);
        
        return $query->execute();
    }

    public function viewAllSupply($search = "")
    {
    $sql = "SELECT * FROM supplies WHERE name LIKE CONCAT('%', :search, '%') 
            ORDER BY name ASC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":search", $search);
        $query->execute();
        
        return $query->fetchAll();
    }

    public function searchSupplyNames($search = "")
    {
        $sql = "SELECT name FROM supplies WHERE name LIKE CONCAT('%', :search, '%')";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":search", $search);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    //broken
    public function editSupply($supplyId = "")
    {
        $sql = "UPDATE supplies SET supply_categories_id = :supply_categories_id, name = :name, unit_of_supply = :unit_of_supply, price_per_unit = :price_per_unit
                WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $name);
        $query->bindParam(":supply_categories_id", $supply_categories_id);
        $query->bindParam(":unit_of_supply", $unit_of_supply);
        $query->bindParam(":price_per_unit", $price_per_unit);
        $query->bindParam(":id", $supplyId);

        return $query->execute();
    }

    public function getSupplyByName($name = "")
    {
        $sql = "SELECT id, name FROM supplies WHERE name = :name LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $name);
        $query->execute();
        return $query->fetch();
    }

    public function getSupplyNameById($supplyId = "")
    {
        $sql = "SELECT name FROM supplies WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $supplyId);
        $query->execute();
        $result = $query->fetch();
        
        return $result ? $result["name"] : "";
    }
}


