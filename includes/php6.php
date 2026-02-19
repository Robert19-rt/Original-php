<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный формат']);
    exit;
}

$user = getUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

try {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user['id']]);
        $cartId = $pdo->lastInsertId();
    }

    foreach ($input['items'] as $item) {
        if (!isset($item['product_id'], $item['size'], $item['quantity'])) continue;

        $stmt = $pdo->prepare("SELECT id, quantity FROM product_sizes WHERE product_id = ? AND size = ?");
        $stmt->execute([$item['product_id'], $item['size']]);
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$size || $size['quantity'] <= 0) continue;

        $newQty = min($item['quantity'], $size['quantity']);
        $sizeId = $size['id'];

        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND size_id = ?");
        $stmt->execute([$cartId, $item['product_id'], $sizeId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = min($existing['quantity'] + $newQty, $size['quantity']);
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cartId, $item['product_id'], $sizeId, $newQty]);
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера']);
}