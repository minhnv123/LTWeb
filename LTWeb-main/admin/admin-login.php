<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

$error = '';

// 1. Sinh phép tính ngẫu nhiên nếu chưa có
if (!isset($_SESSION['num1']) || !isset($_SESSION['num2'])) {
    $_SESSION['num1'] = rand(1, 9);
    $_SESSION['num2'] = rand(1, 9);
    $_SESSION['captcha_answer'] = $_SESSION['num1'] + $_SESSION['num2'];
}

// 2. Xử lý khi Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $userCaptcha = trim($_POST['captcha'] ?? '');

    // Kiểm tra Captcha
    if ((int)$userCaptcha !== (int)$_SESSION['captcha_answer']) {
        $error = "Mã xác nhận (Captcha) không chính xác!";
        
        // Làm mới phép tính khi nhập sai
        $_SESSION['num1'] = rand(1, 9);
        $_SESSION['num2'] = rand(1, 9);
        $_SESSION['captcha_answer'] = $_SESSION['num1'] + $_SESSION['num2'];
    } else {
        // Đúng Captcha -> Xóa biến captcha
        unset($_SESSION['num1'], $_SESSION['num2'], $_SESSION['captcha_answer']);

        if (!empty($email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND LOWER(TRIM(role)) = 'admin' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'        => $user['id'],
                    'full_name' => $user['full_name'] ?? $user['name'] ?? 'Admin',
                    'email'     => $user['email'],
                    'role'      => strtolower(trim($user['role']))
                ];

                header("Location: index.php");
                exit();
            } else {
                $error = "Email hoặc mật khẩu không chính xác!";
            }
        } else {
            $error = "Vui lòng nhập đầy đủ thông tin!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị Viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen font-sans">

    <div class="w-full max-w-md p-8 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-rose-500 tracking-wider uppercase flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-shield"></i> HỆ THỐNG ADMIN
            </h1>
            <p class="text-xs text-slate-400 mt-1">Đăng nhập để quản lý hệ thống</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-medium rounded-lg text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Email Admin:</label>
                <input type="email" name="email" required placeholder="admin@gmail.com" 
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Mật Khẩu:</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-all">
            </div>

            <!-- PHẦN XÁC MINH CAPTCHA DẠNG TEXT ĐƯỢC DESIGN ĐẸP MAT -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">XÁC MINH ROBOT (CAPTCHA)</label>
                <div class="flex gap-3 items-center">
                    <!-- KHUNG PHÉP TÍNH -->
                    <div class="px-4 py-2.5 bg-slate-950 border border-rose-500/40 text-rose-500 font-black text-base rounded-xl select-none tracking-wider whitespace-nowrap shadow-inner">
                        <?= $_SESSION['num1'] ?> + <?= $_SESSION['num2'] ?> = ?
                    </div>

                    <!-- Ô NHẬP KẾT QUẢ -->
                    <input type="number" name="captcha" required placeholder="Kết quả?" 
                           class="flex-1 px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-all">
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30 transition-all duration-200 flex items-center justify-center gap-2 mt-2">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập Quản Trị
            </button>
        </form>
    </div>

</body>
</html>