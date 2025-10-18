<?php

class Departments extends Database
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