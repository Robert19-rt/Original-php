<?php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        
        die('Ошибка: таблицы не созданы. Импортируйте SQL-скрипт вручную.');
    } else {
        $stmt = $pdo->query("SELECT id FROM users WHERE email = 'admin@example.com'");
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                'admin@example.com',
                password_hash('admin123', PASSWORD_BCRYPT),
                'Администратор',
                'admin'
            ]);
        }
    }
} catch (PDOException $e) {
    die("Ошибка подключения к MySQL: " . htmlspecialchars($e->getMessage()));
}