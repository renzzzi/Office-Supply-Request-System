<?php

/*

CREATE TABLE department (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

*/

class Department extends Database
{
    public $id = "";
    public $name = "";

    public function addDepartment()
    {
        $sql = "INSERT INTO department (name) VALUES (:name)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);

        return $query->execute();
    }
}

?>