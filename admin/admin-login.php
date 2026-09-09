<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        // Truy vấn lấy tài khoản có role admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Kiểm tra khớp mật khẩu
        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'] ?? $user['name'] ?? 'Admin';
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_logged_in'] = true;

            header("Location: index.php");
            exit();
        } else {
            $error = "Email hoặc mật khẩu không chính xác!";
        }
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị Viên</title>
    <style>
        body { background: #1e1e2d; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .login-card { background: #fff; padding: 30px; border-radius: 8px; width: 100%; max-width: 380px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .login-card h2 { margin-top: 0; text-align: center; color: #1e1e2d; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-admin { width: 100%; padding: 10px; background: #7367f0; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-admin:hover { background: #5e50ee; }
        .error-msg { color: #ea5455; margin-bottom: 15px; text-align: center; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>HỆ THỐNG ADMIN</h2>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="POST" autocomplete="off">
            <div class="form-group">
                <label>Email Admin:</label>
                <input type="email" name="email" required placeholder="Nhập email admin...">
            </div>
            <div class="form-group">
                <label>Mật Khẩu:</label>
                <input type="password" name="password" required placeholder="Nhập mật khẩu...">
            </div>
            <button type="submit" class="btn-admin">Đăng Nhập Quản Trị</button>
        </form>
    </div>
</body>
</html>