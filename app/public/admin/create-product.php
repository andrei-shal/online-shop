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

    if (!in_array($role, ['SUPER', 'ADMIN'])) {
        header('Location: /login.php');
        exit();
    }
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <title>admin</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <title>Admin — добавление товара</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/admin/script/create-product.js"></script>
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

            <form id="product-create-form">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-5">
                    <div class="flex-grow-1">
                        <input type="text" name="title" class="form-control form-control-lg fw-bold tracking-tight text-dark fs-1 bg-white rounded-3 px-3 py-2" placeholder="Введите название товара" required>
                    </div>

                    <div>
                        <button type="submit" id="save-btn" class="btn btn-success px-4 py-2.5 rounded-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                            Создать товар
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden product-preview-box p-4 border d-flex flex-column align-items-center justify-content-center bg-white" style="min-height: 340px;">
                            <img id="product-image-preview" src="" alt="" class="product-img d-none">

                            <div id="product-image-placeholder" class="text-center text-muted py-5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-image text-body-secondary mb-3" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                </svg>
                                <p class="mb-0 small fw-medium">Выберите изображение товара</p>
                            </div>

                            <div class="w-100 mt-3">
                                <input type="file" name="image" id="product-file-input" class="form-control form-control-sm rounded-3" accept="image/*" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                            <h5 class="fw-bold text-dark mb-4">Параметры нового товара</h5>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold mb-1">Цена</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control form-control-lg fw-bold text-primary rounded-3" placeholder="0.00" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold mb-1">Описание товара</label>
                                <textarea name="description" class="form-control text-muted rounded-3 lh-base" rows="6" placeholder="Опишите характеристики и особенности товара..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php require_once __DIR__ . '/../../ui/alerts.php'; ?>
    </body>
</html>