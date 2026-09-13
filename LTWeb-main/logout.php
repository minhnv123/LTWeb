<?php
// 1. Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Xóa toàn bộ biến lưu trong $_SESSION
$_SESSION = array();

// 3. Xóa Cookie lưu Session ID trên trình duyệt (Giúp hủy hẳn Session ở client-side)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Hủy Session trên Server
session_destroy();

// 5. Chuyển hướng về trang chủ
header('Location: index.php');
exit;