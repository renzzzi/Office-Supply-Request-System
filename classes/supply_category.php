<?php

/*

CREATE TABLE supply_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

*/

class SupplyCategory extends Database
{
    public $id = "";
    public $name = "";

    public function addSupplyCategory()
    {
        $sql = "INSERT INTO supply_category (name) VALUES (:name)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }
}

?>