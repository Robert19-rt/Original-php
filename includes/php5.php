<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db.php';
session_start();

$action = $_GET['action'] ?? '';
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$type = $data['type'] ?? '';

try {
    if ($type === 'register') {
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $name = trim($data['name'] ?? '');
        $pass = $data['password'] ?? '';
        $pass2 = $data['password2'] ?? '';

        if (!$email || !$name || !$pass || $pass !== $pass2 || strlen($pass) < 6) {
            throw new Exception('Заполните все поля. Пароль не менее 6 символов.');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) throw new Exception('Email уже зарегистрирован');

        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$email, $hash, $name]);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();
        $_SESSION['user_id'] = $userId;

        if (isset($data['cart']) && !empty($data['cart']['items'])) {
            $_POST = ['items' => $data['cart']['items']];
            require 'cart.php';
        }

        echo json_encode(['success' => true]);

    } elseif ($type === 'login') {
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $pass = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pass, $user['password'])) {
            throw new Exception('Неверный email или пароль');
        }

        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);

    } else {
        throw new Exception('Неизвестный тип запроса');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}