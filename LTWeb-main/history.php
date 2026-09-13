<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// Bắt buộc phải đăng nhập mới xem được lịch sử
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Lấy danh sách lịch sử đặt vé của user kèm thông tin Phim & Rạp
$history = [];
try {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               m.title AS movie_title, m.poster, m.duration,
               c.name AS cinema_name
        FROM bookings b
        LEFT JOIN showtimes s ON b.showtime_id = s.id
        LEFT JOIN movies m ON s.movie_id = m.id
        LEFT JOIN cinemas c ON s.cinema_id = c.id
        WHERE b.user_id = ?
        ORDER BY b.id DESC
    ");
    $stmt->execute([$userId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $history = [];
}

include_once 'header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
            <span class="w-2 h-8 bg-rose-600 rounded-full"></span>
            Lịch Sử Đặt Vé
        </h1>
        <p class="text-slate-400 text-sm mt-1">Danh sách tất cả các vé xem phim bạn đã đặt</p>
    </div>

    <?php if (empty($history)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400 shadow-xl">
            <i class="fa-solid fa-ticket-simple text-5xl mb-4 text-slate-700"></i>
            <p class="text-base font-semibold text-slate-300">Bạn chưa có đơn đặt vé nào.</p>
            <p class="text-xs text-slate-500 mt-1 mb-6">Hãy chọn một bộ phim yêu thích và trải nghiệm ngay!</p>
            <a href="index.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-rose-600/30">
                <i class="fa-solid fa-film"></i> Khám phá phim ngay
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($history as $item): ?>
                <?php
                // Xử lý danh sách ghế (Decode JSON hoặc để nguyên nếu là chuỗi string)
                $seatsDisplay = 'Chưa xác định';
                if (!empty($item['selected_seats'])) {
                    $decodedSeats = json_decode($item['selected_seats'], true);
                    if (is_array($decodedSeats)) {
                        $seatsDisplay = implode(', ', $decodedSeats);
                    } else {
                        $seatsDisplay = $item['selected_seats'];
                    }
                } elseif (!empty($item['seats'])) {
                    $seatsDisplay = $item['seats'];
                }

                // Xử lý Badge trạng thái
                $status = strtolower(trim($item['status'] ?? 'paid'));
                $statusBadge = '<span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold rounded-full">Đã thanh toán</span>';
                if ($status === 'pending') {
                    $statusBadge = '<span class="px-3 py-1 bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold rounded-full">Chờ thanh toán</span>';
                } elseif ($status === 'cancelled' || $status === 'canceled') {
                    $statusBadge = '<span class="px-3 py-1 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold rounded-full">Đã hủy</span>';
                }
                ?>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 md:p-6 shadow-xl flex flex-col md:flex-row gap-5 items-start md:items-center justify-between">
                    <div class="flex gap-4 items-center">
                        <img src="uploads/<?php echo htmlspecialchars($item['poster'] ?? ''); ?>" 
                             onerror="this.src='https://placehold.co/100x150/0f172a/94a3b8?text=NO+IMAGE'" 
                             alt="Poster" 
                             class="w-16 h-24 object-cover rounded-xl border border-slate-800 flex-shrink-0">
                        
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-mono text-xs text-rose-500 font-bold">#MÃ-<?php echo $item['id']; ?></span>
                                <?php echo $statusBadge; ?>
                            </div>

                            <h3 class="text-base font-bold text-white leading-snug">
                                <?php echo htmlspecialchars($item['movie_title'] ?? 'Phim CineStar'); ?>
                            </h3>

                            <p class="text-xs text-slate-400 flex items-center gap-2">
                                <span><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i><?php echo htmlspecialchars($item['cinema_name'] ?? 'Rạp CineStar'); ?></span>
                            </p>

                            <p class="text-xs text-slate-300">
                                <i class="fa-solid fa-couch text-amber-400 mr-1"></i>Ghế: <strong class="text-amber-400 font-bold"><?php echo htmlspecialchars($seatsDisplay); ?></strong>
                            </p>
                        </div>
                    </div>

                    <div class="w-full md:w-auto border-t md:border-t-0 border-slate-800 pt-3 md:pt-0 flex md:flex-col justify-between items-end gap-1">
                        <p class="text-xs text-slate-500">Mã đơn đặt</p>
                        <p class="text-lg font-black text-emerald-400 font-mono">
                            <?php echo number_format($item['total_price'] ?? $item['total_amount'] ?? 0, 0, ',', '.'); ?> đ
                        </p>
                        <p class="text-[11px] text-slate-500">
                            <?php echo isset($item['created_at']) ? date('H:i - d/m/Y', strtotime($item['created_at'])) : ''; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>