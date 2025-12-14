<?php

class Logs
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function logAction($userId, $actionType, $message): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $stmt = $this->pdo->prepare(
            "INSERT INTO activity_logs (users_id, ip_address, action_type, message)
             VALUES (:uid, :ip, :type, :msg)"
        );

        try {
            $stmt->execute([
                ':uid'  => $userId,
                ':ip'   => $ip,
                ':type' => $actionType,
                ':msg'  => $message
            ]);
        } catch (PDOException $e) {
        }
    }

    public function getStockLogsCount(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM stock_logs"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getStockLogs(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT sl.*, s.name AS supply_name, s.unit_of_supply
             FROM stock_logs sl
             JOIN supplies s ON sl.supplies_id = s.id
             ORDER BY sl.changed_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getActivityLogsCount(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM activity_logs"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getActivityLogs(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT al.*, u.first_name, u.last_name
             FROM activity_logs al
             LEFT JOIN users u ON al.users_id = u.id
             ORDER BY al.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
