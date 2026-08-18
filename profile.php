<?php
require_once 'config/dp.php';
include_once 'header.php';

// Yêu cầu đăng nhập để xem trang
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];

// Lấy danh sách lịch sử đặt vé từ database
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- User Info Header -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-8 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-rose-600/20 text-rose-500 border border-rose-500/30 rounded-2xl flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white"><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></h1>
                <p class="text-sm text-slate-400"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
            </div>
        </div>
        <a href="logout.php" class="px-4 py-2 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-xl text-sm font-semibold transition-all">
            <i class="fa-solid fa-right-from-bracket mr-1"></i> Đăng Xuất
        </a>
    </div>

    <!-- Booking History Section -->
    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i class="fa-solid fa-ticket text-rose-500"></i> Lịch Sử Đặt Vé
    </h2>

    <?php if (empty($bookings)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
            <i class="fa-solid fa-ticket-simple text-4xl mb-3 text-slate-600"></i>
            <p>Bạn chưa thực hiện giao dịch đặt vé nào.</p>
            <a href="index.php" class="inline-block mt-4 px-5 py-2.5 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700 transition-all">Đặt vé ngay</a>
        </div>
    <?php else: ?>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 uppercase text-[11px] border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Mã Vé</th>
                            <th class="px-6 py-4">Tên Phim</th>
                            <th class="px-6 py-4">Suất Chiếu</th>
                            <th class="px-6 py-4">Ghế Đặt</th>
                            <th class="px-6 py-4">Tổng Tiền</th>
                            <th class="px-6 py-4">Ngày Đặt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php foreach ($bookings as $b): ?>
                            <tr class="hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-rose-400">#<?php echo htmlspecialchars($b['id']); ?></td>
                                <td class="px-6 py-4 font-semibold text-white"><?php echo htmlspecialchars($b['movie_title'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($b['showtime'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4"><span class="bg-slate-800 text-amber-400 border border-slate-700 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($b['seats'] ?? 'N/A'); ?></span></td>
                                <td class="px-6 py-4 font-semibold text-emerald-400"><?php echo isset($b['total_price']) ? number_format($b['total_price'], 0, ',', '.') . ' VNĐ' : 'N/A'; ?></td>
                                <td class="px-6 py-4 text-slate-400"><?php echo htmlspecialchars($b['created_at'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>