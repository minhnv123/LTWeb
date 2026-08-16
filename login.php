<?php
ob_start();
require_once 'config/dp.php';
include_once 'header.php';

// Chuyển hướng nếu đã đăng nhập
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate cơ bản
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập Email hợp lệ.';
    }

    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Lưu thông tin vào Session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email']
            ];

            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Email hoặc mật khẩu không chính xác.';
        }
    }
}
?>

<div class="max-w-md mx-auto my-12 p-6 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
    <h2 class="text-2xl font-bold text-center text-white mb-6">Đăng Nhập</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-xl mb-4 text-sm">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Mật khẩu</label>
            <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all mt-2">
            Đăng Nhập
        </button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-6">
        Chưa có tài khoản? <a href="register.php" class="text-rose-500 hover:underline">Đăng ký ngay</a>
    </p>
</div>

<?php include_once 'footer.php'; ?>