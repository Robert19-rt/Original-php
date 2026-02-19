<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

requireRole('user');

$user = getUser();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Личный кабинет — Обувной Магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">👟 Обувной Магазин</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="cart.php">🛒 Корзина <span class="badge bg-secondary cart-count"></span></a>
            <a class="nav-link" href="/api/auth.php?action=logout">🚪 Выйти</a>
            <?php if (in_array($user['role'], ['admin', 'manager'])): ?>
                <a class="nav-link" href="admin/products.php">⚙️ Админка</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>👤 Личный кабинет</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= e($user['name']) ?></h5>
                    <p class="text-muted"><?= e($user['email']) ?></p>
                    <p>Роль: <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'secondary') ?>">
                        <?= $user['role'] ?>
                    </span></p>
                    <?php if ($user['phone']): ?>
                        <p>Телефон: <?= e($user['phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <h2 id="orders">📦 Мои заказы</h2>
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">У вас ещё нет заказов</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($orders as $o): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>Заказ #<?= $o['id'] ?></strong>
                                <span class="badge bg-<?= 
                                    $o['status'] === 'delivered' ? 'success' : 
                                    ($o['status'] === 'cancelled' ? 'danger' : 'secondary') 
                                ?>">
                                    <?= $o['status'] ?>
                                </span>
                            </div>
                            <small>от <?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></small>
                            <p class="mb-1">Сумма: <?= formatPrice($o['total_amount']) ?></p>
                            <?php if ($o['shipping_address']): ?>
                                <p class="mb-0">Адрес: <?= e($o['shipping_address']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2 class="mt-5">✏️ Редактировать профиль</h2>
            <form method="POST" action="/api/profile.php">
                <div class="mb-3">
                    <label class="form-label">Имя</label>
                    <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                    <small class="form-text text-muted">Email изменить нельзя</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>