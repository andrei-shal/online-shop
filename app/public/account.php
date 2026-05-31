<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OnlineShop - личный кабинет</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script type="module" src="/script/account.js"></script>
        <link href="/style/cart.css" rel="stylesheet">
    </head>
    <body>
        <?php
        $accountPage = true;
        require_once __DIR__ . '/../ui/header.php';
        ?>

        <div class="py-5 px-5" id="make-order-content">
            <?php require_once __DIR__ . '/../public/content/make-order-content.php'; ?>
        </div>

        <div class="py-5 px-5" id="orders-content">
            <?php require_once __DIR__ . '/../public/content/orders-content.php'; ?>
        </div>

        <?php require_once __DIR__ . '/../ui/alerts.php'; ?>
    </body>
</html>