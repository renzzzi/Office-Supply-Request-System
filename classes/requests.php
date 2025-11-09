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
    public $processors_id = ""; 
    public $released_to = ""; 

    public $requested_date = "";
    public $claimed_date = "";
    public $ready_date = "";
    public $finished_date = "";
    
    public $status = "";
    public $updated_at = "";

    public $requesters_message = "";
    public $processors_remarks = "";

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

    public function getFilteredRequestsForRequester($requesterId, $reportType, $startDate = null, $endDate = null)
    {
        $sql = "SELECT r.*, u_proc.first_name as proc_first_name, u_proc.last_name as proc_last_name
                FROM requests r
                LEFT JOIN users u_proc ON r.processors_id = u_proc.id
                WHERE r.requesters_id = ? ";

        $params = [$requesterId];

        if ($reportType === 'in_progress') {
            $status_params = [RequestStatus::Pending->value, RequestStatus::Claimed->value, RequestStatus::Ready->value];
            $placeholders = implode(',', array_fill(0, count($status_params), '?'));
            $sql .= " AND r.status IN ($placeholders)";
            $params = array_merge($params, $status_params);
        } elseif ($reportType === 'finished') {
            $status_params = [RequestStatus::Released->value, RequestStatus::Denied->value];
            $placeholders = implode(',', array_fill(0, count($status_params), '?'));
            $sql .= " AND r.status IN ($placeholders)";
            $params = array_merge($params, $status_params);
        }
        
        if (!empty($startDate)) {
            $sql .= " AND DATE(r.requested_date) >= ? ";
            $params[] = $startDate;
        }
        if (!empty($endDate)) {
            $sql .= " AND DATE(r.requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= "ORDER BY r.requested_date DESC";
        
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
}