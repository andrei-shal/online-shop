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

    if (!in_array($role, ['SUPER', 'ADMIN', 'MANAGER'])) {
        header('Location: /login.php');
        exit();
    }
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
        <title>Admin - заказы</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/admin/script/panel.js"></script>
    </head>
    <body>
        <?php require_once __DIR__ . "/../../ui/admin/header.php"; ?>

        <div class="container pb-5">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 class="fw-bold tracking-tight mb-1">Управление заказами</h1>
                </div>
            </div>

            <form id="filter-form" class="card border-0 bg-light p-4 rounded-4 mb-5 shadow-sm">
                <div class="row g-3">

                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold text-muted small">Поиск по Email покупателя</label>
                        <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                        </svg>
                                    </span>
                            <input
                                    type="text"
                                    name="filters[email]"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Введите email покупателя..."
                                    value="<?= htmlspecialchars(isset($_GET['filters']['email']) ? $_GET['filters']['email'] : '') ?>"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-muted small">Статус заказа</label>
                        <select name="filters[status]" class="form-select">
                            <?php $currentStatus = isset($_GET['filters']['status']) ? $_GET['filters']['status'] : ''; ?>
                            <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>Все статусы</option>
                            <option value="CREATED" <?= $currentStatus === 'CREATED' ? 'selected' : '' ?>>Создан (Created)</option>
                            <option value="CONFIRMED" <?= $currentStatus === 'CONFIRMED' ? 'selected' : '' ?>>Подтвержден (Confirmed)</option>
                            <option value="COMPLITED" <?= $currentStatus === 'COMPLETED' ? 'selected' : '' ?>>Выполнен (Completed)</option>
                            <option value="CANCELED" <?= $currentStatus === 'CANCELED' ? 'selected' : '' ?>>Отменен (Canceled)</option>
                        </select>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="/admin/panel.php" class="btn btn-outline-secondary px-4 rounded-3 fw-medium">Сбросить</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">Применить</button>
                </div>
            </form>

            <div id="orders-content">
                <?php
                $_GET['page'] = (int)(isset($_GET['page']) ? $_GET['page'] : 1);
                $_GET['pageSize'] = 10;

                require_once __DIR__ . '/content/orders-content.php';
                ?>
            </div>
        </div>

        <?php require_once __DIR__ . '/../../ui/alerts.php'; ?>
    </body>
</html>