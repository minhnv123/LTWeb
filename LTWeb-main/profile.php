<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$message = '';
$error = '';

// Xử lý cập nhật thông tin & đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    // 1. Cập nhật Họ và tên
    if (!empty($fullName)) {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->execute([$fullName, $userId]);
        $_SESSION['user']['full_name'] = $fullName;
        $message = "Cập nhật thông tin thành công!";
    }

    // 2. Đổi mật khẩu
    if (!empty($oldPassword) || !empty($newPassword)) {
        if (empty($oldPassword) || empty($newPassword)) {
            $error = "Vui lòng nhập đầy đủ cả mật khẩu hiện tại và mật khẩu mới!";
        } elseif (strlen($newPassword) < 6) {
            $error = "Mật khẩu mới phải có ít nhất 6 ký tự!";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user && password_verify($oldPassword, $user['password'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtUpdate->execute([$newHash, $userId]);
                $message = "Đổi mật khẩu thành công!";
            } else {
                $error = "Mật khẩu hiện tại không chính xác!";
            }
        }
    }
}

// Lấy thông tin mới nhất của user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

include_once 'header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl shadow-black/30">
        
        <!-- Header Section & Nút chuyển hướng -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 border-b border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-rose-600/20 text-rose-500 border border-rose-500/30 rounded-2xl flex items-center justify-center text-3xl font-bold">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Thông Tin Tài Khoản</h1>
                    <p class="text-sm text-slate-400">Quản lý thông tin cá nhân và bảo mật tài khoản</p>
                </div>
            </div>

            <!-- Nút điều hướng nhanh đến Lịch Sử Vé -->
            <a href="history.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-xl border border-slate-700 transition-all">
                <i class="fa-solid fa-ticket text-rose-400"></i>
                <span>Xem Vé Đã Đặt</span>
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                <i class="fa-solid fa-circle-check mr-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Địa chỉ Email (Không đổi)</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled 
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Họ và Tên</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-rose-500 transition-colors">
                </div>
            </div>

            <hr class="border-slate-800 my-6">

            <h2 class="text-base font-bold text-slate-200 mb-4"><i class="fa-solid fa-key text-amber-400 mr-2"></i>Đổi Mật Khẩu (Để trống nếu không đổi)</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Mật khẩu hiện tại</label>
                    <input type="password" name="old_password" placeholder="••••••••"
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-rose-500 transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Mật khẩu mới</label>
                    <input type="password" name="new_password" placeholder="••••••••"
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-rose-500 transition-colors">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition-all shadow-lg shadow-rose-600/20 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?>