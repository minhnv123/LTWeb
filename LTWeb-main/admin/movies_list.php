<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra khóa bảo vệ Session Admin
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Kết nối CSDL chuẩn
require_once '../config/db.php';
include_once 'header.php';

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

// 3. Cấu hình & Xử lý Phân trang
$limit = 6; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalRows = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// 4. Lấy danh sách phim phân trang bằng PDO
$sql = "SELECT * FROM movies ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Sách Phim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Thêm thư viện SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-8 px-4 md:px-8">

    <div class="max-w-6xl mx-auto">
        <!-- Header & Điều hướng -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 bg-slate-900 border border-slate-800 p-5 rounded-2xl">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-film text-rose-500"></i> Quản Lý Phim
                </h2>
                <p class="text-xs text-slate-400 mt-1">Danh sách tất cả các phim đang có trong hệ thống</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Dashboard
                </a>
                <a href="movie_add.php" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Thêm Phim Mới
                </a>
            </div>
        </div>

        <!-- Bảng Danh Sách Phim -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 text-xs uppercase border-b border-slate-800">
                        <tr>
                            <th scope="col" class="py-4 px-5 w-16 text-center">ID</th>
                            <th scope="col" class="py-4 px-5 w-24">Poster</th>
                            <th scope="col" class="py-4 px-5">Tên Phim</th>
                            <th scope="col" class="py-4 px-5">Thể Loại</th>
                            <th scope="col" class="py-4 px-5">Trạng Thái</th>
                            <th scope="col" class="py-4 px-5">Thời Lượng</th>
                            <th scope="col" class="py-4 px-5 text-right w-36">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php if (!empty($movies)): ?>
                            <?php foreach ($movies as $row): ?>
                            <tr class="hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 px-5 text-center font-mono text-xs text-slate-500">#<?= $row['id'] ?></td>
                                <td class="py-3 px-5">
                                    <?php if (!empty($row['poster']) && file_exists('../uploads/' . $row['poster'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($row['poster']) ?>" class="w-12 h-16 object-cover rounded-lg border border-slate-800" alt="Poster">
                                    <?php else: ?>
                                        <div class="w-12 h-16 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-center text-[10px] text-slate-600 text-center p-1">
                                            Không ảnh
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-5 font-semibold text-white">
                                    <?= htmlspecialchars($row['title']) ?>
                                </td>
                                <td class="py-3 px-5 text-slate-400">
                                    <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-full text-xs text-rose-400">
                                        <?= htmlspecialchars($row['genre'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-5">
                                    <?php if (($row['status'] ?? 'now_showing') === 'coming_soon'): ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full inline-flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-[10px]"></i> Sắp Chiếu
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-play text-[10px]"></i> Đang Chiếu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-5 text-slate-400">
                                    <i class="fa-regular fa-clock text-xs mr-1 text-slate-500"></i>
                                    <?= htmlspecialchars($row['duration']) ?> phút
                                </td>
                                <td class="py-3 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="movie_edit.php?id=<?= $row['id'] ?>" class="p-2 bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-white rounded-lg transition-all text-xs" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <!-- Nút xóa dùng SweetAlert2 -->
                                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="p-2 bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg transition-all text-xs" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 italic">
                                    Chưa có dữ liệu phim nào trong cơ sở dữ liệu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Nút Phân Trang -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 pt-6 pb-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $i === $page ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-800 text-slate-400 hover:text-white' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Script xử lý Popup -->
    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: '',
            text: 'Bạn có chắc chắn muốn xóa?',
            icon: 'warning',
            iconColor: '#f97316',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3b82f6',
            confirmButtonText: 'Xác nhận xóa!',
            cancelButtonText: 'Huỷ',
            customClass: {
                popup: 'rounded-2xl shadow-2xl bg-white text-slate-800'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'movies_list.php?delete_id=' + id;
            }
        });
    }
    </script>
</body>
</html>