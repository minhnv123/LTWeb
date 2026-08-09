<?php
$host = '127.0.0.1';
$port = '3333';        // Cổng MySQL theo hình XAMPP
$dbname = 'cinema';      // Tên DB của bạn (hoặc 'LTweb')
$username = 'root';
$password = '';

try {
    // Đã thêm chính xác $port vào chuỗi kết nối
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>