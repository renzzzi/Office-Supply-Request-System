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
}

?>