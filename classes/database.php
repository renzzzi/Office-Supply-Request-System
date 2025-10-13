<?php

class Database
{
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $db_name = "office_supply_request";

    protected $conn;

    protected function connect()
    {
        $this->conn = new PDO("mysql:host = $this->host; dbname = $this->db_name", $this->username, $this->password);
        return $this->conn;
    }
}

?>