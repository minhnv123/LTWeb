<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra khóa bảo vệ Session Admin (Đã đồng bộ chuẩn với login.php)
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Đổi đường dẫn file kết nối DB chuẩn trong project
require_once '../config/db.php';
include_once 'header.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? ''); // Bổ sung trường thể loại
    $duration = intval($_POST['duration'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($genre) || $duration <= 0) {
        $error = "Vui lòng nhập đầy đủ Tên phim, Thể loại và Thời lượng hợp lệ!";
    } else {
        // Xử lý Upload Poster
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $fileName = $_FILES['poster']['name'];
            $fileTmp = $_FILES['poster']['tmp_name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExts)) {
                $newFileName = time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = '../uploads/';
                
                if (!is_dir($uploadDir)) { 
                    mkdir($uploadDir, 0777, true); 
                }

                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    // Thêm trường 'genre' vào truy vấn SQL
                    $stmt = $pdo->prepare("INSERT INTO movies (title, genre, duration, description, poster) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $genre, $duration, $description, $newFileName]);
                    
                    header("Location: movies_list.php");
                    exit();
                } else { 
                    $error = "Lỗi khi tải file lên máy chủ!"; 
                }
            } else { 
                $error = "Chỉ chấp nhận file ảnh dạng .jpg, .jpeg, .png, .webp"; 
            }
        } else { 
            $error = "Vui lòng chọn ảnh poster cho phim!"; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Phim Mới — Quản Trị</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4">

    <div class="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clapperboard text-rose-500"></i> Thêm Phim Mới
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
                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required placeholder="Ví dụ: Avatar 3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Thể Loại <span class="text-rose-500">*</span></label>
                <input type="text" name="genre" value="<?= htmlspecialchars($_POST['genre'] ?? '') ?>" required placeholder="Ví dụ: Hành động, Viễn tưởng" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Thời Lượng (Phút) <span class="text-rose-500">*</span></label>
                <input type="number" name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>" required min="1" placeholder="Ví dụ: 120" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Mô Tả Phim</label>
                <textarea name="description" rows="4" placeholder="Tóm tắt nội dung phim..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-rose-500 text-sm"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Poster Phim (.jpg / .png / .webp) <span class="text-rose-500">*</span></label>
                <input type="file" name="poster" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-600/20 file:text-rose-400 hover:file:bg-rose-600/30 file:cursor-pointer">
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all text-sm">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu Phim
                </button>
                <a href="movies_list.php" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition-all text-sm text-center">
                    Hủy
                </a>
            </div>
        </form>
    </div>

</body>
</html>