<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <?php if (isset($role)): ?>
            <a class="navbar-brand fw-bold text-primary" href="/admin/panel.php">Админ панель</a>

            <?php if (in_array($role, ['SUPER', 'ADMIN'])): ?>
                <a class="btn btn-light position-relative" href="/admin/products.php">Товары</a>

                <?php if ($role === 'SUPER'): ?>
                    <a class="btn btn-light position-relative" href="/admin/users.php">Пользователи</a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>