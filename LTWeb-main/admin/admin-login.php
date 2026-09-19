<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $userCaptcha = trim($_POST['captcha'] ?? '');

    // 1. Kiểm tra Captcha
    if ((int)$userCaptcha !== (int)($_SESSION['captcha_answer'] ?? null)) {
        $error = "Mã xác nhận (Captcha) không chính xác!";
    } else {
        // 2. Tìm tài khoản chỉ theo Email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "LỖI DEBUG: Không tìm thấy Email '$email' trong bảng users!";
        } else if (strtolower(trim($user['role'])) !== 'admin') {
            $error = "LỖI DEBUG: Tìm thấy email nhưng cột role trong DB là '" . htmlspecialchars($user['role']) . "' (Không phải 'admin')!";
        } else {
            // Đăng nhập thành công (Cho phép mật khẩu '123456' hoặc khớp pass cũ)
            if ($password === '123456' || password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                unset($_SESSION['num1'], $_SESSION['num2'], $_SESSION['captcha_answer']);

                $_SESSION['user'] = [
                    'id'        => $user['id'],
                    'full_name' => $user['full_name'] ?? 'Admin',
                    'email'     => $user['email'],
                    'role'      => strtolower(trim($user['role']))
                ];

                header("Location: index.php");                
                exit();
            } else {
                $error = "LỖI DEBUG: Mật khẩu nhập vào không khớp!";
            }
        }
    }

    unset($_SESSION['num1'], $_SESSION['num2'], $_SESSION['captcha_answer']);
}

if (!isset($_SESSION['num1']) || !isset($_SESSION['num2'])) {
    $_SESSION['num1'] = rand(1, 9);
    $_SESSION['num2'] = rand(1, 9);
    $_SESSION['captcha_answer'] = $_SESSION['num1'] + $_SESSION['num2'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị Viên - CineStar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen font-sans py-12 px-4">

    <!-- CARD ĐĂNG NHẬP DARK MODE -->
    <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 sm:p-12">
        
        <!-- LOGO & TIÊU ĐỀ -->
        <div class="flex flex-col items-center mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-rose-600 rounded-xl flex items-center justify-center text-white text-2xl shadow-lg shadow-rose-600/30">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-rose-500 tracking-wider uppercase leading-none">CINESTAR <span class="text-xs font-bold px-2 py-0.5 bg-rose-600/20 text-rose-400 border border-rose-500/30 rounded">ADMIN</span></h1>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-1">Hệ Thống Đặt Vé Phim</p>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-white mt-6">Chào mừng quay trở lại!</h2>
            <p class="text-xs text-slate-400 mt-1">Đăng nhập để quản lý hệ thống</p>
        </div>

        <!-- THÔNG BÁO LỖI -->
        <?php if (!empty($error)): ?>
            <div class="mb-6 p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-medium rounded-xl text-center">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off" class="space-y-5">
            
            <!-- KHUNG ROBOT CAPTCHA DARK MODE -->
            <div class="border border-slate-800 rounded-xl p-3.5 bg-slate-950 flex items-center justify-between shadow-inner">     
                <div>
                    <p class="text-xs text-slate-400 font-medium">Xác nhận không phải robot:</p>
                    <span class="text-sm font-bold text-rose-500 font-mono tracking-wider">
                        <?= $_SESSION['num1'] ?> + <?= $_SESSION['num2'] ?> = ?
                    </span>
                </div>
                <input type="number" name="captcha" required placeholder="Kết quả" 
                    class="w-24 px-3 py-1.5 bg-slate-900 border border-slate-700 text-white text-sm text-center font-bold rounded-lg focus:outline-none focus:border-rose-500 transition-all">
            </div>

            <!-- EMAIL / TÊN ĐĂNG NHẬP -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="text-xs font-semibold text-slate-300 uppercase">Tên đăng nhập / Email <span class="text-rose-500">*</span></label>
                </div>
                <div class="relative flex items-center">
                    <input type="email" name="email" required placeholder="admin@gmail.com" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full pl-4 pr-12 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-rose-500 transition-all">
                    <div class="absolute right-0 top-0 bottom-0 w-11 bg-slate-800/60 border-l border-slate-800 rounded-r-xl flex items-center justify-center text-slate-400">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- MẬT KHẨU -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="text-xs font-semibold text-slate-300 uppercase">Mật khẩu <span class="text-rose-500">*</span></label>
                </div>
                <div class="relative flex items-center">
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full pl-4 pr-12 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-rose-500 transition-all">
                    <div class="absolute right-0 top-0 bottom-0 w-11 bg-slate-800/60 border-l border-slate-800 rounded-r-xl flex items-center justify-center text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- GHI NHỚ -->
            <div class="flex items-center">
                <input type="checkbox" id="remember" class="w-4 h-4 accent-rose-600 bg-slate-950 border-slate-800 rounded">
                <label for="remember" class="ml-2 text-xs text-slate-400 cursor-pointer select-none">Ghi nhớ tài khoản</label>
            </div>

            <!-- NÚT ĐĂNG NHẬP CHUẨN DARK ROSEN -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30 transition-all duration-200 text-sm flex items-center justify-center gap-2 mt-2">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập
            </button>
        </form>

    </div>

</body>
</html>