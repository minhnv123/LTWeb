<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 2. NẾU LÀ TÀI KHOẢN ADMIN -> ĐẨY THẲNG SANG TRANG ADMIN RIÊNG
if (isset($_SESSION['user']['role']) && strtolower(trim($_SESSION['user']['role'])) === 'admin') {
    header("Location: admin/index.php");
    exit();
}

require_once 'config/db.php';

$userId = $_SESSION['user']['id'];
$message = '';
$error = '';

// Xử lý cập nhật thông tin / mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($fullName)) {
        $error = "Họ và tên không được để trống!";
    } else {
        // Cập nhật Họ và Tên
        $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->execute([$fullName, $userId]);
        $_SESSION['user']['full_name'] = $fullName;

        // Xử lý đổi mật khẩu nếu người dùng có nhập
        if (!empty($newPassword)) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
                $error = "Mật khẩu hiện tại không chính xác!";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $updatePassStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePassStmt->execute([$hashedPassword, $userId]);
                $message = "Cập nhật thông tin và mật khẩu thành công!";
            }
        } else {
            $message = "Cập nhật thông tin thành công!";
        }
    }
}

// Lấy thông tin user hiện tại
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 md:p-10 shadow-2xl backdrop-blur-xl">
        
        <!-- Title & Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center font-black text-white text-2xl shadow-lg shadow-rose-600/20">
                    <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white">Thông Tin Tài Khoản</h1>
                    <p class="text-xs text-slate-400">Quản lý thông tin cá nhân và bảo mật tài khoản</p>
                </div>
            </div>
            <a href="history.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl transition-all border border-slate-700">
                Xem Vé Đã Đặt
            </a>
        </div>

        <!-- Thông báo -->
        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm rounded-xl">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm rounded-xl">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form thông tin -->
        <form action="" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">ĐỊA CHỈ EMAIL (KHÔNG ĐỔI)</label>
                    <input type="email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" disabled 
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-400 text-sm focus:outline-none cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">HỌ VÀ TÊN</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" 
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-rose-500 transition-all">
                </div>
            </div>

            <hr class="border-slate-800 my-6">

            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Đổi Mật Khẩu (Để trống nếu không đổi)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">MẬT KHẨU HIỆN TẠI</label>
                    <input type="password" name="current_password" placeholder="••••••••" 
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-rose-500 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">MẬT KHẨU MỚI</label>
                    <input type="password" name="new_password" placeholder="••••••••" 
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-rose-500 transition-all">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-8 py-3.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Lưu Thay Đổi
                </button>
            </div>
        </form>

    </div>
</div>

<?php require_once 'footer.php'; ?>