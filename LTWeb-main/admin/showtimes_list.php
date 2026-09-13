<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check quyền Admin
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// 2. Xử lý xóa suất chiếu
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $stmtDelete = $pdo->prepare("DELETE FROM showtimes WHERE id = ?");
    $stmtDelete->execute([$deleteId]);
    header("Location: showtimes_list.php");
    exit();
}

// Cấu hình phân trang
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalRows = $pdo->query("SELECT COUNT(*) FROM showtimes")->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT s.id, s.show_date, s.show_time, s.price, m.title AS movie_title, c.name AS cinema_name 
        FROM showtimes s
        JOIN movies m ON s.movie_id = m.id
        JOIN cinemas c ON s.cinema_id = c.id
        ORDER BY s.show_date DESC, s.show_time ASC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$showtimes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Lịch Chiếu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 p-6 min-h-screen">
    <div class="max-w-6xl mx-auto bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-rose-500"><i class="fa-solid fa-list-check mr-2"></i>Danh Sách Lịch Chiếu</h2>
            <div class="flex items-center gap-3">
                <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold px-4 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-arrow-left mr-1"></i>Dashboard
                </a>
                <a href="showtime_add.php" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all shadow-md shadow-rose-600/30">
                    <i class="fa-solid fa-plus mr-1"></i>Thêm Suất Chiếu
                </a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-800">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Tên Phim</th>
                        <th class="p-3">Rạp / Phòng</th>
                        <th class="p-3">Ngày Chiếu</th>
                        <th class="p-3">Giờ Chiếu</th>
                        <th class="p-3">Giá Vé</th>
                        <th class="p-3 text-right">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php if (count($showtimes) > 0): ?>
                        <?php foreach ($showtimes as $s): ?>
                            <tr class="hover:bg-slate-800/50">
                                <td class="p-3 font-semibold text-slate-400">#<?= $s['id'] ?></td>
                                <td class="p-3 font-medium text-white"><?= htmlspecialchars($s['movie_title']) ?></td>
                                <td class="p-3 text-amber-400"><?= htmlspecialchars($s['cinema_name']) ?></td>
                                <td class="p-3"><?= date('d/m/Y', strtotime($s['show_date'])) ?></td>
                                <td class="p-3 font-bold text-rose-400"><?= date('H:i', strtotime($s['show_time'])) ?></td>
                                <td class="p-3"><?= number_format($s['price'], 0, ',', '.') ?> đ</td>
                                <td class="p-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="showtime_edit.php?id=<?= $s['id'] ?>" class="p-2 bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-white rounded-lg transition-all text-xs" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="showtimes_list.php?delete_id=<?= $s['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa suất chiếu này?')" class="p-2 bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg transition-all text-xs" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="p-4 text-center text-slate-500">Chưa có lịch chiếu nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center gap-2 mt-6">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $i === $page ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-800 text-slate-400 hover:text-white' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>