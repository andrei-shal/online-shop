<?php

require_once __DIR__ . '/../Database.php';

class Users {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll() {
        $sql = "
            SELECT 
                u.id, 
                u.username, 
                u.email, 
                COALESCE(a.role, 'USER') AS role
            FROM users u
            LEFT JOIN admins a ON u.id = a.user_id
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getByUsernameOrEmail($identifier) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function existsByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE username = ?");
        $stmt->execute([$username]);

        return (bool)$stmt->fetch();
    }

    public function existsByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = ?");
        $stmt->execute([$email]);

        return (bool)$stmt->fetch();
    }

    public function create($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $passwordHash]);
    }

    public function isAdmin($id) {
        return $this->getRole($id) !== null;
    }

    public function getRole($id) {
        $stmt = $this->pdo->prepare("SELECT role FROM admins WHERE user_id = ?");
        $stmt->execute([$id]);
        $admin = $stmt->fetch();

        return ($admin && isset($admin['role'])) ? $admin['role'] : null;
    }

    public function clearRole($id) {
        $stmt = $this->pdo->prepare("DELETE FROM admins WHERE user_id = ?");
        return $stmt->execute([$id]);
    }

    public function changeRole($id, $role) {
        $stmt = $this->pdo->prepare("
            INSERT INTO admins (user_id, role) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE role = ?
        ");
        return $stmt->execute([$id, $role, $role]);
    }

    public function getFilteredPage($page, $pageSize, $filters = []) {
        if ($page < 1) {
            throw new Exception("The page must be a positive number.");
        }

        $offset = ($page - 1) * $pageSize;

        $sql = "
            SELECT 
                u.id, 
                u.username AS name,
                u.email, 
                COALESCE(a.role, 'USER') AS role
            FROM users u
            LEFT JOIN admins a ON u.id = a.user_id
            WHERE 1=1
        ";
        $params = [];

        $sql .= $this->solveFilters($filters, $params);

        $sql .= " ORDER BY id DESC LIMIT $offset, $pageSize";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTotalCount($filters = []) {
        $sql = "
            SELECT COUNT(*) 
            FROM users u
            LEFT JOIN admins a ON u.id = a.user_id
            WHERE 1=1
        ";
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
            if (isset($filters['username']) && trim((string)$filters['username']) !== '') {
                $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
                $params[] = "%" . $filters['username'] . "%";
                $params[] = "%" . $filters['username'] . "%";
            }

            if (isset($filters['id']) && trim((string)$filters['id']) !== '') {
                $sql .= " AND u.id = ?";
                $params[] = (int)$filters['id'];
            }

            if (isset($filters['role']) && trim((string)$filters['role']) !== '') {
                if ($filters['role'] === 'USER') {
                    $sql .= " AND a.role IS NULL";
                } else {
                    $sql .= " AND a.role = ?";
                    $params[] = $filters['role'];
                }
            }
        }

        return $sql;
    }
}