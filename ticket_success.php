<?php
session_start();
require_once 'config/database.php';

$code = $_GET['code'] ?? '';

$stmt = $pdo->prepare("
    SELECT b.*, m.title, s.show_date, s.show_time 
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    WHERE b.booking_code = ?
");
$stmt->execute([$code]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Không tìm thấy thông tin vé!");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Vé Xem Phim - <?= htmlspecialchars($booking['booking_code']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen flex items-center justify-center p-4">

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl text-center">
        <h1 class="text-xl font-bold text-white mb-1">Đặt Vé Thành Công!</h1>
        <p class="text-xs text-slate-400 mb-6">Mã vé: <strong class="text-yellow-400"><?= htmlspecialchars($booking['booking_code']) ?></strong></p>

        <div class="bg-slate-950 rounded-xl p-4 mb-6 text-left space-y-2 text-sm border border-slate-800">
            <p><strong class="text-white">Phim:</strong> <?= htmlspecialchars($booking['title']) ?></p>
            <p><strong class="text-white">Ghế:</strong> <span class="text-yellow-400 font-bold"><?= htmlspecialchars($booking['seats']) ?></span></p>
            <p><strong class="text-white">Tổng tiền:</strong> <?= number_format($booking['total_price'], 0, ',', '.') ?> đ</p>
        </div>

        <div class="bg-white p-4 rounded-xl inline-block mb-6">
            <div id="qrcode"></div>
        </div>

        <a href="index.php" class="block w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm">
            Về Trang Chủ
        </a>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= $booking['booking_code'] ?>",
            width: 128,
            height: 128
        });
    </script>
</body>
</html>