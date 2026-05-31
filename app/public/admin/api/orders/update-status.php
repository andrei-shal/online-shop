<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../db/models/Orders.php';
require_once __DIR__ . '/../../../../db/models/Users.php';
require_once __DIR__ . '/../../../../services/EmailService.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

$orderId = isset($data['orderId']) ? (int)$data['orderId'] : 0;
$newStatus = trim(isset($data['status']) ? strtoupper($data['status']) : '');


try {
    $users = new Users();
    if (!isset($_SESSION['user']) || !in_array($users->getRole($_SESSION['user']), ['SUPER', 'ADMIN', 'MANAGER'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Доступ запрещен'
        ]);
        exit;
    }

    if ($orderId <= 0 || empty($newStatus)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Некорректные параметры запроса'
        ]);
        exit;
    }

    $allowedStatuses = ['CREATED', 'CONFIRMED', 'COMPLETED', 'CANCELLED'];
    if (!in_array($newStatus, $allowedStatuses)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "Передан недопустимый статус заказа"
        ]);
        exit;
    }

    $orders = new Orders();

    $orderData = $orders->getById($orderId);
    if (!$orderData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => "Заказ не найден"
        ]);
        exit;
    }

    if ($orderData['status'] === $newStatus) {
        echo json_encode(['success' => true]);
        exit;
    }

    $isUpdated = $orders->updateStatus($orderId, $newStatus);

    if (!$isUpdated) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Не удалось обновить статус в базе данных"
        ]);
        exit;
    }

    $emailService = EmailService::getInstance();
    $emailService->SendUpdateStatusEmail(
        $orderId,
        $orderData['email'],
        $newStatus,
        $orderData['created_at']
    );

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Сервис временно недоступен"
    ]);
}