<?php

require_once "database.php";

class Requests
{
    private $pdo;

    public $id = "";
    public $requesters_id = "";
    public $processors_id = ""; // nullable
    public $requested_date = "";
    public $ready_date = ""; // nullable
    public $finished_date = ""; // nullable
    public $updated_at = ""; // auto-updated
    public $status = ""; // default 'Pending'
    public $requesters_message = ""; // nullable
    public $processors_remarks = ""; // nullable

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRequest()
    {
        $sql = "INSERT INTO requests (requesters_id, requested_date) 
                VALUES (:requesters_id, :requested_date)";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requesters_id", $this->requesters_id);
        $query->bindParam(":requested_date", $this->requested_date);
        $query->execute();
        
        return $this->pdo->lastInsertId();
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

        return $query->fetchAll();
    }

    public function getAllRequestsByRequesterId($requesterId = "")
    {
        $sql = "SELECT * FROM requests WHERE requesters_id = :requesters_id ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requesters_id", $requesterId);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllUnclaimedRequests()
    {
        $sql = "SELECT * FROM requests WHERE status = 'pending' 
                ORDER BY request_date DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllClaimedRequestsByProcessorId($processorId = "")
    {
        $sql = "SELECT * FROM requests WHERE processors_id = :processors_id 
                AND status = 'in_progress' ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllCompletedRequestsByProcessorId($processorId = "")
    {
        $sql = "SELECT * FROM requests WHERE processors_id = :processors_id 
                AND status = 'completed' ORDER BY request_date DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllDeniedRequestsByProcessorId($processorId = "")
    {
        $sql = "SELECT * FROM requests WHERE processors_id = :processors_id 
                AND status = 'denied' ORDER BY request_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->execute();

        return $query->fetchAll();
    }
}

?>