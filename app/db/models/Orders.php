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

    public function sendOrderEmail($orderId, $email) {
        try {
            $stmt = $this->pdo->prepare("SELECT created_at, total_sum FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) return false;

            $items = $this->getOrderItems($orderId);
            if (empty($items)) return false;

            $itemsHtml = '';
            foreach ($items as $item) {
                $title = htmlspecialchars($item['title']);
                $qty = (int)$item['quantity'];
                $price = $item['price'] / 100;
                $itemTotal = $price * $qty;

                $itemsHtml .= "
                    <tr style='border-bottom: 1px solid #eef2f3;'>
                        <td style='padding: 12px 0; font-size: 15px; color: #2d3748;'>{$title}</td>
                        <td style='padding: 12px 10px; font-size: 15px; color: #718096; text-align: center;'>{$qty} шт.</td>
                        <td style='padding: 12px 0; font-size: 15px; font-weight: bold; color: #2d3748; text-align: right; white-space: nowrap;'>" . number_format($itemTotal, 2, '.', ' ') . " руб.</td>
                    </tr>
                ";
            }

            $formattedDate = date('d.m.Y в H:i', strtotime($order['created_at']));
            $totalDisplay = number_format($order['total_sum'] / 100, 2, '.', ' ');

            $message = "
                <!DOCTYPE html>
                <html lang='ru'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Заказ #{$orderId}</title>
                    </head>
                    <body style='margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                        <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f6f8; padding: 20px 10px;'>
                            <tr>
                                <td align='center'>
                                    <table width='100%' max-width='600' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                                        <tr>
                                            <td style='padding: 30px;'>
                                                <h2 style='margin: 0 0 5px 0; color: #2d3748; font-size: 22px; font-weight: 700;'>Подтверждение заказа</h2>
                                                <p style='margin: 0 0 25px 0; font-size: 14px; color: #718096;'>Заказ #{$orderId} от {$formattedDate}</p>
                                                
                                                <h3 style='margin: 0 0 15px 0; color: #2d3748; font-size: 16px; border-bottom: 2px solid #0d6efd; padding-bottom: 6px; display: inline-block;'>Email получателя</h3>
                                                <p style='margin: 0 0 30px 0; font-size: 15px; color: #4a5568;'>" . htmlspecialchars($email) . "</p>
                    
                                                <h3 style='margin: 0 0 15px 0; color: #2d3748; font-size: 16px; border-bottom: 2px solid #0d6efd; padding-bottom: 6px; display: inline-block;'>Состав заказа</h3>
                                                
                                                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 25px;'>
                                                    {$itemsHtml}
                                                </table>
                    
                                                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f8fafc; border-radius: 12px; padding: 20px;'>
                                                    <tr>
                                                        <td style='font-size: 16px; font-weight: 600; color: #4a5568;'>Итого к оплате:</td>
                                                        <td style='font-size: 22px; font-weight: 700; color: #0d6efd; text-align: right; white-space: nowrap;'>{$totalDisplay} руб.</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </body>
                </html>
            ";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            return mail($email, "Ваш заказ #{$orderId} успешно оформлен!", $message, $headers);
        } catch (Exception $e) {
            return false;
        }
    }
}