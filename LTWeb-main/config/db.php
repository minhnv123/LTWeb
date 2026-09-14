<?php
$host = '127.0.0.1';
$dbname = 'cinema'; // Nếu tên CSDL của bạn khác, hãy đổi tên ở đây
$username = 'root';
$password = '';

// Danh sách các port MySQL phổ biến (bổ sung 3306 và 3308)
$ports = ['3308', '3306', '3307', '3333'];
$pdo = null;
$lastError = null;

foreach ($ports as $port) {
    try {
        // Kết nối kiểm tra port
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Tự động tạo CSDL nếu chưa tồn tại
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        break; // Kết nối thành công thì thoát vòng lặp
    } catch (PDOException $e) {
        $pdo = null;
        $lastError = $e;
    }
}

if (!$pdo) {
    die("Lỗi kết nối CSDL: " . ($lastError ? $lastError->getMessage() : 'Không thể kết nối đến MySQL'));
}
?>