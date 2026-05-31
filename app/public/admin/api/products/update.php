<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../db/models/Products.php';
require_once __DIR__ . '/../../../../db/models/Users.php';
require_once __DIR__ . '/../../../../services/S3Service.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? (int)$data['id'] : 0;

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


    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Некорректные параметры запроса'
        ]);
        exit;
    }

    $products = new Products();
    $product = $products->getById($id);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'error' => 'Товар не найден'
        ]);
        exit;
    }

    $title = isset($data['title']) ? trim($data['title']) : $product['title'];
    $price = isset($data['price']) ? (int)$data['price'] : $product['price'];
    $description = isset($data['description']) ? trim($data['description']) : $product['description'];
    $imageBase64 = isset($data['image']) ? $data['image'] : '';

    if ($price <= 0 || empty($title)) {
        echo json_encode([
            'success' => false,
            'error' => 'Некорректные параметры запроса'
        ]);
        exit;
    }

    $imagePath = $product['image_path'];
    $oldImage = null;

    if (!empty($imageBase64)) {
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

        $temp = $imagePath;
        $imagePath = $s3->uploadFile($tmpFilePath, $newFileName, 'products-images');
        fclose($tmpFile);

        if (!$imagePath) {
            echo json_encode([
                'success' => false,
                'error' => 'Ошибка при сохранении файла в S3 хранилище'
            ]);
            exit;
        }

        $oldImage = $temp;
    }

    $success = $products->update($id, $title, $price, $description, $imagePath);

    if ($success) {
        if ($oldImage) S3Service::getInstance()->deleteFile($oldImage, 'products-images');
        echo json_encode(['success' => true]);
    }
    else echo json_encode([
        'success' => false,
        'error' => "Ошибка изменения товара"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Сервис временно недоступен"
    ]);
}