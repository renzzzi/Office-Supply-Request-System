<?php

/*

CREATE TABLE purchase_order_supply(
    purchase_order_id INT NOT NULL,
    supply_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),
    price_per_unit DECIMAL(8, 2) NOT NULL,

    PRIMARY KEY (purchase_order_id, supply_id),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_order(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supply(id)
        ON UPDATE CASCADE
); 

*/

class PurchaseOrderSupply extends Database
{
    public $purchase_order_id = "";
    public $supply_id = "";
    public $supply_quantity = "";
    public $price_per_unit = "";

    public function addPurchaseOrderSupply()
    {
        $sql = "INSERT INTO purchase_order_supply (purchase_order_id, supply_id, supply_quantity, price_per_unit) 
                VALUES (:purchase_order_id, :supply_id, :supply_quantity, :price_per_unit)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":purchase_order_id", $this->purchase_order_id);
        $query->bindParam(":supply_id", $this->supply_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);
        $query->bindParam(":price_per_unit", $this->price_per_unit);

        return $query->execute();
    }
}

?>