<?php
// 1. Nhúng Header Admin (Đã bao gồm Session Check, CSDL & Sidebar)
include_once 'header.php';

$id = intval($_GET['id'] ?? 0);

// Lấy thông tin phim bằng PDO Prepared Statement
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
    $duration = intval($_POST['duration'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $posterName = $movie['poster']; // Giữ poster cũ mặc định

    // 2. Nếu chọn poster mới thì upload và xóa poster cũ
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowedExts)) {
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = '../uploads/';

            if (!is_dir($uploadDir)) { 
                mkdir($uploadDir, 0777, true); 
            }

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDir . $newFileName)) {
                
                // Xóa ảnh cũ khỏi thư mục uploads nếu file tồn tại
                if (!empty($movie['poster']) && file_exists($uploadDir . $movie['poster'])) {
                    unlink($uploadDir . $movie['poster']);
                }
                
                $posterName = $newFileName;
            } else {
                $error = "Lỗi khi lưu ảnh poster mới!";
            }
        } else {
            $error = "Chỉ chấp nhận file ảnh định dạng .jpg, .jpeg, .png, .webp";
        }
    }

    if (empty($error)) {
        // 3. Cập nhật thông tin phim vào CSDL
        $updateStmt = $pdo->prepare("UPDATE movies SET title = ?, duration = ?, description = ?, poster = ? WHERE id = ?");
        $updateStmt->execute([$title, $duration, $description, $posterName, $id]);

        header("Location: movies_list.php");
        exit();
    }
}
?>

<!-- TIÊU ĐỀ TRANG -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-rose-500"></i> Cập Nhật Phim
        </h1>
        <p class="text-xs text-slate-400 mt-1">Chỉnh sửa thông tin phim #<?= $movie['id'] ?></p>
    </div>
    <a href="movies_list.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- FORM CHỈNH SỬA PHIM -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-2xl shadow-xl">
    
    <?php if (!empty($error)): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 text-sm p-3 rounded-xl mb-6 font-medium flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Tên Phim <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Thời Lượng (Phút) <span class="text-rose-500">*</span></label>
            <input type="number" name="duration" value="<?= htmlspecialchars($movie['duration']) ?>" required 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Mô Tả Phim</label>
            <textarea name="description" rows="4" 
                      class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white"><?= htmlspecialchars($movie['description']) ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Poster Hiện Tại</label>
            <?php if (!empty($movie['poster'])): ?>
                <div class="mb-3 flex items-center gap-4 bg-slate-950 p-3 border border-slate-800 rounded-xl w-fit">
                    <img src="../uploads/<?= htmlspecialchars($movie['poster']) ?>" alt="Poster" class="w-16 h-20 object-cover rounded-lg border border-slate-700">
                    <span class="text-xs text-slate-400 truncate max-w-[200px]"><?= htmlspecialchars($movie['poster']) ?></span>
                </div>
            <?php endif; ?>

            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Thay Đổi Poster Mới (Nếu có)</label>
            <input type="file" name="poster" accept="image/*" 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700 cursor-pointer">
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-rose-600/30 text-sm">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Cập Nhật Phim
            </button>
            <a href="movies_list.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">
                Hủy
            </a>
        </div>
    </form>
</div>

<?php
// 4. Nhúng Footer Admin
include_once 'footer.php';
?>