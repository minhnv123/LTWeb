<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin-login.php");
    exit();
}
require_once '../config/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $duration = intval($_POST['duration']);
    $description = trim($_POST['description']);

    // Xử lý Upload Poster (Validate .jpg/.png)
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES['poster']['name'];
        $fileTmp = $_FILES['poster']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExts)) {
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = '../uploads/';
            
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                // CRUD Create dùng PDO chuẩn hóa
                $stmt = $pdo->prepare("INSERT INTO movies (title, duration, description, poster) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $duration, $description, $newFileName]);
                
                header("Location: movies_list.php");
                exit();
            } else { $error = "Lỗi khi tải file lên!"; }
        } else { $error = "Chỉ chấp nhận file ảnh dạng .jpg, .png, .webp"; }
    } else { $error = "Vui lòng chọn ảnh poster!"; }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Phim Mới</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { font-family: sans-serif; background: var(--bg-body, #f4f5f7); padding: 30px; }
        .form-card { background: #fff; max-width: 600px; margin: 0 auto; padding: 25px; border-radius: 8px; box-shadow: var(--shadow-card); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: var(--accent-color, #7367f0); color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Thêm Phim Mới</h2>
        <?php if($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên Phim:</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Thời Lượng (Phút):</label>
                <input type="number" name="duration" required>
            </div>
            <div class="form-group">
                <label>Mô Tả Phim:</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Poster Phim (.jpg / .png):</label>
                <input type="file" name="poster" accept="image/*" required>
            </div>
            <button type="submit" class="btn-submit">Lưu Phim</button>
            <a href="movies_list.php" style="margin-left: 10px; color: #666; text-decoration: none;">Hủy</a>
        </form>
    </div>
</body>
</html>