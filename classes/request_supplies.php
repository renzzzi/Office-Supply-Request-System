<?php

require_once "database.php";

class RequestSupplies
{
    private $pdo;

    public $request_id = "";
    public $supply_id = "";
    public $supply_quantity = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRequestSupply()
    {
        $sql = "INSERT INTO request_supply (request_id, supply_id, supply_quantity) 
                VALUES (:request_id, :supply_id, :supply_quantity)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":request_id", $this->request_id);
        $query->bindParam(":supply_id", $this->supply_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);

        return $query->execute();
    }
}

?>