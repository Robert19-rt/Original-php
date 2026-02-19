<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    die('Товар не найден');
}

$sizes = getAvailableSizes($id);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($product['name']) ?> — Обувной Магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">👟 Обувной Магазин</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="cart.php">🛒 Корзина <span class="badge bg-secondary cart-count"></span></a>
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
    <div class="row">
        <div class="col-md-6">
            <div id="main-img-container" class="mb-3">
                <img id="main-img" src="https://via.placeholder.com/500x400/e0e0e0/555?text=<?= urlencode(e($product['name'])) ?>" 
                     class="img-fluid rounded" alt="<?= e($product['name']) ?>">
            </div>
            <div class="product-gallery">
                <img src="https://via.placeholder.com/100x100/e0e0e0/555?text=1" class="gallery-img active" alt="1">
                <img src="https://via.placeholder.com/100x100/e0e0e0/555?text=2" class="gallery-img" alt="2">
                <img src="https://via.placeholder.com/100x100/e0e0e0/555?text=3" class="gallery-img" alt="3">
            </div>
        </div>
        <div class="col-md-6">
            <h1><?= e($product['name']) ?></h1>
            <p class="text-muted"><?= e($product['brand']) ?> • <?= e($product['category']) ?></p>
            <h2 class="text-primary"><?= formatPrice($product['price']) ?></h2>
            
            <div class="mt-4">
                <h5>Размеры в наличии:</h5>
                <?php if (empty($sizes)): ?>
                    <p class="text-danger">Нет в наличии</p>
                <?php else: ?>
                    <div class="mb-3">
                        <?php foreach ($sizes as $s): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input size-select" type="radio" 
                                       name="size" id="size_<?= $s['size'] ?>" 
                                       value="<?= $s['size'] ?>" required>
                                <label class="form-check-label" for="size_<?= $s['size'] ?>">
                                    <?= $s['size'] ?> (<?= $s['quantity'] ?> шт.)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <button class="btn btn-success btn-lg w-100 btn-add-to-cart" 
                        data-product-id="<?= $product['id'] ?>" <?= empty($sizes) ? 'disabled' : '' ?>>
                    <?= empty($sizes) ? 'Нет в наличии' : 'Добавить в корзину' ?>
                </button>
            </div>

            <div class="mt-4">
                <h5>Описание</h5>
                <p><?= nl2br(e($product['description'])) ?></p>
                <p><strong>Материал:</strong> <?= e($product['material']) ?></p>
                <p><strong>Цвет:</strong> <?= e($product['color']) ?></p>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>