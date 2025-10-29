<?php

use LDAP\Result;

require_once "database.php";

enum RequestStatus: string
{
    case Pending = "Pending";
    case Claimed = "Claimed";
    case Ready = "Ready For Pickup";
    case Released = "Released";
    case Denied = "Denied";
}

class Requests
{
    private $pdo;

    public $id = "";
    public $requesters_id = "";
    public $processors_id = ""; // nullable
    public $released_to_id = ""; // nullable

    public $requested_date = "";
    public $claimed_date = ""; //nullable
    public $ready_date = ""; // nullable
    public $finished_date = ""; // nullable
    
    public $status = ""; // default 'Pending'
    public $updated_at = ""; // auto-updated

    public $requesters_message = ""; // nullable
    public $processors_remarks = ""; // nullable

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Create
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

    // Update
    public function updateRequestStatus($requestId, RequestStatus $newStatus)
    {
        $sql = "UPDATE requests SET status = :status, ";
        
        switch ($newStatus) {
            case RequestStatus::Claimed:
                $sql .= "claimed_date = NOW() ";
                break;
            case RequestStatus::Ready:
                $sql .= "ready_date = NOW() ";
                break;
            case RequestStatus::Released:
            case RequestStatus::Denied:
                $sql .= "finished_date = NOW() ";
                break;
        }
        $sql .= "WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindValue(":status", $newStatus->value);
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

    public function setReleasedToId($requestId = "", $releasedToId = "")
    {
        $sql = "UPDATE requests SET released_to_id = :released_to_id WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":released_to_id", $releasedToId);
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }

    // Read
    public function getAllRequests()
    {
        $sql = "SELECT * FROM requests ORDER BY requested_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllRequestsByRequesterId($requesterId = "")
    {
        $sql = "SELECT * FROM requests WHERE requesters_id = :requesters_id ORDER BY requested_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":requesters_id", $requesterId);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllRequestsByProcessorIdAndStatus($processorId = "", RequestStatus $status)
    {
        $sql = "SELECT * FROM requests WHERE processors_id = :processors_id AND status = :status ORDER BY requested_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":processors_id", $processorId);
        $query->bindValue(":status", $status->value);
        $query->execute();

        return $query->fetchAll();
    }

    public function getAllRequestsByStatus(RequestStatus $status)
    {
        $sql = "SELECT * FROM requests WHERE status = :status ORDER BY requested_date DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindValue(":status", $status->value);
        $query->execute();

        return $query->fetchAll();
    }
}

?>