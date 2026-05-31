<?php

echo "=== Проверка и создание SUPER-администратора ===\n";

$superUser  = getenv('SUPER_ADMIN_USER');
$superEmail = getenv('SUPER_ADMIN_EMAIL');
$superPass  = getenv('SUPER_ADMIN_PASS');

if (!$superUser || !$superEmail || !$superPass) {
    echo "Ошибка: Переменные администратора не найдены в .env\n";
    exit(1);
}

echo "Ожидание подключения к базе данных...";
for ($i = 0; $i < 10; $i++) {
    try {
        require_once __DIR__ . '/db/Database.php';
        $pdo = Database::getInstance();
        break;
    } catch (Exception $e) {
        echo ".";
        sleep(2);
    }
}
echo " Подключено!\n";

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$superUser, $superEmail]);

    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("SELECT 1 FROM admins WHERE user_id = ?");
        $stmt->execute([$user["id"]]);

        $role = $stmt->fetch();
    }

    if (!$user || !$role) {
        if (!$user) {
            $passwordHash = password_hash($superPass, PASSWORD_BCRYPT);

            $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmtUser->execute([$superUser, $superEmail, $passwordHash]);
            $userId = $pdo->lastInsertId();
        } else {
            $userId = $user["id"];
        }

        $stmtAdmin = $pdo->prepare("INSERT INTO admins (user_id, role) VALUES (?, 'SUPER')");
        $stmtAdmin->execute([$userId]);

        echo "SUPER-администратор [{$superUser}] успешно добавлен в базу!\n";
    } else {
        echo "SUPER-администратор уже существует в базе. Пропуск.\n";
    }
} catch (Exception $e) {
    echo "Ошибка СУБД при создании админа: " . $e->getMessage() . "\n";
    exit(1);
}