<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin'); 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка — Пользователи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">👟 Обувной Магазин</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../profile.php">👤 <?= e(getUser()['name']) ?></a>
            <a class="nav-link" href="/api/auth.php?action=logout">🚪 Выйти</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>⚙️ Админка — Управление пользователями</h1>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="products.php">Товары</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="users.php">Пользователи</a>
        </li>
    </ul>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
            while ($u = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <select class="form-select form-select-sm role-select" data-user-id="<?= $u['id'] ?>">
                            <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                            <option value="manager" <?= $u['role'] === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Админ</option>
                        </select>
                    </td>
                    <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger block-btn" 
                                data-user-id="<?= $u['id'] ?>" 
                                data-action="<?= $u['role'] === 'blocked' ? 'unblock' : 'block' ?>">
                            <?= $u['role'] === 'blocked' ? 'Разблокировать' : 'Заблокировать' ?>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.role-select').forEach(select => {
    select.onchange = async () => {
        const userId = select.dataset.userId;
        const role = select.value;
        try {
            await fetch('/api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set_role', user_id: userId, role })
            });
            location.reload();
        } catch (e) {
            alert('Ошибка');
        }
    };
});

document.querySelectorAll('.block-btn').forEach(btn => {
    btn.onclick = async () => {
        const userId = btn.dataset.userId;
        const action = btn.dataset.action;
        try {
            await fetch('/api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, user_id: userId })
            });
            location.reload();
        } catch (e) {
            alert('Ошибка');
        }
    };
});
</script>
</body>
</html>