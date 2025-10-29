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
    $sql = "INSERT INTO supplies (supply_categories_id, name, unit_of_supply, price_per_unit, stock_quantity) 
        VALUES (:supply_categories_id, :name, :unit_of_supply, :price_per_unit, :stock_quantity)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":supply_categories_id", $this->supply_categories_id);
        $query->bindParam(":unit_of_supply", $this->unit_of_supply);
        $query->bindParam(":price_per_unit", $this->price_per_unit);
        $query->bindParam(":stock_quantity", $this->stock_quantity);
        
        return $query->execute();
    }

    public function deductStock($supplyId, $quantityToDeduct)
    {
        $sql = "UPDATE supplies SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
        
        $query = $this->pdo->prepare($sql);
        
        return $query->execute([
            ':quantity' => $quantityToDeduct,
            ':id' => $supplyId
        ]);
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


