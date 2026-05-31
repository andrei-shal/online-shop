<?php

require_once __DIR__ . '/../../db/models/Users.php';
require_once __DIR__ . '/../../db/models/Products.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

try {
    $users = new Users();
    $role = $users->getRole($_SESSION['user']);

    if (!in_array($role, ['SUPER', 'ADMIN'])) {
        header('Location: /login.php');
        exit();
    }

    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $products = new Products();
    $product = $productId > 0 ? $products->getById($productId) : null;

    if (!$product) {
        header('Location: /admin/products.php');
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
        <title>Admin — <?= htmlspecialchars($product['title']) ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/admin/script/product.js"></script>
        <link href="/admin/style/product.css" rel="stylesheet">
    </head>
    <body class="bg-body-tertiary">
        <?php require_once __DIR__ . "/../../ui/admin/header.php"; ?>

        <div class="container pb-5">
            <div class="mb-4">
                <a href="/admin/products.php" class="btn btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    Назад к каталогу
                </a>
            </div>

            <form id="product-form" data-product-id="<?= $product['id'] ?>">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-5">
                    <div>
                        <input type="text" name="title" class="form-control form-control-lg fw-bold tracking-tight border-0 bg-transparent p-0 fs-1 text-dark" value="<?= htmlspecialchars($product['title']) ?>" disabled required>
                        <p class="text-muted mb-0 mt-1">Системный ID: <span class="fw-semibold text-secondary">#<?= $product['id'] ?></span></p>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="edit-btn" class="btn btn-outline-primary px-4 py-2.5 rounded-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                            Редактировать
                        </button>
                        <button type="submit" id="save-btn" class="btn btn-success px-4 py-2.5 rounded-3 fw-semibold shadow-sm d-none align-items-center gap-2">
                            Сохранить изменения
                        </button>
                        <button type="button" id="cancel-btn" class="btn btn-light px-4 py-2.5 rounded-3 fw-medium d-none">
                            Отмена
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden product-preview-box p-4 border position-relative">
                            <?php if (!empty($product['image_path'])): ?>
                                <img id="product-image-view" src="<?= htmlspecialchars($product['image_path']) ?>" alt="" class="product-img">
                            <?php else: ?>
                                <div id="product-image-placeholder" class="text-center text-muted py-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-image text-body-secondary mb-3" viewBox="0 0 16 16">
                                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                    </svg>
                                    <p class="mb-0 small fw-medium">Нет изображения</p>
                                </div>
                            <?php endif; ?>

                            <div id="file-input-container" class="position-absolute bottom-0 start-0 w-100 p-3 bg-white bg-opacity-75 d-none border-top">
                                <input type="file" name="image" id="product-file-input" class="form-control form-control-sm rounded-3" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                            <h5 class="fw-bold text-dark mb-4">Параметры товара</h5>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold mb-1">Цена (в рублях)</label>
                                <div class="input-group edit-mode-group">
                                    <input type="number" step="0.01" name="price" class="form-control form-control-lg fw-bold text-primary border-0 bg-transparent p-0 fs-2" value="<?= number_format($product['price'] / 100, 2, '.', '') ?>" disabled required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold mb-1">Описание товара</label>
                                <textarea name="description" class="form-control text-muted border-0 bg-transparent p-0 lh-base" rows="6" disabled><?= htmlspecialchars(isset($product['description']) ? $product['description'] : '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php require_once __DIR__ . '/../../ui/alerts.php'; ?>
    </body>
</html>