<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 1);

// BƯỚC 2: Khi bấm Xác Nhận Thanh Toán -> Thực hiện lưu CSDL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $showtimeId = (int)$_POST['showtime_id'];
    $seats = trim($_POST['seats']);
    $seatsData = json_decode($_POST['seats_json'], true) ?: [];
    $combosData = $_POST['combos_json'];
    $totalPrice = (float)$_POST['total_price'];

    if (!empty($seatsData)) {
        try {
            $pdo->beginTransaction();

            $bookingCode = 'CS' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            // 1. Thêm đơn hàng vào bảng `bookings`
            $stmtBooking = $pdo->prepare("
                INSERT INTO bookings (booking_code, user_id, showtime_id, seats, combos, total_price, status)
                VALUES (?, ?, ?, ?, ?, ?, 'paid')
            ");
            $stmtBooking->execute([$bookingCode, $userId, $showtimeId, $seats, $combosData, $totalPrice]);
            $bookingId = $pdo->lastInsertId();

            // 2. Thêm chi tiết ghế vào bảng `booking_details`
            $stmtDetail = $pdo->prepare("
                INSERT INTO booking_details (booking_id, seat_number, seat_type, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($seatsData as $item) {
                $stmtDetail->execute([$bookingId, $item['code'], $item['type'], $item['price']]);
            }

            $pdo->commit();
            header("Location: ticket_success.php?code=" . urlencode($bookingCode));
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi thanh toán: " . $e->getMessage());
        }
    }
}

// BƯỚC 1: Hiển thị thông tin chờ thanh toán từ booking.php gửi sang
$showtimeId = (int)($_POST['showtime_id'] ?? 0);
$seats = $_POST['seats'] ?? '';
$seatsJson = $_POST['seats_json'] ?? '[]';
$combosJson = $_POST['combos_json'] ?? '[]';
$totalPrice = (float)($_POST['total_price'] ?? 0);

$combos = json_decode($combosJson, true) ?: [];

if (empty($seats)) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng - CineStar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen py-10">

    <div class="max-w-2xl mx-auto px-4">
        <h1 class="text-xl font-extrabold text-white mb-6 text-center">
            <i class="fa-solid fa-credit-card text-yellow-500 mr-2"></i>Màn Hình Thanh Toán
        </h1>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
            
            <!-- THÔNG TIN VÉ -->
            <div class="border-b border-slate-800 pb-4">
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-2 font-bold">Danh sách ghế đặt</p>
                <p class="text-lg font-bold text-yellow-400"><?= htmlspecialchars($seats) ?></p>
            </div>

            <!-- BẮP NƯỚC ĐÃ CHỌN -->
            <?php if (!empty($combos)): ?>
            <div class="border-b border-slate-800 pb-4">
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-2 font-bold">Bắp nước kèm theo</p>
                <ul class="text-sm space-y-1">
                    <?php foreach ($combos as $c): ?>
                        <li class="flex justify-between text-slate-300">
                            <span><?= htmlspecialchars($c['name']) ?> x <?= $c['qty'] ?></span>
                            <span class="font-mono"><?= number_format($c['price'] * $c['qty'], 0, ',', '.') ?>đ</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- CHỌN PHƯƠNG THỨC THANH TOÁN -->
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-3 font-bold">Chọn phương thức thanh toán</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label id="label-qr" class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-yellow-500 bg-slate-950 cursor-pointer transition-all">
                        <input type="radio" name="pay_method" value="qr" checked class="accent-yellow-500" onchange="togglePayment('qr')">
                        <i class="fa-solid fa-qrcode text-yellow-500 text-lg"></i>
                        <div>
                            <p class="text-sm font-semibold text-white">Quét mã QR</p>
                            <p class="text-xs text-slate-400">MoMo / Banking</p>
                        </div>
                    </label>

                    <label id="label-card" class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-slate-800 bg-slate-950 cursor-pointer transition-all">
                        <input type="radio" name="pay_method" value="card" class="accent-yellow-500" onchange="togglePayment('card')">
                        <i class="fa-solid fa-credit-card text-slate-400 text-lg"></i>
                        <div>
                            <p class="text-sm font-semibold text-white">Thẻ ATM / Visa / Master</p>
                            <p class="text-xs text-slate-400">Thẻ ngân hàng trực tuyến</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- KHU VỰC 1: QUÉT MÃ QR -->
            <div id="payment-qr-block" class="bg-slate-950 p-5 rounded-xl border border-slate-800 text-center">
                <p class="text-xs text-slate-400 mb-3">Mở app Ngân hàng hoặc Ví điện tử để quét mã</p>
                <img src="https://img.vietqr.io/image/MB-0123456789-compact.png?amount=<?= $totalPrice ?>&addInfo=Thanh%20toan%20ve%20phim" class="w-44 h-44 mx-auto rounded-lg border border-slate-700 shadow-md">
            </div>

            <!-- KHU VỰC 2: FORM NHẬP THẺ REALISTIC -->
            <div id="payment-card-block" class="bg-slate-950 p-5 rounded-xl border border-slate-800 hidden space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Số thẻ (16 chữ số)</label>
                    <div class="relative">
                        <input type="text" id="card_number" maxlength="19" placeholder="4123 4567 8901 2345" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono focus:outline-none focus:border-yellow-500 text-sm">
                        <i class="fa-solid fa-credit-card absolute right-3 top-3 text-slate-500"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Hạn thẻ (MM/YY)</label>
                        <input type="text" id="card_expiry" placeholder="12/28" maxlength="5" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono focus:outline-none focus:border-yellow-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Mã CVC / CVV</label>
                        <input type="password" id="card_cvv" placeholder="123" maxlength="3" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono focus:outline-none focus:border-yellow-500 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Tên chủ thẻ (Không dấu)</label>
                    <input type="text" id="card_name" placeholder="NGUYEN VAN A" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white font-mono uppercase focus:outline-none focus:border-yellow-500 text-sm">
                </div>
            </div>

            <!-- TỔNG TIỀN -->
            <div class="flex justify-between items-center border-t border-slate-800 pt-4">
                <span class="font-bold text-white">Số tiền thanh toán:</span>
                <span class="text-2xl font-extrabold text-yellow-400 font-mono"><?= number_format($totalPrice, 0, ',', '.') ?> đ</span>
            </div>

            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="showtime_id" value="<?= $showtimeId ?>">
                <input type="hidden" name="seats" value="<?= htmlspecialchars($seats) ?>">
                <input type="hidden" name="seats_json" value="<?= htmlspecialchars($seatsJson) ?>">
                <input type="hidden" name="combos_json" value="<?= htmlspecialchars($combosJson) ?>">
                <input type="hidden" name="total_price" value="<?= $totalPrice ?>">
                
                <button type="submit" name="process_payment" value="1" 
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-yellow-500/20 text-sm cursor-pointer">
                    Xác Nhận & Thanh Toán Ngay
                </button>
            </form>

        </div>
    </div>

    <script>
        let currentMethod = 'qr';

        function togglePayment(method) {
            currentMethod = method;
            const qrBlock = document.getElementById('payment-qr-block');
            const cardBlock = document.getElementById('payment-card-block');
            const labelQr = document.getElementById('label-qr');
            const labelCard = document.getElementById('label-card');

            if (method === 'card') {
                qrBlock.classList.add('hidden');
                cardBlock.classList.remove('hidden');
                labelCard.className = "flex items-center gap-3 p-3.5 rounded-xl border-2 border-yellow-500 bg-slate-950 cursor-pointer transition-all";
                labelQr.className = "flex items-center gap-3 p-3.5 rounded-xl border-2 border-slate-800 bg-slate-950 cursor-pointer transition-all";
            } else {
                qrBlock.classList.remove('hidden');
                cardBlock.classList.add('hidden');
                labelQr.className = "flex items-center gap-3 p-3.5 rounded-xl border-2 border-yellow-500 bg-slate-950 cursor-pointer transition-all";
                labelCard.className = "flex items-center gap-3 p-3.5 rounded-xl border-2 border-slate-800 bg-slate-950 cursor-pointer transition-all";
            }
        }

        function validateForm() {
            if (currentMethod === 'card') {
                const cardNum = document.getElementById('card_number').value.trim();
                const cardName = document.getElementById('card_name').value.trim();
                if (cardNum.length < 12 || cardName === '') {
                    alert('Vui lòng nhập đầy đủ và chính xác thông tin Thẻ thanh toán!');
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>