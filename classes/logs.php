<?php

require_once __DIR__ . '/../config.php';

class Logs
{
    private $conn;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function logAction($userId, $actionType, $message)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        
        $sql = "INSERT INTO activity_logs (users_id, ip_address, action_type, message) 
                VALUES (:uid, :ip, :type, :msg)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':uid' => $userId,
                ':ip' => $ip,
                ':type' => $actionType,
                ':msg' => $message
            ]);
        } catch (PDOException $e) {
            // Silently fail logging to not disrupt main flow, or handle error
        }
    }

    public function getStockLogsCount()
    {
        $sql = "SELECT COUNT(*) FROM stock_logs";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getStockLogs($limit, $offset)
    {
        $sql = "SELECT sl.*, s.name AS supply_name, s.unit_of_supply 
                FROM stock_logs sl 
                JOIN supplies s ON sl.supplies_id = s.id 
                ORDER BY sl.changed_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActivityLogsCount()
    {
        $sql = "SELECT COUNT(*) FROM activity_logs";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getActivityLogs($limit, $offset)
    {
        $sql = "SELECT al.*, u.first_name, u.last_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.users_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>