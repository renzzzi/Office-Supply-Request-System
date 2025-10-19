<?php

require_once "database.php";

class Supplies
{
    private $pdo;

    public $id = "";
    public $category_id = "";
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
        $sql = "INSERT INTO supply (category_id, name, unit_of_supply, price_per_unit, stock_quantity) 
                VALUES (:category_id, :name, :unit_of_supply, :price_per_unit, :stock_quantity)";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":category_id", $this->category_id);
        $query->bindParam(":unit_of_supply", $this->unit_of_supply);
        $query->bindParam(":price_per_unit", $this->price_per_unit);
        $query->bindParam(":stock_quantity", $this->stock_quantity);
        
        return $query->execute();
    }

    public function viewSupply($search = "")
    {
        $sql = "SELECT * FROM supply WHERE name LIKE CONCAT('%', :search, '%') 
                ORDER BY name ASC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":search", $search);

        if ($query->execute()) 
        {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } 
        else 
        {
            return null;
        }
    }

    public function editSupply($supplyId = "")
    {
        $sql = "UPDATE supply SET category_id = :category_id, name = :name, unit_of_supply = :unit_of_supply, price_per_unit = :price_per_unit, stock_quantity = :stock_quantity
                WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $name);
        $query->bindParam(":category_id", $category_id);
        $query->bindParam(":unit_of_supply", $unit_of_supply);
        $query->bindParam(":price_per_unit", $price_per_unit);
        $query->bindParam(":stock_quantity", $stock_quantity);
        $query->bindParam(":id", $supplyId);

        return $query->execute();
    }

    
}


