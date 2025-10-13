<?php

/*

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    role ENUM('requester', 'processor', 'admin') NOT NULL DEFAULT 'requester',

    FOREIGN KEY (department_id) REFERENCES department(id)
);

*/

require_once "database.php";

class User extends Database
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