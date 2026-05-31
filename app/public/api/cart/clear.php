<?php

require_once __DIR__ . '/../../../db/models/Carts.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

if (isset($_SESSION['user'])) {
    try {
        $carts = new Carts();

        if (!$carts->deleteByUserId($_SESSION['user'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => "Ошибка очистки"
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Сервис временно недоступен"
        ]);
        exit;
    }
} else {
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
}

echo json_encode([
    'success' => true
]);
