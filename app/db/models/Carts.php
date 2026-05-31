<?php

require_once __DIR__ . '/../Database.php';

class Carts {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM products_in_cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getProductsByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                cic.product_id, 
                cic.quantity, 
                p.price, 
                p.title 
            FROM products_in_cart cic
            JOIN products p ON cic.product_id = p.id
            WHERE cic.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function deleteByUserId($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM products_in_cart WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function addProduct($userId, $productId, $quantity) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products_in_cart (user_id, product_id, quantity) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        return $stmt->execute([$userId, $productId, $quantity, $quantity]);
    }

    public function removeProduct($userId, $productId, $quantity) {
        $stmt = $this->pdo->prepare("SELECT * FROM products_in_cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);

        $productInCart = $stmt->fetch();

        if (!$productInCart) {
            return false;
        }

        if ($productInCart['quantity'] > $quantity) {
            $stmt = $this->pdo->prepare("UPDATE products_in_cart SET quantity = quantity - ? WHERE user_id = ? AND product_id = ?");
            return $stmt->execute([$quantity, $userId, $productId]);
        }

        $stmt = $this->pdo->prepare("DELETE FROM products_in_cart WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }

    public function mergeCart($userId, $sessionCart) {
        if (empty($sessionCart)) {
            return true;
        }

        try {
            $this->pdo->beginTransaction();
            foreach ($sessionCart as $productId => $quantity) {
                if (!$this->addProduct($userId, (int)$productId, (int)$quantity)) throw new Exception("adding product error");
            }
            return $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }
}