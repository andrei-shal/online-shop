<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../db/models/Products.php';
require_once __DIR__ . '/../../../../db/models/Users.php';
require_once __DIR__ . '/../../../../services/S3Service.php';


session_start();

$data = json_decode(file_get_contents('php://input'), true);

$title = isset($data['title']) ? trim($data['title']) : '';
$price = isset($data['price']) ? (int)$data['price'] : 0;
$description = isset($data['description']) ? trim($data['description']) : '';
$imageBase64 = isset($data['image']) ? $data['image'] : '';

try {
    $users = new Users();
    if (!isset($_SESSION['user']) || !in_array($users->getRole($_SESSION['user']), ['SUPER', 'ADMIN'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Доступ запрещен'
        ]);
        exit;
    }

    if ($price <= 0 || empty($title) || empty($imageBase64)) {
        echo json_encode([
            'success' => false,
            'error' => 'Некорректные параметры запроса'
        ]);
        exit;
    }

    if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
        $fileExtension = strtolower($type[1]);

        $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);

        $decodedImage = base64_decode($imageBase64);

        if ($decodedImage === false) {
            echo json_encode([
                'success' => false,
                'error' => 'Не удалось декодировать изображение'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Неверный формат изображения'
        ]);
        exit;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode([
            'success' => false,
            'error' => 'Разрешены только форматы изображений jpg, png, webp и svg'
        ]);
        exit;
    }

    $tmpFile = tmpfile();
    $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];
    file_put_contents($tmpFilePath, $decodedImage);

    $newFileName = md5(time() . uniqid()) . '.' . $fileExtension;

    $s3 = S3Service::getInstance();
    $imagePath = $s3->uploadFile($tmpFilePath, $newFileName, 'products-images');

    fclose($tmpFile);

    if (!$imagePath) {
        echo json_encode([
            'success' => false,
            'error' => 'Ошибка при сохранении файла в S3 хранилище'
        ]);
        exit;
    }

    $products = new Products();

    if ($products->create($title, $price, $description, $imagePath)) echo json_encode(['success' => true]);
    else echo json_encode([
        'success' => false,
        'error' => "Ошибка создания товара"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Сервис временно недоступен"
    ]);
}