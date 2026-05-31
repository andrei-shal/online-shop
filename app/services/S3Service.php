<?php

class S3Service {
    private static $instance = null;
    private $internalEndpoint;
    private $publicUrl;

    private function __construct() {
        $this->internalEndpoint = 'http://s3filer:8888';
        $this->publicUrl = '/data';
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function uploadFile($fileTmpPath, $fileName, $bucket) {
        $bucket = trim($bucket, '/');
        $url = $this->internalEndpoint . '/' . $bucket . '/' . $fileName;

        $fileData = file_get_contents($fileTmpPath);
        if ($fileData === false) {
            return false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $fileData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,

            CURLOPT_HTTPHEADER => [
                'Content-Type: ' . mime_content_type($fileTmpPath),
                'Content-Length: ' . strlen($fileData)
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            return false;
        }

        if ($httpCode === 200 || $httpCode === 201) {
            return $this->publicUrl . '/' . $bucket . '/' . $fileName;
        }

        return false;
    }

    public function deleteFile($imagePath, $bucket) {
        if (empty($imagePath)) {
            return false;
        }

        $fileName = basename($imagePath);

        $url = $this->internalEndpoint . $bucket . $fileName;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($response === false) {
            return false;
        }

        if ($httpCode === 200 || $httpCode === 404) {
            return true;
        }

        return false;
    }
}