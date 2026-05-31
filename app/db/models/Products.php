<?php

require_once __DIR__ . '/../Database.php';

class Products {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);

        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function update($id, $title, $price, $description, $imagePath = null) {
        if ($imagePath) {
            $sql = "UPDATE products SET title = ?, price = ?, description = ?, image_path = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$title, $price, $description, $imagePath, $id]);
        } else {
            $sql = "UPDATE products SET title = ?, price = ?, description = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$title, $price, $description, $id]);
        }
    }

    public function create($title, $price, $description, $imagePath) {
        $sql = "INSERT INTO products (title, price, description, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$title, $price, $description, $imagePath]);
    }

    public function getFilteredPage($page, $pageSize, $filters = []) {
        if ($page < 1) {
            throw new Exception("The page must be a positive number.");
        }

        $offset = ($page - 1) * $pageSize;

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        $sql .= $this->solveFilters($filters, $params);

        $allowedSorts = [
            'price_asc' => 'price ASC',
            'price_desc' => 'price DESC',
            'title_asc' => 'title ASC',
            'title_desc' => 'title DESC',
        ];

        if (isset($filters['sort']) and array_key_exists($filters['sort'], $allowedSorts)) {
            $sql .= " ORDER BY " . $allowedSorts[$filters['sort']];
        } else {
            $sql .= " ORDER BY id DESC";
        }

        $sql .= " LIMIT $offset, $pageSize";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM products WHERE 1=1";
        $params = [];

        $sql .= $this->solveFilters($filters, $params);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function solveFilters($filters, &$params) {
        $sql = "";

        if (!is_array($filters)) {
            return $sql;
        }

        if (!empty($filters)) {
            if (isset($filters['title']) && trim((string)$filters['title']) !== '') {
                $sql .= " AND title LIKE ?";
                $params[] = "%" . $filters['title'] . "%";
            }

            if (isset($filters['minPrice']) && trim((string)$filters['minPrice']) !== '') {
                $sql .= " AND price >= ?";
                $params[] = $filters['minPrice'];
            }

            if (isset($filters['maxPrice']) && trim((string)$filters['maxPrice']) !== '') {
                $sql .= " AND price <= ?";
                $params[] = $filters['maxPrice'];
            }
        }

        return $sql;
    }
}