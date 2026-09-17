<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
include_once 'header.php';
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) { 
    header("Location: movies_list.php"); 
    exit(); 
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $status = trim($_POST['status'] ?? 'now_showing'); // Lấy trạng thái từ form
    $description = trim($_POST['description'] ?? '');
    $posterName = $movie['poster'];

    if (empty($title) || empty($genre) || $duration <= 0) {
        $error = "Vui lòng nhập đầy đủ Tên phim, Thể loại và Thời lượng hợp lệ!";
    } else {
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $newFileName = time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = '../uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDir . $newFileName)) {
                    if (!empty($movie['poster']) && file_exists($uploadDir . $movie['poster'])) {
                        unlink($uploadDir . $movie['poster']);
                    }
                    $posterName = $newFileName;
                } else {
                    $error = "Lỗi khi tải poster mới lên máy chủ!";
                }
            } else {
                $error = "Chỉ chấp nhận file ảnh dạng .jpg, .jpeg, .png, .webp";
            }
        }

        if (empty($error)) {
            // Cập nhật cả cột status
            $updateStmt = $pdo->prepare("UPDATE movies SET title = ?, genre = ?, duration = ?, status = ?, description = ?, poster = ? WHERE id = ?");
            $updateStmt->execute([$title, $genre, $duration, $status, $description, $posterName, $id]);

            header("Location: movies_list.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Thông Tin Phim — Quản Trị</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4">

    <div class="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-rose-500"></i> Cập Nhật Phim #<?= htmlspecialchars($movie['id']) ?>
            </h2>
            <a href="movies_list.php" class="text-xs text-slate-400 hover:text-white transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Danh sách phim
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Tên Phim <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $movie['title']) ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Thể Loại <span class="text-rose-500">*</span></label>
                    <input type="text" name="genre" value="<?= htmlspecialchars($_POST['genre'] ?? $movie['genre']) ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Thời Lượng (Phút) <span class="text-rose-500">*</span></label>
                    <input type="number" name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? $movie['duration']) ?>" required min="1" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
                </div>
            </div>

            <!-- Cụm chọn Trạng Thái Phim -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Trạng Thái Chiếu <span class="text-rose-500">*</span></label>
                <?php $currentStatus = $_POST['status'] ?? ($movie['status'] ?? 'now_showing'); ?>
                <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
                    <option value="now_showing" <?= $currentStatus === 'now_showing' ? 'selected' : '' ?>>🎬 Đang Chiếu</option>
                    <option value="coming_soon" <?= $currentStatus === 'coming_soon' ? 'selected' : '' ?>>⏳ Sắp Chiếu</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Mô Tả Phim</label>
                <textarea name="description" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm"><?= htmlspecialchars($_POST['description'] ?? $movie['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Poster Hiện Tại</label>
                <?php if (!empty($movie['poster'])): ?>
                    <div class="flex items-center gap-4 mb-3 p-2 bg-slate-950 border border-slate-800 rounded-xl w-fit">
                        <img src="../uploads/<?= htmlspecialchars($movie['poster']) ?>" alt="Poster" class="w-16 h-20 object-cover rounded-lg">
                        <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($movie['poster']) ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-500 mb-3 italic">Chưa có ảnh poster</p>
                <?php endif; ?>

                <label class="block text-sm font-medium text-slate-300 mb-1">Thay Đổi Poster Mới (Nếu muốn)</label>
                <input type="file" name="poster" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-600/20 file:text-rose-400 hover:file:bg-rose-600/30 file:cursor-pointer">
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all text-sm">
                    <i class="fa-solid fa-rotate mr-1"></i> Cập Nhật Phim
                </button>
                <a href="movies_list.php" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition-all text-sm text-center">
                    Hủy
                </a>
            </div>
        </form>
    </div>

</body>
</html>