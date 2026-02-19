<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db.php';

$category = $_GET['category'] ?? null;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$size = isset($_GET['size']) ? (float)$_GET['size'] : null;
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
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}
if ($size !== null) {
    $sql .= " AND EXISTS (SELECT 1 FROM product_sizes ps WHERE ps.product_id = p.id AND ps.size = ? AND ps.quantity > 0)";
    $params[] = $size;
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as &$p) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_sizes WHERE product_id = ? AND quantity > 0");
    $stmt->execute([$p['id']]);
    $p['in_stock'] = $stmt->fetchColumn() > 0;
}
unset($p);

echo json_encode([
    'products' => $products,
    'page' => $page,
    'has_more' => count($products) === $limit
]);