<?php

require_once __DIR__ . '/../../db/models/Users.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

try {
    $users = new Users();
    $role = $users->getRole($_SESSION['user']);

    if ($role !== 'SUPER') {
        header('Location: /login.php');
        exit();
    }

    $targetId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $targetUser = $targetId > 0 ? $users->getById($targetId) : null;

    if (!$targetUser) {
        header('Location: /admin/users.php');
        exit();
    }

    $targetUserRole = $users->getRole($targetId);
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>admin</title>

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </head>
        <body>
            <?php require_once __DIR__ . "/../../ui/errors/server-error.php"; ?>
        </body>
    </html>
    <?php
    exit;
}

?>


<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin — <?= $targetUser['username'] ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/admin/script/user.js"></script>
    </head>
    <body class="bg-body-tertiary">
        <?php require_once __DIR__ . "/../../ui/admin/header.php"; ?>

        <div class="container pb-5">
            <div class="mb-4">
                <a href="/admin/users.php" class="btn btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    Назад к списку пользователей
                </a>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 class="fw-bold tracking-tight mb-1">Профиль пользователя</h1>
                    <p class="text-muted mb-0">Управление правами доступа и личными данными</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center p-4 bg-white">
                        <p class="text-muted small mb-3"><?= htmlspecialchars($targetUser['email']) ?></p>

                        <div class="pt-2 border-top w-100">
                            <span class="text-muted small d-block mb-1">Системный ID</span>
                            <span class="fw-semibold text-secondary">#<?= $targetUser['id'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold text-dark mb-4">Настройки безопасности</h5>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-muted small">Уровень доступа (Роль)</label>

                            <?php if ($targetUserRole === 'SUPER'): ?>
                                <div class="alert alert-danger rounded-3 p-3 mb-0 border-0 shadow-sm d-flex align-items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-lock-fill text-danger" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 62 62 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"/>
                                    </svg>
                                    <div>
                                        <strong class="d-block text-danger">SUPER ADMIN</strong>
                                        <span class="small text-muted">Права главного создателя системы не могут быть изменены.</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="max-width: 350px;">
                                    <select
                                        id="user-role-select"
                                        class="form-select status-select fw-medium rounded-3"
                                        data-user-id="<?= $targetUser['id'] ?>"
                                    >
                                        <option value="USER" <?= $targetUserRole === 'USER' ? 'selected' : '' ?>>Обычный пользователь (User)</option>
                                        <option value="MANAGER" <?= $targetUserRole === 'MANAGER' ? 'selected' : '' ?>>Менеджер (Manager)</option>
                                        <option value="ADMIN" <?= $targetUserRole === 'ADMIN' ? 'selected' : '' ?>>Администратор (Admin)</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="orders-content">
            <?php
            $_GET['page'] = 1;
            $_GET['pageSize'] = 10;
            $hideSelect = true;
            $_GET['filters'] = [
                'user_id' => $targetUser['id']
            ];

            require_once __DIR__ . '/content/orders-content.php';
            ?>
        </div>

        <?php require_once __DIR__ . '/../../ui/alerts.php'; ?>
    </body>
</html>