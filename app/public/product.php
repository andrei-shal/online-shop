<?php

require_once __DIR__ . '/../db/models/Products.php';

session_start();

$id = (int)(isset($_GET['id']) ? $_GET['id'] : -1);

if ($id >= 0) {
    try {
        $product = (new Products())->getById($id);

        if ($product) {
            $productTitle = $product['title'];
        }
    } catch (Exception $e) {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OnlineShop - <?= isset($productTitle) ? $productTitle : "ошибка" ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/script/product.js"></script>
        <link href="/style/product.css" rel="stylesheet">
    </head>
    <body>
    <?php require_once __DIR__ . '/../ui/header.php'; ?>

    <?php
    if (isset($error)) {
        require_once __DIR__ . "/../ui/errors/server-error.php";
    } else {
        if (isset($product)) {
            ?>
            <div class="container py-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="row g-4">

                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center bg-light rounded-4 p-3" style="min-height: 350px;">
                            <?php if (!empty($product['image_path'])): ?>
                                <img
                                        src="<?= htmlspecialchars($product['image_path']) ?>"
                                        alt="<?= htmlspecialchars($product['title']) ?>"
                                        class="img-fluid rounded-3"
                                        style="max-height: 400px; object-fit: contain; width: 100%;"
                                >
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-image mb-3 opacity-50" viewBox="0 0 16 16">
                                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                    </svg>
                                    <p class="mb-0 fw-medium">Изображение отсутствует</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6 d-flex flex-column justify-content-between ps-md-4">
                            <div>
                                <a href="/" class="btn btn-sm btn-light text-muted rounded-3 mb-3 d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                                    </svg>
                                    Назад в каталог
                                </a>

                                <h1 class="fw-bold text-dark h2 mb-2"><?= htmlspecialchars($product['title']) ?></h1>

                                <div class="mb-4">
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-semibold">В наличии</span>
                                </div>

                                <hr class="text-muted opacity-25">

                                <div class="my-4">
                                    <span class="text-muted d-block fs-6 mb-1">Цена:</span>
                                    <span class="fw-extrabold text-primary display-5" style="letter-spacing: -1px;">
                            <?= number_format($product['price'] / 100, 2, '.', ' ') ?> <span class="fs-2 fw-bold">руб.</span>
                        </span>
                                </div>

                                <div class="my-4">
                                    <h6 class="fw-bold text-dark mb-2">Описание товара</h6>
                                    <p class="text-muted lh-base">
                                        <?= nl2br(htmlspecialchars(isset($product['description']) ? $product['description'] : 'У этого товара пока нет детального описания. Но он чертовски хорош, раз вы на него смотрите!')) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="bg-light p-3 rounded-4 mt-auto">
                                <div class="row g-3 align-items-center">

                                    <div class="col-auto">
                                        <div class="d-flex align-items-center bg-white rounded-3 p-1 border shadow-sm" style="max-width: 130px;">
                                            <button type="button" class="btn btn-sm btn-link text-secondary p-1 px-2 border-0 text-decoration-none fw-bold" id="detail-minus">-</button>
                                            <input type="number" id="detail-count" class="form-control form-control-sm text-center fw-semibold border-0 p-0 text-dark" value="1" min="1" max="99" style="width: 35px; box-shadow: none; background: transparent;">
                                            <button type="button" class="btn btn-sm btn-link text-secondary p-1 px-2 border-0 text-decoration-none fw-bold" id="detail-plus">+</button>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <button type="button"
                                                class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2 add-to-cart-button"
                                                id="detail-add-btn"
                                                data-product="<?= $product['id'] ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
                                                <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
                                                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                            </svg>
                                            Добавить в корзину
                                        </button>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <?php
        } else {
            require_once __DIR__ . "/../ui/errors/product-not-found.php";
        }
    }
    ?>

    <?php require_once __DIR__ . '/../ui/cart.php'; ?>

    <?php require_once __DIR__ . '/../ui/alerts.php'; ?>
    </body>
</html>