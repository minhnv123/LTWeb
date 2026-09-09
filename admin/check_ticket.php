<?php
require_once '../config/database.php';

$ticket = null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['booking_code'] ?? '');

    if (!empty($code)) {
        // Tìm thông tin vé theo mã
        $stmt = $pdo->prepare("SELECT b.*, u.full_name, m.title AS movie_title, s.show_date, s.show_time 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id
                               JOIN showtimes s ON b.showtime_id = s.id
                               JOIN movies m ON s.movie_id = m.id
                               WHERE b.booking_code = ?");
        $stmt->execute([$code]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            $error = "Mã vé không tồn tại trên hệ thống!";
        } else {
            // Xử lý khi nhấn nút Check-in xác nhận
            if (isset($_POST['confirm_checkin'])) {
                if ($ticket['status'] === 'used') {
                    $error = "Vé này ĐÃ ĐƯỢC SỬ DỤNG trước đó!";
                } elseif ($ticket['status'] === 'cancelled') {
                    $error = "Vé này ĐÃ BỊ HỦY!";
                } else {
                    // Cập nhật trạng thái thành 'used'
                    $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'used' WHERE id = ?");
                    $updateStmt->execute([$ticket['id']]);
                    $ticket['status'] = 'used'; // Cập nhật trạng thái hiển thị
                    $message = "Xác nhận Soát Vé Thành Công!";
                }
            }
        }
    } else {
        $error = "Vui lòng nhập hoặc quét mã vé!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Soát Vé Xem Phim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 p-6 min-h-screen">
    <div class="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
        <h2 class="text-xl font-bold text-rose-500 mb-4 text-center"><i class="fa-solid fa-qrcode mr-2"></i>Soát Vé Xem Phim</h2>

        <!-- Form Nhập/Quét Mã Vé -->
        <form method="POST" class="flex gap-2 mb-6">
            <input type="text" name="booking_code" value="<?= htmlspecialchars($_POST['booking_code'] ?? '') ?>" placeholder="Nhập hoặc quét mã vé (VD: TICKET123)..." autofocus required class="flex-1 bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm focus:border-rose-500 focus:outline-none">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-all">Tra Cứu</button>
        </form>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 text-sm rounded-lg text-center font-bold"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-500/20 border border-rose-500/50 text-rose-400 text-sm rounded-lg text-center font-bold"><?= $error ?></div>
        <?php endif; ?>

        <!-- Hiển thị thông tin vé tra cứu -->
        <?php if ($ticket): ?>
            <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-3">
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs text-slate-400">Mã Vé:</span>
                    <span class="font-bold text-rose-400"><?= $ticket['booking_code'] ?></span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs text-slate-400">Khách Hàng:</span>
                    <span class="font-medium text-white"><?= htmlspecialchars($ticket['full_name']) ?></span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs text-slate-400">Phim:</span>
                    <span class="font-semibold text-amber-400"><?= htmlspecialchars($ticket['movie_title']) ?></span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs text-slate-400">Suất Chiếu:</span>
                    <span><?= date('H:i', strtotime($ticket['show_time'])) ?> - <?= date('d/m/Y', strtotime($ticket['show_date'])) ?></span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs text-slate-400">Ghế Đặt:</span>
                    <span class="font-bold text-emerald-400"><?= $ticket['seats'] ?></span>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <span class="text-xs text-slate-400">Trạng Thái:</span>
                    <?php if ($ticket['status'] === 'used'): ?>
                        <span class="px-2.5 py-1 bg-slate-800 text-slate-400 text-xs font-bold rounded">Đã Sử Dụng</span>
                    <?php else: ?>
                        <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold rounded">Hợp Lệ (Đã Thanh Toán)</span>
                    <?php endif; ?>
                </div>

                <?php if ($ticket['status'] !== 'used' && $ticket['status'] !== 'cancelled'): ?>
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="booking_code" value="<?= $ticket['booking_code'] ?>">
                        <button type="submit" name="confirm_checkin" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg transition-all shadow-lg shadow-emerald-600/30">
                            XÁC NHẬN CHO VÀO RẠP
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>