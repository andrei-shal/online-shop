<?php

require_once __DIR__ . '/../../../db/models/Carts.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$data = json_decode(file_get_contents('php://input'), true);

$product = isset($data['product']) ? (int)$data['product'] : -1;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : -1;

if ($product < 0 || $quantity < 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => "Неверно задан продукт или количество"
    ]);
} else {
    if (isset($_SESSION['user'])) {
        try {
            $carts = new Carts();

            if (!$carts->addProduct($_SESSION['user'], $product, $quantity)) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => "Ошибка добавления"
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
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
        if (isset($_SESSION['cart'][$product])) $_SESSION['cart'][$product] += $quantity;
        else $_SESSION['cart'][$product] = $quantity;
    }

    echo json_encode([
        'success' => true
    ]);
}
