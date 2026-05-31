<?php

require_once __DIR__ . '/../../db/models/Users.php';
require_once __DIR__ . '/../../db/models/Orders.php';

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

    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $orders = new Orders();

    $order = $orderId > 0 ? $orders->getById($orderId) : null;

    if (!$order) {
        header('Location: /admin/panel.php');
        exit();
    }

    $orderItems = $orders->getOrderItems($orderId);
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
        <title>Admin — заказ #<?= $order['id'] ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/admin/script/order.js"></script>
        <link href="/admin/style/order.css" rel="stylesheet">
    </head>
    <body class="bg-body-tertiary">
        <?php require_once __DIR__ . "/../../ui/admin/header.php"; ?>

        <div class="container pb-5">
            <div class="mb-4">
                <a href="/admin/panel.php" class="btn btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    Назад к списку заказов
                </a>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-5">
                <div>
                    <h1 class="fw-bold tracking-tight mb-1">Заказ #<?= $order['id'] ?></h1>
                    <p class="text-muted mb-0">Покупатель: <span class="fw-medium text-dark"><?= htmlspecialchars($order['email']) ?></span></p>
                </div>

                <div id="orders-content" style="min-width: 240px;">
                    <label class="form-label fw-semibold text-muted small mb-1">Статус заказа</label>
                    <select class="form-select status-select fw-medium rounded-3" data-order="<?= $order['id'] ?>">
                        <option value="CREATED" <?= $order['status'] === 'CREATED' ? 'selected' : '' ?>>Created</option>
                        <option value="CONFIRMED" <?= $order['status'] === 'CONFIRMED' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="COMPLITED" <?= $order['status'] === 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                        <option value="CANCELED" <?= $order['status'] === 'CANCELLED' ? 'selected' : '' ?>>Canceled</option>
                    </select>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="p-4 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark">Содержимое заказа</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Товар</th>
                                    <th class="py-3 text-center">Кол-во</th>
                                    <th class="py-3 text-end">Цена</th>
                                    <th class="pe-4 py-3 text-end">Итого</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orderItems as $item):
                                    $itemTotal = $item['quantity'] * $item['price'];
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product-img-container rounded-3 border d-flex align-items-center justify-content-center p-1 flex-shrink-0">
                                                    <?php if (!empty($item['image_path'])): ?>
                                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-image text-muted" viewBox="0 0 16 16">
                                                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars(isset($item['title']) ? $item['title'] : 'Товар удален из каталога') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center fw-medium text-secondary">
                                            <?= $item['quantity'] ?> шт.
                                        </td>
                                        <td class="text-end fw-medium text-dark">
                                            <?= number_format($item['price'] / 100, 2, '.', ' ') ?> руб.
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-dark">
                                            <?= number_format($itemTotal / 100, 2, '.', ' ') ?> руб.
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <h5 class="fw-bold mb-4 text-dark">Информация о платеже</h5>

                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted small fw-medium">Дата оформления</span>
                            <span class="text-dark fw-medium small">
                                <?= date('d.m.Y H:i', strtotime(isset($order['created_at']) ? $order['created_at'] : 'now')) ?>
                            </span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="text-muted fw-semibold">Сумма заказа:</span>
                            <span class="fs-4 fw-bold text-primary">
                                <?= number_format($order['total_sum'] / 100, 2, '.', ' ') ?> руб.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../../ui/alerts.php'; ?>
    </body>
</html>