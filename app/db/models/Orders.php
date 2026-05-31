<?php

require_once __DIR__ . '/../Database.php';

class Orders {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);

        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($userId, $email, $products, $sum) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, email, total_sum) VALUES (?, ?, ?)");

            if ($stmt->execute([$userId, $email, $sum])) {
                $orderId = $this->pdo->lastInsertId();

                $stmt = $this->pdo->prepare("INSERT INTO products_in_order (order_id, product_id, quantity) VALUES (?, ?, ?)");

                foreach ($products as $productId => $quantity) {
                    if (!$stmt->execute([$orderId, $productId, $quantity])) {
                        throw new Exception("An error occured while adding product to order");
                    }
                }
            } else {
                throw new Exception("Creating order failed");
            }

            $stmtDel = $this->pdo->prepare("DELETE FROM products_in_cart WHERE user_id = ?");
            if (!$stmtDel->execute([$userId])) {
                throw new Exception("Failed to clear cart during order creation");
            }

            if ($this->pdo->commit()) return $orderId;
            return false;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function getOrderItems($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT pio.quantity, p.title, p.price, p.image_path 
            FROM products_in_order pio
            JOIN products p ON pio.product_id = p.id
            WHERE pio.order_id = ?
        ");
        $stmt->execute([$orderId]);

        return $stmt->fetchAll();
    }

    public function getFilteredPage($page, $pageSize, $filters = []) {
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT id, email, total_sum, status, created_at FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters)) {
            if (!empty($filters['user_id'])) {
                $sql .= " AND user_id=?";
                $params[] = $filters['user_id'];
            }
            if (!empty($filters['email'])) {
                $sql .= " AND email LIKE ?";
                $params[] = "%" . $filters['email'] . "%";
            }
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
        }

        $sql .= " ORDER BY id DESC LIMIT $offset, $pageSize";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters)) {
            if (!empty($filters['email'])) {
                $sql .= " AND email LIKE ?";
                $params[] = "%" . $filters['email'] . "%";
            }
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus($orderId, $newStatus) {
        $stmt = $this->pdo->prepare("
            UPDATE orders 
            SET status = ?
            WHERE id = ?
        ");

        return (bool)$stmt->execute([$newStatus, $orderId]);
    }
}