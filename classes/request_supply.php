<?php

/*

CREATE TABLE request_supply (
    request_id INT NOT NULL,
    supply_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (request_id, supply_id),
    FOREIGN KEY (request_id) REFERENCES request(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supply(id)
        ON UPDATE CASCADE
);

*/

require_once "database.php";

class RequestSupply extends Database
{
    public $request_id = "";
    public $supply_id = "";
    public $supply_quantity = "";

    public function addRequestSupply()
    {
        $sql = "INSERT INTO request_supply (request_id, supply_id, supply_quantity) 
                VALUES (:request_id, :supply_id, :supply_quantity)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":request_id", $this->request_id);
        $query->bindParam(":supply_id", $this->supply_id);
        $query->bindParam(":supply_quantity", $this->supply_quantity);

        return $query->execute();
    }
}

?>