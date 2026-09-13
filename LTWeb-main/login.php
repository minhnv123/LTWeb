<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';
require_once 'captcha.php'; // Kết nối file Captcha

// Nếu đã đăng nhập thì chuyển hướng
if (isset($_SESSION['user'])) {
    $userRole = strtolower(trim($_SESSION['user']['role'] ?? ''));
    header('Location: ' . ($userRole === 'admin' ? 'admin/index.php' : 'index.php'));
    exit;
}

$errors = [];

// Khởi tạo Captcha ban đầu
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['captcha_answer'])) {
    generateCaptcha();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha  = trim($_POST['captcha'] ?? '');

    // 1. Kiểm tra Email & Password
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập Email hợp lệ.';
    }
    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu.';
    }

    // 2. Kiểm tra CAPTCHA
    if (!verifyCaptcha($captcha)) {
        $errors[] = 'Mã xác nhận (CAPTCHA) không đúng.';
    }

    // Đổi phép tính mới cho lần thử tiếp theo
    generateCaptcha();

    // 3. Xử lý đăng nhập khi không có lỗi
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $role = strtolower(trim($user['role'] ?? 'user'));
            $_SESSION['user'] = [
                'id'        => (int)$user['id'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $role
            ];

            // Đăng nhập thành công -> Xóa data captcha
            unset($_SESSION['captcha_num1'], $_SESSION['captcha_num2'], $_SESSION['captcha_answer']);

            header('Location: ' . ($role === 'admin' ? 'admin/index.php' : 'index.php'));
            exit;
        } else {
            $errors[] = 'Email hoặc mật khẩu không chính xác.';
        }
    }
}

include_once 'header.php';
?>

<div class="max-w-md mx-auto my-12 p-6 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
    <h2 class="text-2xl font-bold text-center text-white mb-6">Đăng Nhập</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3.5 rounded-xl mb-5 text-sm">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Mật khẩu</label>
            <input type="password" name="password" required 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <!-- CAPTCHA -->
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Xác minh bạn không phải Robot</label>
            <div class="flex items-center gap-3">
                <div class="h-11 px-4 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-center font-bold text-rose-500 tracking-wider text-base select-none shadow-inner min-w-[110px]">
                    <?php echo ($_SESSION['captcha_num1'] ?? 0) . ' + ' . ($_SESSION['captcha_num2'] ?? 0) . ' = ?'; ?>
                </div>
                <input type="number" name="captcha" placeholder="Kết quả?" required 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
            </div>
        </div>

        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all mt-2 cursor-pointer">
            Đăng Nhập
        </button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-6">
        Chưa có tài khoản? <a href="register.php" class="text-rose-500 hover:underline">Đăng ký ngay</a>
    </p>
</div>

<?php include_once 'footer.php'; ?>