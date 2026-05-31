<link href="/style/cart.css" rel="stylesheet">
<script type="module" src="/script/ui/cart.js"></script>

<button class="btn btn-primary floating-cart-btn" data-bs-toggle="offcanvas" data-bs-target="#cart">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bag-dash-fill" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0M6 9.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1z"/>
    </svg>
    <span>Корзина</span>
    <span id="cart-badge-count"></span>
</button>

<div class="offcanvas offcanvas-end border-0 shadow" tabindex="-1" id="cart" style="border-radius: 24px 0 0 24px;">
    <div class="offcanvas-header p-4 border-bottom">
        <h5 class="fw-bold mb-0">Ваша корзина</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div id="cart-content">
            <?php require_once __DIR__ . '/../public/content/cart-content.php'; ?>
        </div>
    </div>
</div>