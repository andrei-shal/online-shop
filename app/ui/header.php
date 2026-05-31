<script type="module" src="/script/ui/header.js"></script>

<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/">OnlineShop</a>

        <?php if (isset($_SESSION['user'])): ?>
            <?php if (isset($accountPage) and $accountPage): ?>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light position-relative" id="logout-button">
                        Выйти
                    </button>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center gap-3">
                    <a href="/account.php" class="btn btn-light position-relative">
                        Личный кабинет
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="d-flex align-items-center gap-3">
                <a href="/login.php" class="btn btn-light position-relative">
                    Войти
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>