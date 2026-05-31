<?php

class Database {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {

            $attempts = 5;

            while ($attempts > 0) {
                try {
                    self::$instance = new PDO(
                        "mysql:host=db;dbname=shop;charset=utf8",
                        "user",
                        "secret",
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                        ]
                    );
                    break;

                } catch (PDOException $e) {
                    $attempts--;
                    sleep(2);

                    if ($attempts === 0) {
                        throw new Exception("Database is unavailable");
                    }
                }
            }
        }

        return self::$instance;
    }
}