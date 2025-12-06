<?php

require_once __DIR__ . '/../config.php';

class Database
{
    private $host = DB_HOST;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $db_name = DB_NAME;

    private $conn;

    public function connect()
    {
        $this->conn = null;

        try
        {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name", 
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        catch (PDOException $e)
        {
            die("Connection error: " . $e->getMessage());
        }
        
        return $this->conn;
    }
}

?>