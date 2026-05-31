<?php

require_once __DIR__ . '/../../../db/models/Users.php';
require_once __DIR__ . '/../../../db/models/Carts.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

if (isset($_SESSION['user'])) {
    echo json_encode([
        'success' => true
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$username = trim(isset($data['username']) ? $data['username'] : '');
$password = trim(isset($data['password']) ? $data['password'] : '');

if (empty($username) || empty($password)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => "Заполните всю информацию"
    ]);
} else {
    try {
        $users = new Users();
        $carts = new Carts();

        $user = $users->getByUsernameOrEmail($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            if (isset($_SESSION['cart']) && !$carts->mergeCart($user['id'], $_SESSION['cart'])) throw new Exception("db error");

            unset($_SESSION['cart']);
            $_SESSION['user'] = $user['id'];
            echo json_encode([
                'success' => true
            ]);
            exit;
        }

        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => "Неверное имя пользователя или пароль"
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Сервис временно недоступен"
        ]);
    }
}
