<?php
session_start();
// 1. Kiểm tra khoá bảo vệ Session Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin-login.php");
    exit();
}

require_once '../config/database.php';

// 2. Xử lý Xóa Phim (CRUD Delete)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // Lấy tên poster trước để xóa file vật lý trong thư mục uploads
    $stmtGet = $pdo->prepare("SELECT poster FROM movies WHERE id = ?");
    $stmtGet->execute([$id]);
    $movie = $stmtGet->fetch();

    if ($movie) {
        if (!empty($movie['poster']) && file_exists('../uploads/' . $movie['poster'])) {
            unlink('../uploads/' . $movie['poster']);
        }

        // Xóa bản ghi trong MySQL bằng PDO
        $stmtDelete = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmtDelete->execute([$id]);
    }

    header("Location: movies_list.php");
    exit();
}

// 3. Lấy danh sách phim (CRUD Read) bằng PDO
$stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
$movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Phim</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { font-family: sans-serif; background: var(--bg-body, #f4f5f7); margin: 0; }
        .container { padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color, #e2e8f0); }
        th { background: var(--primary-color, #1e1e2d); color: #fff; }
        .poster-img { width: 60px; height: 80px; object-fit: cover; border-radius: 4px; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: #fff; font-size: 14px; }
        .btn-add { background: var(--color-success, #28c76f); display: inline-block; margin-bottom: 15px; }
        .btn-edit { background: var(--color-warning, #ff9f43); }
        .btn-delete { background: var(--color-danger, #ea5455); }
    </style>
</head>
<body>
    <div class="container">
        <h2>Danh Sách Phim Hiện Có</h2>
        <div style="margin-bottom: 15px;">
            <a href="index.php" class="btn" style="background: #6e6b7b; margin-right: 10px;">← Quay lại Dashboard</a>
            <a href="movie_add.php" class="btn btn-add">+ Thêm Phim Mới</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Poster</th>
                    <th>Tên Phim</th>
                    <th>Thời Lượng</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($movies)): ?>
                    <?php foreach ($movies as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php if (!empty($row['poster']) && file_exists('../uploads/' . $row['poster'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($row['poster']) ?>" class="poster-img" alt="Poster">
                            <?php else: ?>
                                <span style="color: #888; font-size: 12px;">Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                        <td><?= htmlspecialchars($row['duration']) ?> phút</td>
                        <td>
                            <a href="movie_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">Sửa</a>
                            <a href="movies_list.php?delete_id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa phim này?')">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">Chưa có phim nào trong cơ sở dữ liệu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>