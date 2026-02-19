<?php
session_start();
require_once 'db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getUser(): ?array {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT id, email, name, phone, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function requireRole(string $minRole): void {
    $user = getUser();
    $roles = ['user' => 1, 'manager' => 2, 'admin' => 3];
    if (!$user || !isset($roles[$user['role']]) || $roles[$user['role']] < $roles[$minRole]) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function getCurrentCartId(): ?int {
    global $pdo;
    $user = getUser();
    if ($user) {
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        return $stmt->fetchColumn() ?: null;
    } else {
        $sid = session_id();
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE session_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$sid]);
        return $stmt->fetchColumn() ?: null;
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}