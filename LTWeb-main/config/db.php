<?php
$host = '127.0.0.1';
$dbname = 'cinema';
$username = 'root';
$password = '';

// Tự động thử các cổng MySQL phổ biến của XAMPP (3306, 3333, 3307)
$ports = ['3306', '3333', '3307'];
$pdo = null;
$lastError = null;

foreach ($ports as $port) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break;
    } catch (PDOException $e) {
        $lastError = $e;
    }
}

if (!$pdo) {
    die("Lỗi kết nối CSDL: " . ($lastError ? $lastError->getMessage() : 'Không thể kết nối đến MySQL'));
}
?>