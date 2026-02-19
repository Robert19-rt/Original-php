<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$category = $_GET['category'] ?? null;
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$size = $_GET['size'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$sql = "SELECT DISTINCT p.* FROM products p WHERE p.status = 'active'";
$params = [];

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
}
if ($minPrice !== null) {
    $sql .= " AND p.price >= ?";
    $params[] = (float)$minPrice;
}
if ($maxPrice !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = (float)$maxPrice;
}
if ($size !== null) {
    $sql .= " AND EXISTS (SELECT 1 FROM product_sizes ps WHERE ps.product_id = p.id AND ps.size = ? AND ps.quantity > 0)";
    $params[] = (float)$size;
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = ['men' => 'Мужская', 'women' => 'Женская', 'kids' => 'Детская'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Обувной Магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/"> Обувной Магазин</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="cart.php">
                🛒 Корзина <span class="badge bg-secondary cart-count"></span>
            </a>
            <?php if (isLoggedIn()): ?>
                <a class="nav-link" href="profile.php">👤 <?= e(getUser()['name']) ?></a>
                <a class="nav-link" href="/api/auth.php?action=logout">🚪 Выйти</a>
                <?php if (in_array(getUser()['role'], ['admin', 'manager'])): ?>
                    <a class="nav-link" href="admin/products.php"> Админка</a>
                <?php endif; ?>
            <?php else: ?>
                <a class="nav-link" href="login.php"> Войти / Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Каталог обуви</h1>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Категория</label>
            <select name="category" class="form-select">
                <option value="">Любая</option>
                <?php foreach ($categories as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $category === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Цена от</label>
            <input type="number" name="min_price" class="form-control" value="<?= e($minPrice) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">до</label>
            <input type="number" name="max_price" class="form-control" value="<?= e($maxPrice) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Размер</label>
            <input type="number" step="0.5" name="size" class="form-control" value="<?= e($size) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Применить</button>
        </div>
    </form>

    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">Ничего не найдено </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_sizes WHERE product_id = ? AND quantity > 0");
                $stmt->execute([$p['id']]);
                $inStock = $stmt->fetchColumn() > 0;
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="https://via.placeholder.com/300x200/e0e0e0/555?text=<?= urlencode(e($p['name'])) ?>" 
                             class="card-img-top" alt="<?= e($p['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= e($p['name']) ?></h5>
                            <p class="text-muted"><?= e($p['brand']) ?></p>
                            <p class="card-text flex-grow-1"><?= mb_substr(e($p['description']), 0, 60) ?>...</p>
                            <h4 class="text-primary"><?= formatPrice($p['price']) ?></h4>
                            <span class="badge bg-<?= $inStock ? 'success' : 'danger' ?> mb-2">
                                <?= $inStock ? 'В наличии' : 'Нет в наличии' ?>
                            </span>
                            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary">Подробнее</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($page > 1 || count($products) === $limit): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">⬅️ Назад</a>
                    </li>
                <?php endif; ?>
                <li class="page-item disabled">
                    <span class="page-link">Страница <?= $page ?></span>
                </li>
                <?php if (count($products) === $limit): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Вперёд ➡️</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('merged') === '1') {
        localStorage.removeItem('cart');
        history.replaceState(null, '', location.pathname);
    }
});
</script>
</body>
</html>