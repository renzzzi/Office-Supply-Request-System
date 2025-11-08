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
    public $released_to = ""; // nullable

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

    public function setReleasedTo($requestId = "", $releasedTo = "")
    {
        $sql = "UPDATE requests SET released_to = :released_to WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":released_to", $releasedTo);
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

    public function getAllRequestsByRequesterId($requesterId, $statusFilter = [], $limit = 10, $offset = 0)
    {
        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $sql = "SELECT * FROM requests 
                WHERE requesters_id = ? AND status IN ($placeholders)
                ORDER BY requested_date DESC
                LIMIT ? OFFSET ?";

        $query = $this->pdo->prepare($sql);
        $params = array_merge([$requesterId], $statusFilter, [$limit, $offset]);
        
        foreach ($params as $key => $value) {
            $query->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getRequestCountByRequesterId($requesterId, $statusFilter = [])
    {
        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $sql = "SELECT COUNT(id) FROM requests WHERE requesters_id = ? AND status IN ($placeholders)";

        $query = $this->pdo->prepare($sql);
        $params = array_merge([$requesterId], $statusFilter);
        $query->execute($params);
        return $query->fetchColumn();
    }

    public function getFilteredRequestsForRequester($requesterId, $reportType)
    {
        $sql = "SELECT r.*, u_proc.first_name as proc_first_name, u_proc.last_name as proc_last_name
                FROM requests r
                LEFT JOIN users u_proc ON r.processors_id = u_proc.id
                WHERE r.requesters_id = :requester_id ";

        switch ($reportType) {
            case 'completed_90_days':
                $sql .= "AND r.status IN ('" . RequestStatus::Released->value . "', '" . RequestStatus::Denied->value . "') ";
                $sql .= "AND r.finished_date >= DATE_SUB(NOW(), INTERVAL 90 DAY) ";
                break;
            case 'in_progress':
                $sql .= "AND r.status IN ('" . RequestStatus::Pending->value . "', '" . RequestStatus::Claimed->value . "', '" . RequestStatus::Ready->value . "') ";
                break;
            case 'all':
            default:
                // No additional filters needed
                break;
        }

        $sql .= "ORDER BY r.requested_date DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':requester_id', $requesterId);
        $query->execute();
        return $query->fetchAll();
    }
}

?>