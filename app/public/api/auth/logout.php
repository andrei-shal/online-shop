<?php

header('Content-Type: application/json; charset=utf-8');

session_start();

if (isset($_SESSION['user'])) {
    session_destroy();
}

echo json_encode([
    'success' => true
]);