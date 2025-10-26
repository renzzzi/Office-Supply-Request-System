<?php

require_once "database.php";

class RequestSupplies
{
    private $pdo;

    public $requests_id = "";
    public $supplies_id = "";
    public $supply_quantity = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRequestSupply()
    {
        $sql = "INSERT INTO request_supplies (requests_id, supplies_id, supply_quantity) 
                VALUES (:requests_id, :supplies_id, :supply_quantity)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requests_id", $this->requests_id);
        $query->bindParam(":supplies_id", $this->supplies_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);

        return $query->execute();
    }

    public function getSupplyCountByRequestId($requestId = "")
    {
        $sql = "SELECT COUNT(*) as supply_count FROM request_supplies 
                WHERE requests_id = :requests_id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requests_id", $requestId);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result["supply_count"] : 0;
    }

    public function getAllSuppliesByRequestId($requestId = "")
    {
        $sql = "SELECT s.name, rs.supply_quantity 
                FROM request_supplies rs
                JOIN supplies s ON rs.supplies_id = s.id
                WHERE rs.requests_id = :requests_id
                ORDER BY s.name ASC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requests_id", $requestId);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Returns up to 2 supplies for summary display
    public function getSupplySummaryByRequestId($requestId = "")
    {
        $sql = "SELECT s.name, rs.supply_quantity 
                FROM request_supplies rs
                JOIN supplies s ON rs.supplies_id = s.id
                WHERE rs.requests_id = :requests_id 
                LIMIT 2";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requests_id", $requestId);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    
}

?>