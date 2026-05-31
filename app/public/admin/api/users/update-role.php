<?php

require_once __DIR__ . '/../../../../db/models/Users.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$data = json_decode(file_get_contents('php://input'), true);

$targetUserId = isset($data['userId']) ? (int)$data['userId'] : 0;
$newRole = isset($data['role']) ? trim((string)$data['role']) : '';

try {
    $users = new Users();
    if (!isset($_SESSION['user']) || $users->getRole($_SESSION['user']) !== 'SUPER') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Доступ запрещен'
        ]);
        exit;
    }

    if ($targetUserId <= 0 || !in_array($newRole, ['USER', 'MANAGER', 'ADMIN'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Некорректные параметры запроса'
        ]);
        exit;
    }

    $targetUser = $users->getById($targetUserId);
    if (!$targetUser) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Пользователь не найден'
        ]);
        exit;
    }

    $currentRoleOfTarget = $users->getRole($targetUserId);
    if ($currentRoleOfTarget === 'SUPER') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Запрещено изменять права супер админа'
        ]);
        exit;
    }

    $result = false;

    if ($newRole === 'USER') {
        $result = $users->clearRole($targetUserId);
    } else {
        $result = $users->changeRole($targetUserId, $newRole);
    }

    if ($result) echo json_encode(['success' => true]);
    else echo json_encode([
        'success' => false,
        'error' => "Ошибка изменения роли"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Сервис временно недоступен"
    ]);
}