<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
requireRole('admin');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$userId = (int)($data['user_id'] ?? 0);

if (!$userId) {
    http_response_code(400);
    echo json_encode(['error' => 'Нет ID']);
    exit;
}

try {
    global $pdo;
    if ($action === 'set_role') {
        $role = in_array($data['role'], ['user','manager','admin']) ? $data['role'] : 'user';
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);
    } elseif ($action === 'block') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'blocked' WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif ($action === 'unblock') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->execute([$userId]);
    } 
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка']);
}