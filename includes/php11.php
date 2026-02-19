<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$cartId = getCurrentCartId();
$items = [];
$total = 0;

if ($cartId) {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, ps.size 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        JOIN product_sizes ps ON ci.size_id = ps.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Корзина — Обувной Магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">👟 Обувной Магазин</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/">Каталог</a>
            <?php if (isLoggedIn()): ?>
                <a class="nav-link" href="profile.php">👤 <?= e(getUser()['name']) ?></a>
                <a class="nav-link" href="/api/auth.php?action=logout">🚪 Выйти</a>
            <?php else: ?>
                <a class="nav-link" href="login.php">🔐 Войти</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>🛒 Корзина</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">
            Корзина пуста. <a href="/">Выберите товары</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="list-group">
                    <?php foreach ($items as $item): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h5><?= e($item['name']) ?></h5>
                                <p class="mb-1">Размер: <?= $item['size'] ?>, <?= formatPrice($item['price']) ?></p>
                                <span class="badge bg-primary">×<?= $item['quantity'] ?></span>
                            </div>
                            <div>
                                <span class="text-primary"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                                <button class="btn btn-sm btn-outline-danger ms-2 btn-remove-from-cart"
                                        data-product-id="<?= $item['product_id'] ?>"
                                        data-size="<?= $item['size'] ?>">
                                    Удалить
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Итого: <span class="text-primary"><?= formatPrice($total) ?></span></h5>
                        <hr>
                        <?php if (isLoggedIn()): ?>
                            <a href="profile.php#orders" class="btn btn-success w-100">Оформить заказ</a>
                            <small class="text-muted d-block mt-2">Форма заказа — в личном кабинете</small>
                        <?php else: ?>
                            <a href="login.php?redirect=<?= urlencode('/cart.php') ?>" class="btn btn-primary w-100">
                                Войдите, чтобы оформить заказ
                            </a>
                        <?php endif; ?>
                        <a href="/" class="btn btn-outline-secondary w-100 mt-2">Продолжить покупки</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>