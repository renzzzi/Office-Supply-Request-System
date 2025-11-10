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

    public function hasSupplies(int $categoryId): bool
    {
        $sql = "SELECT 1 FROM supplies WHERE supply_categories_id = ? LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->execute([$categoryId]);
        return $query->fetchColumn() !== false;
    }

    public function addSupplyCategory()
    {
        $sql = "INSERT INTO supply_categories (name) VALUES (:name)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $this->name);
        return $query->execute();
    }

    public function updateSupplyCategory(int $categoryId, string $name): bool
    {
        $sql = "UPDATE supply_categories SET name = ? WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$name, $categoryId]);
    }

    public function deleteSupplyCategory(int $categoryId): bool
    {
        $sql = "DELETE FROM supply_categories WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$categoryId]);
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