<?php
session_start();
require_once '../config/database.php'; // Thay đường dẫn DB tương ứng dự án

// Xử lý Xóa Phim (CRUD Delete)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: movies_list.php");
    exit();
}

// Lấy danh sách phim (CRUD Read)
$result = $conn->query("SELECT * FROM movies ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Phim</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Tận dụng biến màu đồng bộ dự án */
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
        <a href="movie_add.php" class="btn btn-add">+ Thêm Phim Mới</a>
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
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="../uploads/<?= $row['poster'] ?>" class="poster-img" alt="Poster"></td>
                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                    <td><?= $row['duration'] ?> phút</td>
                    <td>
                        <a href="movie_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="movies_list.php?delete_id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa phim này?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>