<?php
session_start();
require_once 'config/database.php'; // Sử dụng lại kết nối PDO $pdo đã cấu hình thành công

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Query kiểm tra tài khoản
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Kiểm tra mật khẩu và ĐẶC BIỆT phải có quyền role = 'admin'
        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['role']) && $user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['is_admin'] = true;

                // Đăng nhập đúng Admin -> Cho vào Dashboard Admin
                header("Location: admin/index.php");
                exit();
            } else {
                $error = "Tài khoản của bạn không có quyền truy cập Admin!";
            }
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
    <title>Đăng Nhập Quản Trị Viên</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #1e1e2d; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .login-card { background: #fff; padding: 30px; border-radius: 8px; width: 100%; max-width: 380px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .login-card h2 { margin-top: 0; text-align: center; color: #1e1e2d; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-admin { width: 100%; padding: 10px; background: #7367f0; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .error-msg { color: #ea5455; margin-bottom: 15px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>HỆ THỐNG ADMIN</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Email Admin:</label>
                <input type="email" name="email" required placeholder="admin@gmail.com">
            </div>
            <div class="form-group">
                <label>Mật Khẩu:</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-admin">Đăng Nhập Quản Trị</button>
        </form>
    </div>
</body>
</html>