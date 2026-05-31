<?php

require_once __DIR__ . '/../../db/models/Products.php';

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
    $productsModel = new Products();
    $products = $productsModel->getFilteredPage($page, $pageSize, $filters);
    $totalProducts = $productsModel->getTotalCount($filters);
    $totalPages = ceil($totalProducts / $pageSize);

    if (empty($products)) {
        ?>
        <div class="row justify-content-center">
            <div class="col-md-6 text-center py-5">
                <div class="alert alert-custom p-4 bg-white rounded-4 shadow-sm border-start border-danger border-4">
                    <h4 class="text-danger mb-2">Упс! Таких товаров у нас нет</h4>
                    <p class="text-muted mb-0">Попробуйте изменить фильтры</p>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card product-card h-100 shadow-sm" data-product="<?= $product['id'] ?>">

                        <div class="product-img-wrapper">
                            <?php if (!empty($product['image_path'])): ?>
                                <img
                                    src="<?= htmlspecialchars($product['image_path']) ?>"
                                    class="product-img"
                                    alt="<?= htmlspecialchars($product['title']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="no-photo-placeholder w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-image text-muted mb-2" viewBox="0 0 16 16">
                                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                    </svg>
                                    <small class="text-muted fw-medium">Фото временно отсутствует</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div class="mb-4">
                                <h4 class="product-title" title="<?= htmlspecialchars($product['title']) ?>">
                                    <?= htmlspecialchars($product['title']) ?>
                                </h4>
                                <p class="product-price">
                                    <?= number_format($product['price'] / 100, 2, '.', ' ') . ' руб.' ?>
                                </p>
                            </div>

                            <button class="btn btn-primary btn-add-cart w-100 d-flex align-items-center justify-content-center gap-2 add-to-cart-button" data-product="<?= $product['id'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi py-0" viewBox="0 0 16 16">
                                    <path d="M8 7.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0v-1.5H6a.5.5 0 0 1 0-1h1.5V8a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                                </svg>
                                В корзину
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-center mt-4">
                <ul class="pagination gap-1">

                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <button class="pagination-button page-link rounded-3 border-0 bg-light text-dark" data-page="<?= $page - 1 ?>">&laquo;</button>
                    </li>

                    <?php
                    $range = 2;
                    $showDots = true;

                    for ($i = 1; $i <= $totalPages; $i++) {
                        if ($i == 1 || $i == $totalPages || abs($i - $page) <= $range) {

                            $showDots = true;
                            ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <button class="pagination-button page-link rounded-3 border-0 <?= $i === $page ? 'bg-primary text-white' : 'bg-light text-dark' ?>" data-page="<?= $i ?>">
                                    <?= $i ?>
                                </button>
                            </li>
                            <?php
                        }
                        elseif ($showDots) {
                            ?>
                            <li class="page-item disabled">
                                <span class="pagination-button page-link rounded-3 border-0 bg-light text-muted">...</span>
                            </li>
                            <?php
                            $showDots = false;
                        }
                    }
                    ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <button class="pagination-button page-link rounded-3 border-0 bg-light text-dark" data-page="<?= $page + 1 ?>">&raquo;</button>
                    </li>

                </ul>
            </nav>
        <?php endif;
    }
} catch (Exception $e) {
    require_once __DIR__ . "/../../ui/errors/server-error.php";
}
?>