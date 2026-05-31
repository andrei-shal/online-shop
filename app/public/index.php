<?php

session_start();

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OnlineShop</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script type="module" src="/script/index.js"></script>

        <link href="/style/products-content.css" rel="stylesheet">
    </head>
    <body>
        <?php require_once __DIR__ . '/../ui/header.php'; ?>

        <div class="container pb-5">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 class="fw-bold tracking-tight mb-1">Каталог товаров</h1>
                </div>
            </div>

            <form id="filter-form" class="card border-0 bg-light p-4 rounded-4 mb-5 shadow-sm">
                <div class="row g-3">

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-muted small">Поиск</label>
                        <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </span>
                            <input
                                    type="text"
                                    name="filters[title]"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Введите название товара..."
                                    value="<?= htmlspecialchars(isset($_GET['filters']['title']) ? $_GET['filters']['title'] : '') ?>"
                            >
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small">Цена от (руб.)</label>
                        <input
                                type="number"
                                step="0.01"
                                name="filters[minPrice]"
                                class="form-control"
                                placeholder="0.00"
                                value="<?= htmlspecialchars(isset($_GET['filters']['minPrice']) ? $_GET['filters']['minPrice']/100 : '') ?>"
                        >
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small">Цена до (руб.)</label>
                        <input
                                type="number"
                                step="0.01"
                                name="filters[maxPrice]"
                                class="form-control"
                                placeholder="9999"
                                value="<?= htmlspecialchars(isset($_GET['filters']['maxPrice']) ? $_GET['filters']['maxPrice']/100 : '') ?>"
                        >
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-muted small">Сортировка</label>
                        <select name="filters[sort]" class="form-select">
                            <?php $currentSort = isset($_GET['filters']['sort']) ? $_GET['filters']['sort'] : 'id_desc'; ?>
                            <option value="id_desc" <?= $currentSort === 'id_desc' ? 'selected' : '' ?>>Новинки</option>
                            <option value="price_asc" <?= $currentSort === 'price_asc' ? 'selected' : '' ?>>Сначала дешевые</option>
                            <option value="price_desc" <?= $currentSort === 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
                            <option value="title_asc" <?= $currentSort === 'title_asc' ? 'selected' : '' ?>>По алфавиту (А-Я)</option>
                        </select>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3 fw-medium">Сбросить</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">Применить</button>
                </div>
            </form>

            <div id="products-content">
                <?php
                $_GET['page'] = (int)(isset($_GET['page']) ? $_GET['page'] : 1);
                $_GET['pageSize'] = 6;

                require_once __DIR__ . '/content/products-content.php';
                ?>
            </div>
        </div>

        <?php require_once __DIR__ . '/../ui/cart.php'; ?>

        <?php require_once __DIR__ . '/../ui/alerts.php'; ?>
    </body>
</html>