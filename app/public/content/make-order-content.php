<?php

require_once __DIR__ . '/../../db/models/Products.php';
require_once __DIR__ . '/../../db/models/Carts.php';
require_once __DIR__ . '/../../db/models/Users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    require_once __DIR__ . '/../../ui/errors/unauthorized.php';
} else {
    try {
        $products = new Products();
        $carts = new Carts();

        $productsInCart = $carts->getByUserId($_SESSION['user']);
        $cart = array();

        foreach ($productsInCart as $productInCart) {
            $cart[$productInCart['product_id']] = $productInCart['quantity'];
        }

        if (empty($cart)) {
            ?>
            <div class="text-center py-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-bag-x text-muted mb-3" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M6.146 8.146a.5.5 0 0 1 .708 0L8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 0 1 0-.708"/>
                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                </svg>
                <p class="text-muted fw-medium fs-5 mb-0">Корзина пока пуста</p>
            </div>
            <?php
        } else {
            $users = new Users();

            $userEmail = '';
            $user = $users->getById($_SESSION['user']);

            if ($user) {
                $userEmail = $user['email'];
            }

            $totalPrice = 0;
            ?>

            <div class="row g-4">

                <div class="col-12 col-lg-8">
                    <h4 class="fw-bold mb-4 text-dark d-none d-sm-block">Выбранные товары</h4>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($cart as $id => $quantity): ?>
                            <?php
                            $product = $products->getById($id);
                            if (!$product) continue;

                            $totalPrice += $product['price'] * $quantity;
                            ?>

                            <div class="card border-0 bg-light p-3 rounded-4 shadow-2xs">
                                <div class="row g-3 align-items-center text-center text-sm-start">

                                    <div class="col-12 col-sm-auto d-flex justify-content-center">
                                        <div class="rounded-3 bg-white p-2 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img
                                                    src="<?= htmlspecialchars($product['image_path']) ?>"
                                                    alt="<?= htmlspecialchars($product['title']) ?>"
                                                    style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                                >
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-image text-muted" viewBox="0 0 16 16">
                                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm d-flex flex-column justify-content-center">
                                        <h6 class="fw-semibold mb-1 text-dark text-wrap">
                                            <?= htmlspecialchars($product['title']) ?>
                                        </h6>
                                        <div class="fw-bold text-primary fs-5 text-nowrap">
                                            <?= number_format($quantity * $product['price'] / 100, 2, '.', ' ') ?> руб.
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-auto">
                                        <div class="d-flex align-items-center justify-content-center justify-content-sm-end gap-2 flex-nowrap mx-auto">
                                            <div class="d-flex align-items-center justify-content-between justify-content-sm-center bg-white rounded-3 p-1 shadow-sm border mx-auto" style="max-width: 160px;">
                                                <button class="btn btn-sm btn-link text-secondary p-1 px-3 border-0 text-decoration-none fw-bold minus-from-cart-button" data-product="<?= $id ?>">-</button>
                                                <span class="px-2 fw-semibold text-dark text-center" id="count" style="min-width: 32px; font-size: 0.95rem;"><?= $quantity ?></span>
                                                <button class="btn btn-sm btn-link text-secondary p-1 px-3 border-0 text-decoration-none fw-bold plus-to-cart-button" data-product="<?= $id ?>">+</button>
                                            </div>

                                            <button type="button"
                                                    class="btn btn-link text-muted p-2 remove-from-cart-button delete-icon-btn d-flex align-items-center justify-content-center rounded-3 bg-white border shadow-sm flex-shrink-0"
                                                    style="width: 38px; height: 38px;"
                                                    data-product="<?= $id ?>"
                                                    data-quantity="<?= $quantity ?>"
                                                    title="Удалить товар полностью">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                                                    <path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 2rem; z-index: 10;">

                        <h4 class="fw-bold mb-3 text-dark">Детали заказа</h4>
                        <hr class="text-muted opacity-25 my-3">

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="text-muted fw-semibold">Итого к оплате:</span>
                            <span class="fs-3 fw-bold text-dark">
                                <?= number_format($totalPrice / 100, 2, '.', ' ') ?> <span class="fs-5 fw-semibold">руб.</span>
                            </span>
                        </div>

                        <form id="order-form" onsubmit="return false;">
                            <div class="mb-4">
                                <label for="order-email" class="form-label fw-semibold text-secondary small">Email для связи и чека</label>
                                <input
                                    type="email"
                                    class="form-control form-control-lg rounded-3 fs-6"
                                    id="order-email"
                                    name="email"
                                    placeholder="example@mail.com"
                                    value="<?= htmlspecialchars($userEmail) ?>"
                                    required
                                >
                                <div class="form-text text-muted small mt-1.5" style="font-size: 0.75rem;">
                                    На этот адрес мы отправим информацию о статусе вашего заказа.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-3 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" id="buy-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-credit-card-2-front" viewBox="0 0 16 16">
                                    <path d="M14 3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
                                    <path d="M2 5.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
                                </svg>
                                Оформить заказ
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            <?php
        }
    } catch (Exception $e) {
        require_once __DIR__ . "/../../ui/errors/server-error.php";
    }
}
?>