<?php

require_once "database.php";

class Requests
{
    private $pdo;

    public $id = "";
    public $requester_id = "";
    public $processor_id = ""; // nullable
    public $department_id = "";
    public $status = ""; // default 'pending'
    public $request_date = "";
    public $processed_date = ""; // nullable

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRequest()
    {
        $sql = "INSERT INTO request (requester_id, department_id, request_date) 
                VALUES (:requester_id, :department_id, :request_date)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requester_id", $this->requester_id);
        $query->bindParam(":department_id", $this->department_id);
        $query->bindParam(":request_date", $this->request_date);

        return $query->execute();
    }

    public function modifyRequestStatus($requestId = "", $newStatus = "pending")
    {
        $sql = "UPDATE request SET status = :status WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":status", $newStatus);
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }
}

?>