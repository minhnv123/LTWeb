<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra khóa bảo vệ Session Admin
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// 2. Xử lý Xóa Rạp
$message = '';
$error = '';

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmtDelete = $pdo->prepare("DELETE FROM cinemas WHERE id = ?");
        $stmtDelete->execute([$id]);
        header("Location: cinemas_list.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $error = "Không thể xóa rạp này vì đang có lịch chiếu liên kết!";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "Đã xóa rạp thành công!";
}

// 3. Lấy danh sách rạp kèm số lượng suất chiếu
$stmt = $pdo->query("
    SELECT c.*, COUNT(s.id) AS total_showtimes 
    FROM cinemas c 
    LEFT JOIN showtimes s ON s.cinema_id = c.id 
    GROUP BY c.id 
    ORDER BY c.id DESC
");
$cinemas = $stmt->fetchAll();

include_once 'header.php';
?>

<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-building-ngo text-rose-500"></i> Quản Lý Rạp Chiếu
            </h2>
            <p class="text-xs text-slate-400 mt-1">Danh sách các rạp chiếu phim trong hệ thống CineStar</p>
        </div>
        <a href="cinema_add.php" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Thêm Rạp Mới
        </a>
    </div>

    <?php if ($message): ?>
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="p-4 bg-rose-500/10 border border-rose-500/50 text-rose-400 rounded-xl text-sm font-medium flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Table Rạp -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <?php if (empty($cinemas)): ?>
            <div class="py-12 text-center text-slate-500 italic text-sm">
                <i class="fa-solid fa-building-circle-xmark text-4xl mb-3 text-slate-600"></i>
                <p>Chưa có rạp chiếu nào trong hệ thống.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-950/50">
                            <th class="py-4 px-6">ID</th>
                            <th class="py-4 px-6">Tên Rạp</th>
                            <th class="py-4 px-6">Địa Chỉ</th>
                            <th class="py-4 px-6 text-center">Số Suất Chiếu</th>
                            <th class="py-4 px-6 text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-sm">
                        <?php foreach ($cinemas as $c): ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-rose-400">#<?= $c['id'] ?></td>
                                <td class="py-4 px-6 font-bold text-white"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="py-4 px-6 text-slate-300"><?= htmlspecialchars($c['address']) ?></td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        <?= number_format($c['total_showtimes']) ?> suất
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="cinema_edit.php?id=<?= $c['id'] ?>" class="p-2 bg-slate-800 hover:bg-slate-700 text-sky-400 rounded-lg text-xs font-medium transition-all" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="cinemas_list.php?delete_id=<?= $c['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa rạp này?')" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs font-medium transition-all" title="Xóa">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'footer.php'; ?>
