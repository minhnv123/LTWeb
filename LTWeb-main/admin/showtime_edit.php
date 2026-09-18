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
$id = (int)($_GET['id'] ?? 0);

// Lấy thông tin suất chiếu hiện tại
$stmt = $pdo->prepare("SELECT * FROM showtimes WHERE id = ?");
$stmt->execute([$id]);
$showtime = $stmt->fetch();

if (!$showtime) {
    header("Location: showtimes_list.php");
    exit();
}

$movies = $pdo->query("SELECT id, title FROM movies ORDER BY title ASC")->fetchAll();
$cinemas = $pdo->query("SELECT id, name FROM cinemas ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id  = $_POST['movie_id'] ?? '';
    $cinema_id = $_POST['cinema_id'] ?? '';
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';
    $price     = $_POST['price'] ?? 75000;

    if (!empty($movie_id) && !empty($cinema_id) && !empty($show_date) && !empty($show_time)) {
        $updateStmt = $pdo->prepare("UPDATE showtimes SET movie_id = ?, cinema_id = ?, show_date = ?, show_time = ?, price = ? WHERE id = ?");
        if ($updateStmt->execute([$movie_id, $cinema_id, $show_date, $show_time, $price, $id])) {
            header("Location: showtimes_list.php");
            exit();
        } else {
            $error = "Đã xảy ra lỗi khi cập nhật suất chiếu.";
        }
    } else {
        $error = "Vui lòng điền đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập Nhật Suất Chiếu #<?= $showtime['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 p-6 min-h-screen">
    <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-amber-500"><i class="fa-solid fa-pen-to-square mr-2"></i>Sửa Suất Chiếu #<?= $showtime['id'] ?></h2>
            <a href="showtimes_list.php" class="text-sm text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left mr-1"></i>Quay lại</a>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-500/20 border border-rose-500/50 text-rose-400 text-sm rounded-lg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Chọn Phim</label>
                <select name="movie_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $m['id'] == $showtime['movie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Chọn Rạp / Phòng Chiếu</label>
                <select name="cinema_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                    <?php foreach ($cinemas as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $showtime['cinema_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Ngày Chiếu</label>
                    <input type="date" name="show_date" value="<?= $showtime['show_date'] ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Giờ Chiếu</label>
                    <input type="time" name="show_time" value="<?= $showtime['show_time'] ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Giá Vé (VNĐ)</label>
                <input type="number" name="price" value="<?= $showtime['price'] ?>" step="1000" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-lg transition-all shadow-lg shadow-amber-600/30 mt-2">
                Cập Nhật Suất Chiếu
            </button>
        </form>
    </div>
</body>
</html>