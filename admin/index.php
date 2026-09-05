<?php
session_start();
// Kiểm tra Session Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}
session_regenerate_id(true);
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
        .btn-primary { background: var(--accent-color, #7367f0); color: #fff; padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; }
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
                <p style="margin-top: 10px;">Chào mừng Admin quay trở lại hệ thống!</p>
            </div>
        </main>
    </div>
</body>
</html>