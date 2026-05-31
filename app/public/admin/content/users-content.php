<?php

require_once __DIR__ . '/../../../db/models/Users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = (int)(isset($_GET['page']) ? $_GET['page'] : 1);
$pageSize = (int)(isset($_GET['pageSize']) ? $_GET['pageSize'] : 1);

if ($page < 1)       $page = 1;
if ($pageSize <= 0)  $pageSize = 1;
if ($pageSize > 100) $pageSize = 100;

$rawFilters = isset($_GET['filters']) ? $_GET['filters'] : [];
$filters = is_array($rawFilters) ? $rawFilters : [];

try {
    $users = new Users();

    if (isset($_SESSION['user']) || $users->getRole($_SESSION['user']) === 'SUPER') {
        $usersList = $users->getFilteredPage($page, $pageSize, $filters);
        $totalUsers = $users->getTotalCount($filters);
        $totalPages = (int)ceil($totalUsers / $pageSize);

        if (empty($usersList)) {
            ?>
            <div class="row justify-content-center">
                <div class="col-md-6 text-center py-5">
                    <div class="alert alert-custom p-4 bg-white rounded-4 shadow-sm border-start border-danger border-4">
                        <h4 class="text-danger mb-2">Пользователи не найдены</h4>
                        <p class="text-muted mb-0">Попробуйте изменить параметры поиска</p>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small uppercase">
                        <tr>
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Роль / Статус</th>
                            <th class="text-end pe-4" style="width: 140px;">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usersList as $user): ?>
                            <tr class="user-row cursor-pointer" data-user-id="<?= $user['id'] ?>" style="cursor: pointer;">

                                <td class="ps-4 fw-medium text-secondary">
                                    #<?= $user['id'] ?>
                                </td>

                                <td>
                                    <a href="/admin/user.php?id=<?= $user['id'] ?>" class="text-decoration-none text-dark fw-semibold user-link">
                                        <?= htmlspecialchars(isset($user['name']) ? $user['name'] : 'Без имени') ?>
                                    </a>
                                </td>

                                <td class="text-muted">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>

                                <td>
                                    <div onclick="event.stopPropagation();">
                                        <select
                                                class="form-select form-select-sm rounded-3 w-auto status-select fw-medium <?= $user['role'] === 'SUPER' ? 'border-danger text-danger' : 'border-secondary' ?>"
                                                data-user="<?= $user['id'] ?>"
                                                <?= $_SESSION['user'] === $user['id'] ? 'disabled' : '' ?>
                                        >
                                            <option value="USER" <?= $user['role'] === 'USER' ? 'selected' : '' ?>>User</option>
                                            <option value="MANAGER" <?= $user['role'] === 'MANAGER' ? 'selected' : '' ?>>Manager</option>
                                            <option value="ADMIN" <?= $user['role'] === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                                            <?php if ($user['role'] === 'SUPER'): ?>
                                                <option value="SUPER" selected disabled>Super Admin</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </td>

                                <td class="text-end pe-4" onclick="event.stopPropagation();">
                                    <a href="/admin/user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-light rounded-3 px-3">
                                        Профиль
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="d-flex justify-content-center mt-4">
                    <ul class="pagination gap-1 shadow-sm rounded-3 p-1 bg-white">

                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <button class="pagination-button page-link rounded-3 border-0 bg-light text-dark" data-page="<?= $page - 1 ?>">&laquo;</button>
                        </li>

                        <?php
                        $range = 2;
                        $showDots = true;

                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == 1 || $i == $totalPages || abs($i - $page) <= $range) {
                                $showDots = true;
                                ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <button class="pagination-button page-link rounded-3 border-0 <?= $i === $page ? 'bg-primary text-white' : 'bg-light text-dark' ?>" data-page="<?= $i ?>">
                                        <?= $i ?>
                                    </button>
                                </li>
                                <?php
                            } elseif ($showDots) {
                                ?>
                                <li class="page-item disabled">
                                    <span class="pagination-button page-link rounded-3 border-0 bg-light text-muted">...</span>
                                </li>
                                <?php
                                $showDots = false;
                            }
                        }
                        ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <button class="pagination-button page-link rounded-3 border-0 bg-light text-dark" data-page="<?= $page + 1 ?>">&raquo;</button>
                        </li>

                    </ul>
                </nav>
            <?php endif;
        }
    } else {
        require_once __DIR__ . '/../../../ui/admin/errors/unauthorized.php';
    }
} catch (Exception $e) {
    require_once __DIR__ . "/../../../ui/errors/server-error.php";
}