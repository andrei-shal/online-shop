<?php

require_once __DIR__ . '/../../../db/models/Users.php';
require_once __DIR__ . '/../../../db/models/Orders.php';
require_once __DIR__ . '/../../../db/models/Carts.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$data = json_decode(file_get_contents('php://input'), true);

$email = trim(isset($data['email']) ? $data['email'] : '');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => "Необходимо войти в аккаунт"
    ]);
    exit;
}

try {
    $orders = new Orders();
    $carts = new Carts();

    $user = (new Users())->getById($_SESSION['user']);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => "Такого пользователя не существует"
        ]);
        exit;
    }

    if (empty($email)) {
        $email = $user['email'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "Email введен не корректно"
        ]);
        exit;
    }

    $productsInCart = $carts->getProductsByUserId($_SESSION['user']);
    $cart = array();
    $sum = 0;

    if (empty($productsInCart)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Корзина пуста"
        ]);
        exit;
    }

    foreach ($productsInCart as $productInCart) {
        $productId = $productInCart['product_id'];
        $quantity = (int)$productInCart['quantity'];

        $cart[$productId] = $quantity;
        $sum += (int)$productInCart['price'] * $quantity;
    }

    $order = $orders->create($user['id'], $email, $cart, $sum);

    if (!$order) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Ошибка создания заказа"
        ]);
        exit;
    }

    $orders->sendOrderEmail($order, $email);

    http_response_code(201);
    echo json_encode([
        'success' => true,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Сервис временно недоступен"
    ]);
}