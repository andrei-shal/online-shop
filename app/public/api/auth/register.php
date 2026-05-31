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
$email = trim(isset($data['email']) ? $data['email'] : '');
$password = trim(isset($data['password']) ? $data['password'] : '');
$password2 = trim(isset($data['password2']) ? $data['password2'] : '');

if (empty($username) || empty($email) || empty($password) || empty($password2)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => "Заполните всю информацию"
    ]);
} else {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "Email введен не корректно"
        ]);
        exit;
    }

    if (mb_strlen($password) < 8) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "Пароль должен содержать 8 и более символов"
        ]);
        exit;
    }

    if ($password !== $password2) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "Пароли не совпадают"
        ]);
        exit;
    }

    try {
        $users = new Users();
        $carts = new Carts();

        if ($users->existsByUsername($username)) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => "Имя пользователя уже занято"
            ]);
            exit;
        }

        if ($users->existsByEmail($email)) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => "Email уже зарегистрирован"
            ]);
            exit;
        }

        if ($users->create($username, $email, $password)) {
            try {
                $user = $users->getByUsername($username);

                if (isset($_SESSION['cart']) && !$carts->mergeCart($user['id'], $_SESSION['cart'])) throw new Exception("db error");

                unset($_SESSION['cart']);
                $_SESSION['user'] = $user['id'];
            } catch (Exception $e) {}

            http_response_code(201);
            echo json_encode([
                'success' => true
            ]);
            exit;
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Ошибка регистрации"
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => "Сервис временно недоступен"
        ]);
    }
}
