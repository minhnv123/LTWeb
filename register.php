<?php
require_once 'config/dp.php';
include_once 'header.php';

// Chuyển hướng nếu đã đăng nhập
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate Dữ liệu
    if (empty($full_name)) {
        $errors[] = 'Vui lòng nhập họ và tên.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ hoặc bị trống.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không trùng khớp.';
    }

    // Kiểm tra Email trùng trong DB
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email này đã được sử dụng.';
        }
    }

    // Thêm người dùng mới
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$full_name, $email, $hashed_password])) {
            $success = 'Đăng ký thành công! Đang chuyển hướng sang trang đăng nhập...';
            header("refresh:2;url=login.php");
        } else {
            $errors[] = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}
?>

<div class="max-w-md mx-auto my-12 p-6 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
    <h2 class="text-2xl font-bold text-center text-white mb-6">Đăng Ký Tài Khoản</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-xl mb-4 text-sm">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-3 rounded-xl mb-4 text-sm">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Họ và Tên</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Mật khẩu</label>
            <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Xác nhận Mật khẩu</label>
            <input type="password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500">
        </div>

        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all mt-2">
            Tạo Tài Khoản
        </button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-6">
        Đã có tài khoản? <a href="login.php" class="text-rose-500 hover:underline">Đăng nhập ngay</a>
    </p>
</div>

<?php include_once 'footer.php'; ?>