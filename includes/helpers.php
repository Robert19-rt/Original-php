<?php
function formatPrice(float $price): string {
    return number_format($price, 0, ',', ' ') . ' ₽';
}

function getAvailableSizes(int $productId): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT size, quantity 
        FROM product_sizes 
        WHERE product_id = ? AND quantity > 0 
        ORDER BY size
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}