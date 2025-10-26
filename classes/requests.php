<?php

require_once "database.php";

class Requests
{
    private $pdo;

    public $id = "";
    public $requesters_id = "";
    public $processors_id = ""; // nullable
    public $departments_id = "";
    public $status = ""; // default 'pending'
    public $request_date = "";
    public $processed_date = ""; // nullable

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRequest()
    {
        $sql = "INSERT INTO requests (requesters_id, departments_id, request_date) 
                VALUES (:requesters_id, :departments_id, :request_date)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requesters_id", $this->requesters_id);
        $query->bindParam(":departments_id", $this->departments_id);
        $query->bindParam(":request_date", $this->request_date);

        if ($query->execute()) {
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }
    }

    public function modifyRequestStatus($requestId = "", $newStatus = "pending")
    {
        $sql = "UPDATE requests SET status = :status WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":status", $newStatus);
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }

    public function updateProcessedDate($requestId = "")
    {
        $sql = "UPDATE requests SET processed_date = :processed_date WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processed_date", date('Y-m-d H:i:s'));
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }

    public function setProcessorId($requestId = "", $processorId = "")
    {
        $sql = "UPDATE requests SET processors_id = :processors_id WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }

    public function getAllRequests()
    {
        $sql = "SELECT * FROM requests ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRequestsByRequesterId($requesterId = "")
    {
        $sql = "SELECT * FROM requests WHERE requesters_id = :requesters_id ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requesters_id", $requesterId);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUnclaimedRequests()
    {
        $sql = "SELECT * FROM requests WHERE processors_id IS NULL ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllClaimedRequestsByProcessorId($processorId = "")
    {
        $sql = "SELECT * FROM requests WHERE processors_id = :processors_id ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>