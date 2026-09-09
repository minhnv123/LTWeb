<?php
// 1. Nhúng Header Admin (Đã bao gồm Session Check, CSDL & Sidebar)
include_once 'header.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    // 2. Xử lý Upload Poster (Validate file .jpg, .jpeg, .png, .webp)
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES['poster']['name'];
        $fileTmp = $_FILES['poster']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExts)) {
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = '../uploads/';
            
            // Tự động tạo thư mục uploads ở gốc nếu chưa có
            if (!is_dir($uploadDir)) { 
                mkdir($uploadDir, 0777, true); 
            }

            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                // 3. Thêm phim vào CSDL
                $stmt = $pdo->prepare("INSERT INTO movies (title, duration, description, poster) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $duration, $description, $newFileName]);
                
                header("Location: movies_list.php");
                exit();
            } else { 
                $error = "Lỗi khi lưu file ảnh vào hệ thống!"; 
            }
        } else { 
            $error = "Chỉ chấp nhận file ảnh định dạng .jpg, .jpeg, .png, .webp"; 
        }
    } else { 
        $error = "Vui lòng chọn file ảnh poster!"; 
    }
}
?>

<!-- TIÊU ĐỀ TRANG -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-rose-500"></i> Thêm Phim Mới
        </h1>
        <p class="text-xs text-slate-400 mt-1">Thêm dữ liệu phim mới vào hệ thống CineStar</p>
    </div>
    <a href="movies_list.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
</div>

<!-- FORM THÊM PHIM (ĐỒNG BỘ TAILWIND DARK THEME) -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-2xl shadow-xl">
    
    <?php if (!empty($error)): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 text-sm p-3 rounded-xl mb-6 font-medium flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Tên Phim <span class="text-rose-500">*</span></label>
            <input type="text" name="title" required placeholder="Nhập tên phim..." 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Thời Lượng (Phút) <span class="text-rose-500">*</span></label>
            <input type="number" name="duration" required placeholder="Ví dụ: 120" 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Mô Tả Phim</label>
            <textarea name="description" rows="4" placeholder="Tóm tắt nội dung phim..." 
                      class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500 text-white"></textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Ảnh Poster (.jpg, .png, .webp) <span class="text-rose-500">*</span></label>
            <input type="file" name="poster" accept="image/*" required 
                   class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700 cursor-pointer">
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-rose-600/30 text-sm">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu Phim
            </button>
            <a href="movies_list.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold px-6 py-3 rounded-xl transition-all text-sm">
                Hủy
            </a>
        </div>
    </form>
</div>

<?php
// 4. Nhúng Footer Admin (Tự đóng Layout)
include_once 'footer.php';
?>