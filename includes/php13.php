<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('manager'); 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка — Товары</title>
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
    <h1>⚙️ Админка — Управление товарами</h1>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="products.php">Товары</a>
        </li>
        <?php if (getUser()['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="users.php">Пользователи</a>
            </li>
        <?php endif; ?>
    </ul>

    <a href="#" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
        ➕ Добавить товар
    </a>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Категория</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
            while ($p = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= number_format($p['price'], 0, ',', ' ') ?> ₽</td>
                    <td><?= e($p['category']) ?></td>
                    <td>
                        <span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= $p['status'] === 'active' ? 'активен' : 'скрыт' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">✏️ Редактировать</button>
                        <button class="btn btn-sm btn-outline-danger">🗑️ Удалить</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить новый товар</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Категория *</label>
                            <select name="category" class="form-select" required>
                                <option value="men">Мужская</option>
                                <option value="women">Женская</option>
                                <option value="kids">Детская</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Бренд</label>
                            <input type="text" name="brand" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Цена, ₽ *</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Цвет</label>
                            <input type="text" name="color" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Материал</label>
                            <input type="text" name="material" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Статус</label>
                            <select name="status" class="form-select">
                                <option value="active">Активен</option>
                                <option value="hidden">Скрыт</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Остатки по размерам (через запятую: 41-5, 42-3)</label>
                        <input type="text" name="sizes" class="form-control" placeholder="41-5, 42-3, 43-0">
                        <small class="form-text text-muted">Формат: размер-количество</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" id="saveProductBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('saveProductBtn').onclick = async () => {
    const form = document.getElementById('addProductForm');
    const data = new FormData(form);
    const sizes = data.get('sizes') || '';
    const sizesArray = [];
    if (sizes) {
        sizes.split(',').forEach(part => {
            const [size, qty] = part.trim().split('-').map(x => parseFloat(x));
            if (!isNaN(size) && !isNaN(qty)) sizesArray.push({size, qty});
        });
    }
    data.delete('sizes');
    data.append('sizes', JSON.stringify(sizesArray));

    try {
        const res = await fetch('/api/admin/products.php', {
            method: 'POST',
            body: JSON.stringify(Object.fromEntries(data)),
            headers: { 'Content-Type': 'application/json' }
        });
        const json = await res.json();
        if (json.success) {
            location.reload();
        } else {
            alert('Ошибка: ' + json.error);
        }
    } catch (e) {
        alert('Ошибка сети');
    }
};
</script>
</body>
</html>