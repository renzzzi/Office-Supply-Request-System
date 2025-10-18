<?php

require_once "database.php";

class Users extends Database
{
    public $id = "";
    public $name = "";
    public $email = "";
    public $department_id = "";
    public $role = ""; // default 'requester'

    public function addUser()
    {
        $sql = "INSERT INTO user (name, email, department_id) 
                VALUES (:name, :email, :department_id)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":department_id", $this->department_id);

        return $query->execute();
    }
}

?>