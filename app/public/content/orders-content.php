<?php
require_once __DIR__ . '/../../db/models/Orders.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    require_once __DIR__ . '/../../ui/errors/unauthorized.php';
} else {
    try {
        $orders = new Orders();

        $userOrders = $orders->getByUserId($_SESSION['user']);

        if (empty($userOrders)) {
            ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-seam text-muted mb-3" viewBox="0 0 16 16">
                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.405 5.11l2.595 1.037 6.15-2.46zM1 4.305v5.4a.5.5 0 0 0 .275.445l6 3a.5.5 0 0 0 .45 0l6-3a.5.5 0 0 0 .275-.445v-5.4l-6.15 2.46a.5.5 0 0 1-.37 0zM2.585 5.5l5.415 2.166 5.415-2.166-5.415-2.166z"/>
                </svg>
                <p class="text-muted fw-medium fs-5 mb-0">У вас пока нет оформленных заказов</p>
            </div>
            <?php
        } else {
            ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($userOrders as $order): ?>
                    <div class="card border-0 bg-white p-4 rounded-4 shadow-sm border">

                        <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom pb-3 mb-3 gap-2">
                            <div>
                                <h5 class="mb-1 text-dark fw-bold">Заказ #<?= $order['id'] ?></h5>
                                <span class="text-muted small">
                                    От <?= date('d.m.Y в H:i', strtotime($order['created_at'])) ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-bold text-primary">
                                    <?= number_format($order['total_sum'] / 100, 2, '.', ' ') ?> руб.
                                </span>
                                <div class="mt-1">
                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5">
                                        <?= htmlspecialchars($order['email']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($orders->getOrderItems($order['id']) as $item): ?>
                                <div class="card border-0 bg-light p-2.5 rounded-3 d-flex flex-row align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <div class="rounded-2 bg-white p-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image text-muted" viewBox="0 0 16 16">
                                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-truncate fw-semibold text-dark mb-0" style="font-size: 0.95rem;">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </span>
                                    </div>
                                    <div class="text-muted text-end flex-shrink-0 ps-2" style="font-size: 0.9rem;">
                                        <span class="fw-medium text-dark"><?= $item['quantity'] ?> шт.</span>
                                        <span class="mx-1">×</span>
                                        <?= number_format($item['price'] / 100, 2, '.', ' ') ?> руб.
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        }
    } catch (Exception $e) {
        require_once __DIR__ . "/../../ui/errors/server-error.php";
    }
}
?>
