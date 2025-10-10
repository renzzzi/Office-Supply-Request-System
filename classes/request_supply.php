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
        ON UPDATE CASCADE,
);

*/

require_once "database.php";

class RequestSupply
{
    
}

?>