<?php
session_start();
// 1. Kiểm tra Session Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin-login.php");
    exit();
}
session_regenerate_id(true);

// 2. Kết nối CSDL để lấy dữ liệu thống kê
require_once '../config/database.php';

try {
    // Đếm tổng số phim
    $countMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();

    // Đếm tổng số tài khoản user
    $countUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Đếm tổng số suất chiếu
    $countShowtimes = $pdo->query("SELECT COUNT(*) FROM showtimes")->fetchColumn();
} catch (PDOException $e) {
    // Trường hợp bảng chưa có hoặc lỗi CSDL thì gán mặc định là 0
    $countMovies = $countUsers = $countShowtimes = 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Quản Lý Bán Vé Phim</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
        .sidebar { background: var(--primary-color, #1e1e2d); color: #fff; padding: 20px; }
        .sidebar a { display: block; color: var(--text-muted, #a1a5b7); padding: 12px 0; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { color: #fff; border-left: 3px solid var(--accent-color, #7367f0); padding-left: 8px; }
        .main-content { background: var(--bg-body, #f4f5f7); padding: 25px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: var(--shadow-card); margin-bottom: 20px; }
        
        /* CSS cho các thẻ thống kê */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border-left: 4px solid #7367f0; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #6e6b7b; text-transform: uppercase; }
        .stat-card .number { font-size: 28px; font-weight: bold; color: #5e5873; margin: 0; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <h2>ADMIN PANEL</h2>
            <nav style="margin-top: 20px;">
                <a href="index.php" class="active">Dashboard</a>
                <a href="movies_list.php">Quản lý Phim</a>
                <a href="../logout.php" style="color: var(--color-danger, #ea5455);">Đăng xuất</a>
            </nav>
        </aside>
        <main class="main-content">
            <div class="card">
                <h2>Tổng Quan Quản Trị</h2>
                <p style="margin-top: 5px; color: #6e6b7b;">Chào mừng Admin quay trở lại hệ thống!</p>
                
                <!-- Bảng Thống Kê Nhanh -->
                <div class="stats-grid">
                    <div class="stat-card" style="border-color: #7367f0;">
                        <h3>Tổng Số Phim</h3>
                        <p class="number"><?= $countMovies ?></p>
                    </div>
                    <div class="stat-card" style="border-color: #28c76f;">
                        <h3>Suất Chiếu</h3>
                        <p class="number"><?= $countShowtimes ?></p>
                    </div>
                    <div class="stat-card" style="border-color: #ff9f43;">
                        <h3>Khách Hàng</h3>
                        <p class="number"><?= $countUsers ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>