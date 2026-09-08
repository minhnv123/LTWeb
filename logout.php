<?php
session_start();

// 1. Xóa sạch tất cả các session (cả 'user' lẫn 'is_admin')
$_SESSION = array();

// 2. Hủy hoàn toàn phiên làm việc
session_destroy();

// 3. Chuyển hướng người dùng về Trang chủ
header('Location: index.php');
exit;
?>