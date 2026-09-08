<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin-login.php");
    exit();
}

require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

// Lấy thông tin phim bằng PDO Prepared Statement
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) { 
    header("Location: movies_list.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $duration = intval($_POST['duration']);
    $description = trim($_POST['description']);
    $posterName = $movie['poster']; // Giữ poster cũ mặc định

    // Nếu chọn poster mới thì upload và xóa poster cũ
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['poster']['tmp_name'], '../uploads/' . $newFileName)) {
                
                // Xóa ảnh cũ khỏi thư mục uploads nếu file tồn tại
                if (!empty($movie['poster']) && file_exists('../uploads/' . $movie['poster'])) {
                    unlink('../uploads/' . $movie['poster']);
                }
                
                $posterName = $newFileName;
            }
        }
    }

    // CRUD Update dùng PDO
    $updateStmt = $pdo->prepare("UPDATE movies SET title = ?, duration = ?, description = ?, poster = ? WHERE id = ?");
    $updateStmt->execute([$title, $duration, $description, $posterName, $id]);

    header("Location: movies_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập Nhật Phim</title>
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
        <h2>Sửa Thông Tin Phim</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên Phim:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>
            </div>
            <div class="form-group">
                <label>Thời Lượng (Phút):</label>
                <input type="number" name="duration" value="<?= htmlspecialchars($movie['duration']) ?>" required>
            </div>
            <div class="form-group">
                <label>Mô Tả Phim:</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($movie['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Poster Hiện Tại:</label><br>
                <?php if (!empty($movie['poster'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($movie['poster']) ?>" width="80" style="border-radius:4px; margin-bottom:5px;"><br>
                <?php endif; ?>
                <label>Đổi Poster Mới (Nếu có):</label>
                <input type="file" name="poster" accept="image/*">
            </div>
            <button type="submit" class="btn-submit">Cập Nhật Phim</button>
            <a href="movies_list.php" style="margin-left: 10px; color: #666; text-decoration: none;">Hủy</a>
        </form>
    </div>
</body>
</html>