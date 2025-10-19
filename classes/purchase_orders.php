<?php

class PurchaseOrders
{
    private $pdo;

    public $id = "";
    public $supplier_id = "";
    public $processor_id = "";
    public $order_date = "";
    public $received_date = ""; // nullable
    public $status = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addPurchaseOrder()
    {
        $sql = "INSERT INTO purchase_order (supplier_id, processor_id, order_date) 
                VALUES (:supplier_id, :processor_id, :order_date)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":supplier_id", $this->supplier_id);
        $query->bindParam(":processor_id", $this->processor_id);
        $query->bindParam(":order_date", $this->order_date);

        return $query->execute();
    }
}

?>