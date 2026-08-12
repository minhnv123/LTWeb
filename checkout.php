<?php
require_once 'dp.php';

// LƯU Ý: mọi kiểm tra đăng nhập / redirect / ghi CSDL phải xử lý XONG trước khi
// include header.php, vì header.php in ra HTML ngay lập tức (không thể header() sau đó).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$errors = [];

function money($n) {
    return number_format($n, 0, ',', '.') . 'đ';
}

/**
 * BƯỚC 1: Nhận dữ liệu ghế/combo từ booking.php (POST) và lưu tạm vào session
 * để hiển thị trang xác nhận trước khi ghi vào CSDL.
 */
if (isset($_POST['seats']) && isset($_POST['showtime_id']) && !isset($_POST['confirm'])) {
    $_SESSION['pending_booking'] = [
        'showtime_id' => (int)$_POST['showtime_id'],
        'seats'       => trim($_POST['seats']),
        'combos'      => isset($_POST['combos']) ? $_POST['combos'] : '[]',
        'total_price' => (float)$_POST['total_price'],
    ];
}

/**
 * BƯỚC 2: Xác nhận thanh toán -> kiểm tra lại ghế còn trống -> ghi vào bảng bookings
 */
if (isset($_POST['confirm']) && isset($_SESSION['pending_booking'])) {
    $pb = $_SESSION['pending_booking'];
    $showtimeId = $pb['showtime_id'];
    $requestedSeats = array_filter(array_map('trim', explode(',', $pb['seats'])));

    // Kiểm tra lại ghế đã bị người khác đặt trong lúc chờ hay chưa (chống trùng ghế)
    $soldStmt = $pdo->prepare("SELECT seats FROM bookings WHERE showtime_id = ? AND status IN ('pending','paid','used')");
    $soldStmt->execute([$showtimeId]);
    $soldSeats = [];
    foreach ($soldStmt->fetchAll() as $row) {
        foreach (explode(',', $row['seats']) as $s) {
            $soldSeats[trim($s)] = true;
        }
    }

    $conflict = array_filter($requestedSeats, fn($s) => isset($soldSeats[$s]));

    if (empty($requestedSeats)) {
        $errors[] = 'Không có ghế nào được chọn.';
    } elseif (!empty($conflict)) {
        $errors[] = 'Rất tiếc, ghế ' . implode(', ', $conflict) . ' vừa được người khác đặt. Vui lòng chọn ghế khác.';
        unset($_SESSION['pending_booking']);
    } else {
        // Tạo mã vé duy nhất
        do {
            $bookingCode = 'CS' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $check = $pdo->prepare("SELECT id FROM bookings WHERE booking_code = ?");
            $check->execute([$bookingCode]);
        } while ($check->fetch());

        $insert = $pdo->prepare("
            INSERT INTO bookings (booking_code, user_id, showtime_id, seats, combos, total_price, status)
            VALUES (?, ?, ?, ?, ?, ?, 'paid')
        ");
        $insert->execute([
            $bookingCode,
            $userId,
            $showtimeId,
            implode(',', $requestedSeats),
            $pb['combos'],
            $pb['total_price'],
        ]);

        unset($_SESSION['pending_booking']);
        header('Location: ticket_success.php?code=' . urlencode($bookingCode));
        exit;
    }
}

$pending = $_SESSION['pending_booking'] ?? null;

// Từ đây trở đi chỉ còn RENDER HTML (không còn header()/redirect nào nữa) nên mới include header.php
include_once 'header.php';

// Nếu không có đơn hàng nào đang chờ, đưa người dùng quay lại trang chủ
if (!$pending) {
    echo '<div class="max-w-2xl mx-auto px-4 py-24 text-center">';
    if (!empty($errors)) {
        echo '<p class="text-rose-400 mb-6">' . htmlspecialchars($errors[0]) . '</p>';
    }
    echo '<i class="fa-solid fa-ticket text-slate-700 text-4xl mb-4"></i>';
    echo '<h1 class="text-xl font-bold text-slate-100">Không có đơn đặt vé nào đang chờ thanh toán</h1>';
    echo '<a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>';
    echo '</div>';
    include_once 'footer.php';
    exit;
}

// Lấy thông tin suất chiếu để hiển thị lại cho khách xác nhận
$stmt = $pdo->prepare("
    SELECT s.show_date, s.show_time, s.price,
           m.title, m.poster,
           c.name AS cinema_name
    FROM showtimes s
    JOIN movies m ON m.id = s.movie_id
    JOIN cinemas c ON c.id = s.cinema_id
    WHERE s.id = ?
");
$stmt->execute([$pending['showtime_id']]);
$info = $stmt->fetch();

$seats = array_filter(array_map('trim', explode(',', $pending['seats'])));
$combos = json_decode($pending['combos'], true) ?: [];
$seatTotal = count($seats) * (float)($info['price'] ?? 0);
$comboTotal = array_reduce($combos, fn($sum, $c) => $sum + ($c['price'] * $c['qty']), 0);
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-xl font-extrabold text-slate-100 mb-1"><i class="fa-solid fa-file-invoice text-rose-500 mr-2"></i>Xác Nhận & Thanh Toán</h1>
    <p class="text-sm text-slate-400 mb-6">Vui lòng kiểm tra lại thông tin đơn hàng trước khi hoàn tất.</p>

    <?php if (!empty($errors)): ?>
        <div class="bg-rose-950/50 border border-rose-800 text-rose-300 text-sm rounded-xl px-4 py-3 mb-6">
            <i class="fa-solid fa-circle-exclamation mr-2"></i><?php echo htmlspecialchars($errors[0]); ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <?php if ($info): ?>
        <div class="flex items-center gap-4 pb-5 border-b border-slate-800">
            <img src="uploads/<?php echo htmlspecialchars($info['poster']); ?>" onerror="this.src='https://placehold.co/64x88/0f172a/94a3b8?text=CS'" class="w-14 h-20 object-cover rounded-lg border border-slate-800">
            <div>
                <p class="font-bold text-slate-100"><?php echo htmlspecialchars($info['title']); ?></p>
                <p class="text-xs text-slate-400 mt-1"><?php echo htmlspecialchars($info['cinema_name']); ?></p>
                <p class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($info['show_date'])); ?> • <?php echo substr($info['show_time'], 0, 5); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="py-5 border-b border-slate-800">
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Ghế đã chọn</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($seats as $s): ?>
                    <span class="px-3 py-1 rounded-lg bg-amber-500/15 text-amber-400 text-xs font-bold border border-amber-500/30"><?php echo htmlspecialchars($s); ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($combos)): ?>
        <div class="py-5 border-b border-slate-800">
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Bắp nước</p>
            <?php foreach ($combos as $c): ?>
                <div class="flex justify-between text-sm text-slate-300 mb-1">
                    <span><?php echo htmlspecialchars($c['name']); ?> × <?php echo (int)$c['qty']; ?></span>
                    <span><?php echo money($c['price'] * $c['qty']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="pt-5 space-y-1.5">
            <div class="flex justify-between text-sm text-slate-400">
                <span>Tiền ghế</span><span><?php echo money($seatTotal); ?></span>
            </div>
            <div class="flex justify-between text-sm text-slate-400">
                <span>Bắp nước</span><span><?php echo money($comboTotal); ?></span>
            </div>
            <div class="flex justify-between items-center pt-3 mt-2 border-t border-slate-800">
                <span class="font-bold text-slate-100">Tổng Thanh Toán</span>
                <span class="text-2xl font-extrabold text-rose-500"><?php echo money($pending['total_price']); ?></span>
            </div>
        </div>

        <form method="POST" class="mt-6 flex gap-3">
            <a href="booking.php?showtime_id=<?php echo (int)$pending['showtime_id']; ?>" class="flex-1 text-center py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition-all">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>Chọn Lại Ghế
            </a>
            <button type="submit" name="confirm" value="1" class="flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold transition-all shadow-lg shadow-rose-600/20">
                <i class="fa-solid fa-circle-check mr-1.5"></i>Xác Nhận Thanh Toán
            </button>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?>
