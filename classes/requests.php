<?php

require_once __DIR__ . '/database.php';

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

    public function getPaginatedRequestsByStatus(RequestStatus $status, int $limit, int $offset)
    {
        $sql = "SELECT * FROM requests WHERE status = ? ORDER BY requested_date DESC LIMIT ? OFFSET ?";
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $status->value);
        $query->bindValue(2, $limit, PDO::PARAM_INT);
        $query->bindValue(3, $offset, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }

    public function getPaginatedRequestsByProcessorIdAndStatuses(int $processorId, array $statusFilter, int $limit, int $offset)
    {
        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $sql = "SELECT * FROM requests 
                WHERE processors_id = ? AND status IN ($placeholders)
                ORDER BY finished_date DESC, requested_date DESC 
                LIMIT ? OFFSET ?";

        $query = $this->pdo->prepare($sql);
        $params = array_merge([$processorId], $statusFilter, [$limit, $offset]);
        
        foreach ($params as $key => $value) {
            $query->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $query->execute();
        return $query->fetchAll();
    }

    public function getCountByStatus(RequestStatus $status): int
    {
        $sql = "SELECT COUNT(id) FROM requests WHERE status = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$status->value]);
        return (int)$query->fetchColumn();
    }

    public function getPaginatedRequestsByProcessorIdAndStatus(int $processorId, RequestStatus $status, int $limit, int $offset)
    {
        $sql = "SELECT * FROM requests WHERE processors_id = ? AND status = ? ORDER BY requested_date DESC LIMIT ? OFFSET ?";
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $processorId, PDO::PARAM_INT);
        $query->bindValue(2, $status->value);
        $query->bindValue(3, $limit, PDO::PARAM_INT);
        $query->bindValue(4, $offset, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }

    public function getCountByProcessorIdAndStatus(int $processorId, RequestStatus $status): int
    {
        $sql = "SELECT COUNT(id) FROM requests WHERE processors_id = ? AND status = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$processorId, $status->value]);
        return (int)$query->fetchColumn();
    }

    public function getCountByProcessorIdAndStatuses(int $processorId, array $statusFilter): int
    {
        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $sql = "SELECT COUNT(id) FROM requests WHERE processors_id = ? AND status IN ($placeholders)";

        $query = $this->pdo->prepare($sql);
        $params = array_merge([$processorId], $statusFilter);
        $query->execute($params);
        return (int)$query->fetchColumn();
    }

    public function getFilteredRequestsForProcessor(?int $processorId, string $reportType, ?string $startDate = null, ?string $endDate = null)
    {
        $sql = "SELECT r.*, 
                       u_req.first_name as req_first_name, 
                       u_req.last_name as req_last_name,
                       d.name as department_name,
                       u_proc.first_name as proc_first_name,
                       u_proc.last_name as proc_last_name
                FROM requests r
                LEFT JOIN users u_req ON r.requesters_id = u_req.id
                LEFT JOIN departments d ON u_req.departments_id = d.id
                LEFT JOIN users u_proc ON r.processors_id = u_proc.id
                WHERE 1=1 ";

        $params = [];

        $status_map = [
            'pending' => [RequestStatus::Pending->value],
            'claimed' => [RequestStatus::Claimed->value],
            'ready' => [RequestStatus::Ready->value],
            'finished' => [RequestStatus::Released->value, RequestStatus::Denied->value],
            'processed' => [RequestStatus::Claimed->value, RequestStatus::Ready->value, RequestStatus::Released->value, RequestStatus::Denied->value]
        ];
        
        if (array_key_exists($reportType, $status_map)) {
            $statuses = $status_map[$reportType];
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND r.status IN ($placeholders)";
            $params = array_merge($params, $statuses);

            if ($reportType !== 'pending') {
                 $sql .= " AND r.processors_id = ? ";
                 $params[] = $processorId;
            }

        } elseif ($reportType === 'all') {
            $sql .= " AND (r.status = ? OR r.processors_id = ?) ";
            $params[] = RequestStatus::Pending->value;
            $params[] = $processorId;
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

    public function getRequestCountsByStatusForRequester(int $requesterId, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT status, COUNT(id) as count 
                FROM requests 
                WHERE requesters_id = ? ";
        
        $params = [$requesterId];

        if ($startDate) {
            $sql .= " AND DATE(requested_date) >= ? ";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY status";
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        
        $results = $query->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts = [];
        foreach (RequestStatus::cases() as $case) {
            $counts[$case->value] = $results[$case->value] ?? 0;
        }
        return $counts;
    }

    public function getTopRequestedItemsForRequester(int $requesterId, int $limit = 5, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT s.name, SUM(rs.supply_quantity) as total_quantity
                FROM request_supplies rs
                JOIN requests r ON rs.requests_id = r.id
                JOIN supplies s ON rs.supplies_id = s.id
                WHERE r.requesters_id = ? ";

        $params = [$requesterId];

        if ($startDate) {
            $sql .= " AND DATE(r.requested_date) >= ? ";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(r.requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY s.name ORDER BY total_quantity DESC LIMIT ?";
        
        $query = $this->pdo->prepare($sql);

        $paramIndex = 1;
        foreach ($params as $param) {
            $query->bindValue($paramIndex++, $param);
        }

        $query->bindValue($paramIndex, $limit, PDO::PARAM_INT);

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentRequestsByRequesterId(int $requesterId, int $limit = 5): array
    {
        $sql = "SELECT r.*, u.first_name, u.last_name
                FROM requests r
                LEFT JOIN users u ON r.processors_id = u.id
                WHERE r.requesters_id = ? 
                ORDER BY r.requested_date DESC 
                LIMIT ?";
        
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $requesterId, PDO::PARAM_INT);
        $query->bindValue(2, $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRequestsInDateRangeForRequester(int $requesterId, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT r.*, p.first_name as proc_first_name, p.last_name as proc_last_name
                FROM requests r
                LEFT JOIN users p ON r.processors_id = p.id
                WHERE r.requesters_id = ? ";
        
        $params = [$requesterId];

        if ($startDate) {
            $sql .= " AND DATE(r.requested_date) >= ? ";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(r.requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY r.requested_date DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountCompletedTodayByProcessor(int $processorId, ?string $startDate = null, ?string $endDate = null): int
    {
        $sql = "SELECT COUNT(id) FROM requests 
                WHERE processors_id = ? 
                AND status IN (?, ?) ";
        
        $params = [$processorId, RequestStatus::Released->value, RequestStatus::Denied->value];

        if ($startDate && $endDate) {
            $sql .= " AND DATE(finished_date) BETWEEN ? AND ? ";
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($startDate) {
            $sql .= " AND DATE(finished_date) >= ? ";
            $params[] = $startDate;
        } elseif ($endDate) {
            $sql .= " AND DATE(finished_date) <= ? ";
            $params[] = $endDate;
        } else {
             $sql .= " AND DATE(finished_date) = CURDATE() ";
        }
        
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return (int)$query->fetchColumn();
    }

    public function getTopRequestedItemsSystemWide(int $limit = 5, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT s.name, SUM(rs.supply_quantity) as total_quantity
                FROM request_supplies rs
                JOIN requests r ON rs.requests_id = r.id
                JOIN supplies s ON rs.supplies_id = s.id
                WHERE 1=1 ";

        $params = [];

        if ($startDate) {
            $sql .= " AND DATE(r.requested_date) >= ? ";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(r.requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY s.name ORDER BY total_quantity DESC LIMIT ?";
        
        $query = $this->pdo->prepare($sql);

        $paramIndex = 1;
        foreach ($params as $param) {
            $query->bindValue($paramIndex++, $param);
        }

        $query->bindValue($paramIndex, $limit, PDO::PARAM_INT);
        
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentActionsByProcessor(int $processorId, int $limit = 5): array
    {
        $sql = "SELECT r.*, u.first_name, u.last_name
                FROM requests r
                JOIN users u ON r.requesters_id = u.id
                WHERE r.processors_id = ? 
                ORDER BY r.updated_at DESC 
                LIMIT ?";
        
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $processorId, PDO::PARAM_INT);
        $query->bindValue(2, $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestCountByStatuses(array $statusFilter): int
    {
        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $sql = "SELECT COUNT(id) FROM requests WHERE status IN ($placeholders)";
        $query = $this->pdo->prepare($sql);
        $query->execute($statusFilter);
        return (int)$query->fetchColumn();
    }

    public function getRequestCountCompletedToday(): int
    {
        $sql = "SELECT COUNT(id) FROM requests 
                WHERE status IN (?, ?) AND DATE(finished_date) = CURDATE()";
        $query = $this->pdo->prepare($sql);
        $query->execute([RequestStatus::Released->value, RequestStatus::Denied->value]);
        return (int)$query->fetchColumn();
    }

    public function getRequestVolumeByDepartment(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT d.name, COUNT(r.id) as request_count
                FROM requests r
                JOIN users u ON r.requesters_id = u.id
                JOIN departments d ON u.departments_id = d.id
                WHERE 1=1 ";

        $params = [];

        if ($startDate) {
            $sql .= " AND DATE(r.requested_date) >= ? ";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(r.requested_date) <= ? ";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY d.name ORDER BY request_count DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentSystemActivity(int $limit = 10): array
    {
        $sql = "SELECT 
                    r.id, r.status, r.updated_at,
                    req.first_name as req_first_name, req.last_name as req_last_name,
                    proc.first_name as proc_first_name, proc.last_name as proc_last_name
                FROM requests r
                JOIN users req ON r.requesters_id = req.id
                LEFT JOIN users proc ON r.processors_id = proc.id
                ORDER BY r.updated_at DESC
                LIMIT ?";
        
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequesterInfoByRequestId(int $requestId): ?array
    {
        $sql = "SELECT u.id, u.email, u.first_name, u.last_name
                FROM requests r
                JOIN users u ON r.requesters_id = u.id
                WHERE r.id = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$requestId]);
        $result = $query->fetch();
        return $result ?: null;
    }

    public function getOldPendingRequests(int $ageInHours): array
    {
        $sql = "SELECT id FROM requests 
                WHERE status = 'Pending' 
                AND requested_date < (NOW() - INTERVAL :hours HOUR)";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':hours', $ageInHours, PDO::PARAM_INT);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getProcessorPerformance()
    {
        $sql = "SELECT CONCAT(u.first_name, ' ', u.last_name) as name, COUNT(r.id) as total_processed
                FROM requests r
                JOIN users u ON r.processors_id = u.id
                WHERE r.status IN ('Released', 'Denied')
                GROUP BY r.processors_id
                ORDER BY total_processed DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopDepartmentsByVolume($limit = 5)
    {
        $sql = "SELECT d.name, COUNT(r.id) as request_count
                FROM requests r
                JOIN users u ON r.requesters_id = u.id
                JOIN departments d ON u.departments_id = d.id
                GROUP BY d.id
                ORDER BY request_count DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProcessorPerformanceToday()
    {
        $sql = "SELECT u.first_name, u.last_name, COUNT(r.id) as finished_today
                FROM users u
                LEFT JOIN requests r ON u.id = r.processors_id 
                    AND r.status IN ('Released', 'Denied')
                    AND DATE(r.finished_date) = CURDATE()
                WHERE u.role = 'Processor'
                GROUP BY u.id
                ORDER BY finished_today DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountCompletedTodayByRequester(int $requesterId): int
    {
        $sql = "SELECT COUNT(id) FROM requests 
                WHERE requesters_id = ? 
                AND status IN ('Released', 'Denied') 
                AND DATE(finished_date) = CURDATE()";
        
        $query = $this->pdo->prepare($sql);
        $query->execute([$requesterId]);
        return (int)$query->fetchColumn();
    }

    public function getAllDepartmentsVolume()
    {
        $sql = "SELECT d.name, COUNT(r.id) as request_count
                FROM departments d
                LEFT JOIN users u ON d.id = u.departments_id
                LEFT JOIN requests r ON u.id = r.requesters_id
                GROUP BY d.id, d.name
                ORDER BY request_count DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcessorWeeklyActivity(int $processorId): array
    {
        $sql = "SELECT DATE(finished_date) as work_date, COUNT(id) as count
                FROM requests 
                WHERE processors_id = ? 
                AND status IN ('Released', 'Denied')
                AND finished_date >= DATE(NOW() - INTERVAL 6 DAY)
                GROUP BY work_date
                ORDER BY work_date ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute([$processorId]);
        return $query->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
?>