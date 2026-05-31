<?php

session_start();

if (isset($_SESSION['user'])) {
    header('Location: /account.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OnlineShop - вход</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script type="module" src="/script/login.js"></script>
    </head>
    <body class="bg-light d-flex flex-column min-vh-100">
        <?php require_once __DIR__ . '/../ui/header.php'; ?>

        <div class="container my-auto py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-8 col-md-6 col-lg-4">

                    <div class="card border-0 shadow-sm rounded-4 p-4 p-sm-5 bg-white">

                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark mb-1">Рады видеть!</h2>
                            <p class="text-muted small">Введи свои данные для входа в личный кабинет</p>
                        </div>

                        <form id="login-form" autocomplete="on">

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold text-secondary small">Логин или Email</label>
                                <input
                                        type="text"
                                        class="form-control form-control-lg rounded-3 fs-6"
                                        id="username"
                                        name="username"
                                        placeholder="ivanov777"
                                        required
                                >
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-semibold text-secondary small mb-0">Пароль</label>
                                </div>
                                <input
                                        type="password"
                                        class="form-control form-control-lg rounded-3 fs-6"
                                        id="password"
                                        name="password"
                                        placeholder="••••••••"
                                        required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-2.5 rounded-3 fw-semibold shadow-sm mb-3">
                                Войти в аккаунт
                            </button>

                            <div class="text-center mt-3">
                                <span class="text-muted small">Ещё нет аккаунта?</span>
                                <a href="/register.php" class="text-decoration-none small fw-medium text-primary ms-1">Создать аккаунт</a>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../ui/alerts.php'; ?>
    </body>
</html>