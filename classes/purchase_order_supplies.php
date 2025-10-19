<?php

class PurchaseOrderSupplies
{
    private $pdo;
    
    public $purchase_order_id = "";
    public $supply_id = "";
    public $supply_quantity = "";
    public $price_per_unit = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addPurchaseOrderSupply()
    {
        $sql = "INSERT INTO purchase_order_supply (purchase_order_id, supply_id, supply_quantity, price_per_unit) 
                VALUES (:purchase_order_id, :supply_id, :supply_quantity, :price_per_unit)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":purchase_order_id", $this->purchase_order_id);
        $query->bindParam(":supply_id", $this->supply_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);
        $query->bindParam(":price_per_unit", $this->price_per_unit);

        return $query->execute();
    }
}

?>