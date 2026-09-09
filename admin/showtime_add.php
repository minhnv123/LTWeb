<?php
require_php_file_if_exists: require_once '../config/database.php';

// Lấy danh sách phim & rạp để đổ vào dropdown
$movies = $pdo->query("SELECT id, title FROM movies ORDER BY title ASC")->fetchAll();
$cinemas = $pdo->query("SELECT id, name FROM cinemas ORDER BY name ASC")->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id  = $_POST['movie_id'] ?? '';
    $cinema_id = $_POST['cinema_id'] ?? '';
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';
    $price     = $_POST['price'] ?? 75000;

    if (!empty($movie_id) && !empty($cinema_id) && !empty($show_date) && !empty($show_time)) {
        $stmt = $pdo->prepare("INSERT INTO showtimes (movie_id, cinema_id, show_date, show_time, price) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$movie_id, $cinema_id, $show_date, $show_time, $price])) {
            $message = "Thêm suất chiếu thành công!";
        } else {
            $error = "Đã xảy ra lỗi khi thêm suất chiếu.";
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
    <title>Thêm Suất Chiếu Mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 p-6 min-h-screen">
    <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-rose-500"><i class="fa-solid fa-calendar-plus mr-2"></i>Thêm Suất Chiếu Mới</h2>
            <a href="showtimes_list.php" class="text-sm text-slate-400 hover:text-white"><i class="fa-solid fa-arrow-left mr-1"></i>Quay lại</a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 text-sm rounded-lg"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-500/20 border border-rose-500/50 text-rose-400 text-sm rounded-lg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Chọn Phim</label>
                <select name="movie_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                    <option value="">-- Chọn Phim --</option>
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Chọn Rạp / Phòng Chiếu</label>
                <select name="cinema_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                    <option value="">-- Chọn Rạp --</option>
                    <?php foreach ($cinemas as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Ngày Chiếu</label>
                    <input type="date" name="show_date" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Giờ Chiếu</label>
                    <input type="time" name="show_time" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Giá Vé (VNĐ)</label>
                <input type="number" name="price" value="75000" step="1000" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-lg transition-all shadow-lg shadow-rose-600/30 mt-2">
                Lưu Suất Chiếu
            </button>
        </form>
    </div>
</body>
</html>