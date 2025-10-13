<?php

/*

CREATE TABLE purchase_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    processor_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    received_date DATETIME,
    status ENUM('pending', 'received', 'cancelled') NOT NULL DEFAULT 'pending',

    FOREIGN KEY (supplier_id) REFERENCES supplier(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (processor_id) REFERENCES user(id)
        ON UPDATE CASCADE
);

*/

class PurchaseOrder extends Database
{
    public $id = "";
    public $supplier_id = "";
    public $processor_id = "";
    public $order_date = "";
    public $received_date = ""; // nullable
    public $status = "";

    public function addPurchaseOrder()
    {
        $sql = "INSERT INTO purchase_order (supplier_id, processor_id, order_date) 
                VALUES (:supplier_id, :processor_id, :order_date)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":supplier_id", $this->supplier_id);
        $query->bindParam(":processor_id", $this->processor_id);
        $query->bindParam(":order_date", $this->order_date);

        return $query->execute();
    }
}

?>