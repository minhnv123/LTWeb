<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
include_once 'header.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cinemas WHERE id = ?");
$stmt->execute([$id]);
$cinema = $stmt->fetch();

if (!$cinema) {
    header("Location: cinemas_list.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($address)) {
        $error = "Vui lòng nhập đầy đủ Tên rạp và Địa chỉ!";
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE cinemas SET name = ?, address = ? WHERE id = ?");
        if ($stmtUpdate->execute([$name, $address, $id])) {
            header("Location: cinemas_list.php");
            exit();
        } else {
            $error = "Có lỗi xảy ra khi cập nhật thông tin rạp.";
        }
    }
}

include_once 'header.php';
?>

<div class="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl my-6">
    <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-rose-500"></i> Cập Nhật Rạp Chiếu
        </h2>
        <a href="cinemas_list.php" class="text-xs text-slate-400 hover:text-white transition-colors flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Danh sách rạp
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3.5 rounded-xl mb-6 text-sm flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Tên Rạp <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $cinema['name']) ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Địa Chỉ <span class="text-rose-500">*</span></label>
            <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? $cinema['address']) ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i> Cập Nhật
            </button>
            <a href="cinemas_list.php" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition-all text-sm text-center">
                Hủy
            </a>
        </div>
    </form>
</div>

<?php include_once 'footer.php'; ?>
