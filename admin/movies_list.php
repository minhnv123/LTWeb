<?php
// 1. Nhúng Header Admin (Đã bao gồm Session Check, CSDL & Sidebar)
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

// 3. Lấy danh sách phim (CRUD Read)
$stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
$movies = $stmt->fetchAll();
?>

<!-- TIÊU ĐỀ TRANG -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-film text-rose-500"></i> Quản Lý Danh Sách Phim
        </h1>
        <p class="text-xs text-slate-400 mt-1">Danh sách tất cả phim hiện có trong cơ sở dữ liệu CineStar</p>
    </div>
    <a href="movie_add.php" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition-all shadow-lg shadow-rose-600/30 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Thêm Phim Mới
    </a>
</div>

<!-- BẢNG DANH SÁCH PHIM -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                    <th class="py-4 px-6">ID</th>
                    <th class="py-4 px-6">Poster</th>
                    <th class="py-4 px-6">Tên Phim</th>
                    <th class="py-4 px-6">Thời Lượng</th>
                    <th class="py-4 px-6 text-center">Hành Động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-sm">
                <?php if (!empty($movies)): ?>
                    <?php foreach ($movies as $row): ?>
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="py-4 px-6 font-mono text-slate-500">#<?= $row['id'] ?></td>
                        <td class="py-4 px-6">
                            <?php if (!empty($row['poster']) && file_exists('../uploads/' . $row['poster'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($row['poster']) ?>" class="w-12 h-16 object-cover rounded-lg border border-slate-700 shadow-sm" alt="Poster">
                            <?php else: ?>
                                <div class="w-12 h-16 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center text-[10px] text-slate-500 text-center p-1">
                                    Không ảnh
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-6 font-semibold text-white">
                            <?= htmlspecialchars($row['title']) ?>
                        </td>
                        <td class="py-4 px-6 text-slate-300">
                            <span class="bg-slate-800 border border-slate-700 px-2.5 py-1 rounded-md text-xs font-mono">
                                <?= htmlspecialchars($row['duration']) ?> phút
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="movie_edit.php?id=<?= $row['id'] ?>" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 border border-amber-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1">
                                    <i class="fa-solid fa-pen"></i> Sửa
                                </a>
                                <a href="movies_list.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa phim này?')" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 italic">
                            Chưa có phim nào trong cơ sở dữ liệu.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// 4. Nhúng Footer Admin (Tự động đóng Layout)
include_once 'footer.php';
?>