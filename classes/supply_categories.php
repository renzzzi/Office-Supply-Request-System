<?php

class SupplyCategories
{
    private $pdo;

    public $id = "";
    public $name = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addSupplyCategory()
    {
        $sql = "INSERT INTO supply_categories (name) VALUES (:name)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        return $query->execute();
    }

    public function getAllSupplyCategories()
    {
        $sql = "SELECT * FROM supply_categories ORDER BY name ASC";
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function getSupplyCategoryById($categoryId)
    {
        $sql = "SELECT * FROM supply_categories WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $categoryId);
        $query->execute();
        return $query->fetch();
    }
}