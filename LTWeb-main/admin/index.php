<?php
include_once 'header.php';

// 2. Lấy dữ liệu thống kê từ CSDL
try {
    // Đếm tổng số phim
    $countMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn() ?: 0;

    // Đếm tổng số người dùng (khách hàng)
    $countUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE LOWER(TRIM(role)) != 'admin'")->fetchColumn() ?: 0;

    // Đếm tổng số suất chiếu
    $countShowtimes = $pdo->query("SELECT COUNT(*) FROM showtimes")->fetchColumn() ?: 0;

    // Thống kê đơn đặt vé
    $countBookings = 0;
    $totalRevenue = 0;
    $recentBookings = [];

    $checkBookings = $pdo->query("SHOW TABLES LIKE 'bookings'")->fetch();
    if ($checkBookings) {
        $countBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() ?: 0;
        $totalRevenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('paid', 'used', 'completed')")->fetchColumn() ?: 0;
        
        // Lấy 5 đơn đặt vé mới nhất
        $stmtRecent = $pdo->query("
            SELECT b.*, u.full_name, m.title as movie_title 
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            LEFT JOIN showtimes s ON b.showtime_id = s.id 
            LEFT JOIN movies m ON s.movie_id = m.id 
            ORDER BY b.id DESC LIMIT 5
        ");
        $recentBookings = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $countMovies = $countUsers = $countShowtimes = $countBookings = $totalRevenue = 0;
    $recentBookings = [];
}
?>

<!-- Top Bar Welcome -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
    <div>
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            Chào mừng trở lại, <span class="text-rose-500"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin') ?></span>!
        </h2>
        <p class="text-xs text-slate-400 mt-1">Dưới đây là thống kê tình hình hoạt động của rạp chiếu phim.</p>
    </div>
    <a href="movie_add.php" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-plus"></i> Thêm Phim Mới
    </a>
</div>

<!-- STATS GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Thẻ 1: Tổng Phim -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng Số Phim</p>
            <h3 class="text-2xl font-black text-white mt-1"><?= number_format($countMovies) ?></h3>
            <a href="movies_list.php" class="text-[11px] text-rose-400 hover:underline mt-2 inline-block font-medium">Quản lý phim &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xl">
            <i class="fa-solid fa-film"></i>
        </div>
    </div>

    <!-- Thẻ 2: Tổng Suất Chiếu -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lịch Chiếu Phim</p>
            <h3 class="text-2xl font-black text-white mt-1"><?= number_format($countShowtimes) ?></h3>
            <a href="showtimes_list.php" class="text-[11px] text-emerald-400 hover:underline mt-2 inline-block font-medium">Quản lý lịch chiếu &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
    </div>

    <!-- Thẻ 3: Đơn Đặt Vé -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Đơn Đặt Vé</p>
            <h3 class="text-2xl font-black text-white mt-1"><?= number_format($countBookings) ?></h3>
            <a href="check_ticket.php" class="text-[11px] text-amber-400 hover:underline mt-2 inline-block font-medium">Soát vé khách hàng &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl">
            <i class="fa-solid fa-ticket"></i>
        </div>
    </div>

    <!-- Thẻ 4: Doanh Thu -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng Doanh Thu</p>
            <h3 class="text-xl font-black text-emerald-400 mt-1"><?= number_format($totalRevenue) ?> đ</h3>
            <span class="text-[11px] text-slate-500 mt-2 block">Tự động tính từ CSDL</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center text-xl">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
    </div>
</div>

<!-- RECENT BOOKINGS TABLE -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-history text-rose-500"></i> Đơn Đặt Vé Mới Nhất
        </h3>
        <a href="check_ticket.php" class="text-xs text-rose-400 hover:underline font-medium">Soát vé &rarr;</a>
    </div>

    <?php if (!empty($recentBookings)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Mã Đơn</th>
                        <th class="py-3 px-4">Khách Hàng</th>
                        <th class="py-3 px-4">Phim</th>
                        <th class="py-3 px-4">Tổng Tiền</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-xs">
                    <?php foreach ($recentBookings as $b): ?>
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-rose-400">#<?= htmlspecialchars($b['booking_code'] ?? $b['id']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($b['full_name'] ?? 'Khách vãng lai') ?></td>
                            <td class="py-3 px-4 font-semibold text-white"><?= htmlspecialchars($b['movie_title'] ?? 'N/A') ?></td>
                            <td class="py-3 px-4 font-bold text-emerald-400"><?= number_format($b['total_price'] ?? 0) ?> đ</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    <?= htmlspecialchars($b['status'] ?? 'Thành công') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="py-8 text-center text-slate-500 italic text-sm">
            Chưa có đơn đặt vé nào gần đây.
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>