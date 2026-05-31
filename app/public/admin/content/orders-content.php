<?php

require_once __DIR__ . '/../../../db/models/Orders.php';
require_once __DIR__ . '/../../../db/models/Users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = (int)(isset($_GET['page']) ? $_GET['page'] : 1);
$pageSize = (int)(isset($_GET['pageSize']) ? $_GET['pageSize'] : 1);

if ($pageSize <= 0) {
    $pageSize = 1;
}

if ($pageSize > 100) {
    $pageSize = 100;
}

if ($page < 1) {
    $page = 1;
}

$rawFilters = isset($_GET['filters']) ? $_GET['filters'] : [];
$filters = is_array($rawFilters) ? $rawFilters : [];

try {
    if (isset($_SESSION['user']) and in_array(((new Users())->getRole($_SESSION['user'])), ['SUPER', 'ADMIN', 'MANAGER'])) {
        $orders = new Orders();

        $ordersPage = $orders->getFilteredPage($page, $pageSize, $filters);
        $totalOrders = $orders->getTotalCount($filters);
        $totalPages = (int)ceil($totalOrders / $pageSize);

        if (empty($ordersPage)) {
            ?>
            <div class="row justify-content-center">
                <div class="col-md-6 text-center py-5">
                    <div class="alert alert-custom p-4 bg-white rounded-4 shadow-sm border-start border-danger border-4">
                        <h4 class="text-danger mb-2">Упс! Таких заказов нет</h4>
                        <p class="text-muted mb-0">Попробуйте изменить фильтры</p>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="min-width: 800px;">
                        <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="width: 120px;">Заказ</th>
                            <th class="py-3">Email покупателя</th>
                            <th class="py-3">Сумма</th>
                            <?php if (!isset($hideSelect)): ?>
                                <th class="py-3" style="width: 220px;">Статус заказа</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ordersPage as $order): ?>
                            <tr class="order-row" data-order-id="<?= $order['id'] ?>" style="cursor: pointer;">
                                <td class="ps-4 fw-semibold text-secondary">
                                    #<?= $order['id'] ?>
                                </td>
                                <td class="fw-medium text-dark">
                                    <?= htmlspecialchars($order['email']) ?>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= number_format($order['total_sum'] / 100, 2, '.', ' ') ?> руб.
                                </td>
                                <?php if (!isset($hideSelect)): ?>
                                    <td class="pe-4" onclick="event.stopPropagation()">
                                        <select class="form-select form-select-sm status-select fw-medium rounded-3" data-order="<?= $order['id'] ?>">
                                            <option value="CREATED" <?= $order['status'] === 'CREATED' ? 'selected' : '' ?>>Created</option>
                                            <option value="CONFIRMED" <?= $order['status'] === 'CONFIRMED' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="COMPLITED" <?= $order['status'] === 'COMPLITED' ? 'selected' : '' ?>>Completed</option>
                                            <option value="CANCELED" <?= $order['status'] === 'CANCELED' ? 'selected' : '' ?>>Canceled</option>
                                        </select>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <nav>
                        <ul class="pagination pagination-md gap-1 mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <button class="page-link pagination-button border-0 rounded-3 shadow-sm" data-page="<?= $page - 1 ?>">&laquo;</button>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                    <button class="page-link pagination-button border-0 rounded-3 shadow-sm fw-medium" data-page="<?= $i ?>"><?= $i ?></button>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <button class="page-link pagination-button border-0 rounded-3 shadow-sm" data-page="<?= $page + 1 ?>">&raquo;</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif;
        }
    } else {
        require_once __DIR__ . '/../../../ui/admin/errors/unauthorized.php';
    }
} catch (Exception $e) {
    ?>
    <?php
    require_once __DIR__ . "/../../../ui/errors/server-error.php";
}
?>
