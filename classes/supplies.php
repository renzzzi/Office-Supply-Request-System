<?php

require_once __DIR__ . '/database.php';

class Supplies
{
    private $pdo;

    public $id = "";
    public $supply_categories_id = "";
    public $name = "";
    public $unit_of_supply = "";
    public $price_per_unit = "";
    public $stock_quantity = "";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    private function logStockChange(int $supplyId, int $changeAmount, string $reason, ?int $requestId = null)
    {
        $query = $this->pdo->prepare("SELECT stock_quantity FROM supplies WHERE id = ?");
        $query->execute([$supplyId]);
        $currentStock = (int)$query->fetchColumn();
        
        $newQuantity = $currentStock + $changeAmount;

        $sql = "INSERT INTO stock_logs (supplies_id, requests_id, change_amount, new_quantity, reason)
                VALUES (:supplies_id, :requests_id, :change_amount, :new_quantity, :reason)";
        
        $logQuery = $this->pdo->prepare($sql);
        $logQuery->execute([
            ':supplies_id' => $supplyId,
            ':requests_id' => $requestId,
            ':change_amount' => $changeAmount,
            ':new_quantity' => $newQuantity,
            ':reason' => $reason
        ]);
    }

    public function addSupply()
    {
        $sql = "INSERT INTO supplies (supply_categories_id, name, unit_of_supply, price_per_unit, stock_quantity) 
                VALUES (:supply_categories_id, :name, :unit_of_supply, :price_per_unit, :stock_quantity)";

        $query = $this->pdo->prepare($sql);
        $success = $query->execute([
            ':supply_categories_id' => $this->supply_categories_id,
            ':name' => $this->name,
            ':unit_of_supply' => $this->unit_of_supply,
            ':price_per_unit' => $this->price_per_unit,
            ':stock_quantity' => $this->stock_quantity
        ]);

        if ($success) {
            $supplyId = $this->pdo->lastInsertId();
            $this->logStockChange($supplyId, (int)$this->stock_quantity, "Initial stock added");
        }
        
        return $success;
    }

    public function updateSupply(int $supplyId, string $name, int $categoryId, string $unit, float $price): bool
    {
        $sql = "UPDATE supplies SET name = ?, supply_categories_id = ?, unit_of_supply = ?, price_per_unit = ? WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$name, $categoryId, $unit, $price, $supplyId]);
    }

    public function updateStock(int $supplyId, int $changeAmount, string $reason): bool
    {
        $this->logStockChange($supplyId, $changeAmount, $reason);
        $sql = "UPDATE supplies SET stock_quantity = stock_quantity + ? WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$changeAmount, $supplyId]);
    }

    public function deleteSupply(int $supplyId): bool
    {
        $sql = "DELETE FROM supplies WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        return $query->execute([$supplyId]);
    }

    public function deductStock(int $supplyId, int $quantityToDeduct, int $requestId)
    {
        $reason = "Released for Request #" . $requestId;
        $this->logStockChange($supplyId, -$quantityToDeduct, $reason, $requestId);

        $sql = "UPDATE supplies SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
        
        $query = $this->pdo->prepare($sql);
        
        return $query->execute([
            ':quantity' => $quantityToDeduct,
            ':id' => $supplyId
        ]);
    }
    
    public function viewAllSupply($search = "")
    {
        $sql = "SELECT * FROM supplies WHERE name LIKE CONCAT('%', :search, '%') 
                ORDER BY name ASC";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":search", $search);
        $query->execute();
        
        return $query->fetchAll();
    }

    public function searchSupplyNames($search = "")
    {
        $sql = "SELECT name FROM supplies WHERE name LIKE CONCAT('%', :search, '%')";
        
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":search", $search);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSupplyByName($name = "")
    {
        $sql = "SELECT id, name FROM supplies WHERE name = :name LIMIT 1";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":name", $name);
        $query->execute();
        return $query->fetch();
    }

    public function getSupplyNameById($supplyId = "")
    {
        $sql = "SELECT name FROM supplies WHERE id = :id";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id", $supplyId);
        $query->execute();
        $result = $query->fetch();
        
        return $result ? $result["name"] : "";
    }

    public function getSupplyById(int $supplyId): ?array
    {
        $sql = "SELECT * FROM supplies WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$supplyId]);
        $result = $query->fetch();
        return $result ?: null;
    }

    public function getLowStockCount(int $threshold = 5): int
    {
        $sql = "SELECT COUNT(id) FROM supplies WHERE stock_quantity <= ?";
        $query = $this->pdo->prepare($sql);
        $query->bindValue(1, $threshold, PDO::PARAM_INT);
        $query->execute();
        return (int)$query->fetchColumn();
    }

    public function getInventoryValueByCategory(): array
    {
        $sql = "SELECT sc.name, SUM(s.price_per_unit * s.stock_quantity) as total_value
                FROM supplies s
                JOIN supply_categories sc ON s.supply_categories_id = sc.id
                GROUP BY sc.name
                HAVING total_value > 0
                ORDER BY total_value DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function triggerStockNotifications(int $supplyId)
    {
        $supply = $this->getSupplyById($supplyId);
        if (!$supply) return;
    
        $newStock = (int)$supply['stock_quantity'];
        $supplyName = $supply['name'];
        $shouldNotify = false;
        $db_message = "";
    
        if ($newStock <= 0) {
            $shouldNotify = true;
            $db_message = "URGENT: '{$supplyName}' is now out of stock.";
        } elseif ($newStock <= STOCK_LOW_THRESHOLD) {
            $shouldNotify = true;
            $db_message = "Alert: '{$supplyName}' is low on stock ({$newStock} remaining).";
        }
    
        if ($shouldNotify) {
            $notification = new Notification($this->pdo);
            $userObj = new Users($this->pdo);
            $recipients = array_merge($userObj->getUsersByRole('Processor'), $userObj->getUsersByRole('Admin'));
        
            $link = "processor/index.php?page=supply-inventory";
            $email_subject = $db_message;
            $email_body = "<h2>Inventory Alert</h2><p>{$db_message}</p><p><a href='http://localhost/Office-Supply-Request-System/{$link}'>View Inventory</a></p>";
        
            $processedEmails = [];
            foreach ($recipients as $recipient) {
                if (!in_array($recipient['email'], $processedEmails)) {
                     $notification->createNotification($recipient['id'], $db_message, $link, $recipient['email'], $email_subject, $email_body);
                     $processedEmails[] = $recipient['email'];
                }
            }
        }
    }
}