<?php
require_once 'config/db.php'; // 1. Sửa db.php

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
 * BƯỚC 1: Nhận dữ liệu POST từ booking.php và lưu tạm vào session
 */
if (isset($_POST['selected_seats']) && isset($_POST['showtime_id']) && !isset($_POST['confirm'])) {
    
    // Parse mảng ghế từ JSON: ["A1", "A2"] hoặc [{"code":"A1"}, ...]
    $rawSeats = json_decode($_POST['selected_seats'], true) ?: [];
    $cleanSeats = [];
    foreach ($rawSeats as $s) {
        if (is_array($s) && isset($s['code'])) {
            $cleanSeats[] = (string)$s['code'];
        } elseif (is_string($s) || is_numeric($s)) {
            $cleanSeats[] = (string)$s;
        }
    }
    if (empty($cleanSeats) && !empty($_POST['seats'])) {
        $cleanSeats = array_filter(array_map('trim', explode(',', $_POST['seats'])));
    }

    // Parse object / array bắp nước từ JSON
    $rawFoods = json_decode($_POST['foods'] ?? '[]', true) ?: [];

    $_SESSION['pending_booking'] = [
        'showtime_id' => (int)$_POST['showtime_id'],
        'seats'       => array_values($cleanSeats), 
        'foods'       => $rawFoods, 
    ];
}

$pending = $_SESSION['pending_booking'] ?? null;

// Lấy thông tin Suất chiếu & Phim để tính tiền chuẩn từ DB
$info = null;
$seatTotal = 0;
$foodTotal = 0;
$comboListDetails = [];

if ($pending) {
    // Query lấy thông tin Suất chiếu & Giá vé cơ bản
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

    if ($info) {
        // 1. TÍNH LẠI TIỀN GHẾ BẰNG PHP (CHỐNG SỬA GIÁ F12)
        $seatCount = count($pending['seats']);
        $seatTotal = $seatCount * (float)$info['price'];

        // 2. TÍNH LẠI TIỀN BẮP NƯỚC / COMBO
        if (!empty($pending['foods']) && is_array($pending['foods'])) {
            foreach ($pending['foods'] as $key => $f) {
                if (is_array($f) && isset($f['name'], $f['price'], $f['qty'])) {
                    $qty = (int)$f['qty'];
                    $price = (float)$f['price'];
                    if ($qty > 0) {
                        $itemTotal = $price * $qty;
                        $foodTotal += $itemTotal;
                        $comboListDetails[] = [
                            'id'    => $f['id'] ?? $key,
                            'name'  => $f['name'],
                            'price' => $price,
                            'qty'   => $qty,
                            'total' => $itemTotal
                        ];
                    }
                } elseif (is_numeric($f) && (int)$f > 0) {
                    $qty = (int)$f;
                    $foodId = (int)$key;
                    try {
                        $stmtFood = $pdo->prepare("SELECT id, name, price FROM foods WHERE id = ?");
                        $stmtFood->execute([$foodId]);
                        $foodDb = $stmtFood->fetch();
                        if ($foodDb) {
                            $itemTotal = $foodDb['price'] * $qty;
                            $foodTotal += $itemTotal;
                            $comboListDetails[] = [
                                'id'    => $foodDb['id'],
                                'name'  => $foodDb['name'],
                                'price' => $foodDb['price'],
                                'qty'   => $qty,
                                'total' => $itemTotal
                            ];
                        }
                    } catch (PDOException $e) {
                        // Skip if foods table not present
                    }
                }
            }
        }
    }
}

$grandTotal = $seatTotal + $foodTotal;

/**
 * BƯỚC 2: Xác nhận thanh toán -> kiểm tra lại ghế -> ghi DB
 */
if (isset($_POST['confirm']) && $pending) {
    $showtimeId = $pending['showtime_id'];
    $requestedSeats = $pending['seats'];

    // Kiểm tra ghế đã bị đặt chưa (Anti-double booking)
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

        // Chèn DB với tổng tiền $grandTotal đã tự tính toán lại ở Server
        $insert = $pdo->prepare("
            INSERT INTO bookings (booking_code, user_id, showtime_id, seats, combos, total_price, status)
            VALUES (?, ?, ?, ?, ?, ?, 'paid')
        ");
        $insert->execute([
            $bookingCode,
            $userId,
            $showtimeId,
            implode(',', $requestedSeats),
            json_encode($comboListDetails, JSON_UNESCAPED_UNICODE),
            $grandTotal, // Giá đã kiểm tra an toàn
        ]);

        unset($_SESSION['pending_booking']);
        header('Location: ticket_success.php?code=' . urlencode($bookingCode));
        exit;
    }
}

// RENDER HTML
include_once 'header.php';

if (!$pending || !$info) {
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
        <div class="flex items-center gap-4 pb-5 border-b border-slate-800">
            <img src="uploads/<?php echo htmlspecialchars($info['poster']); ?>" onerror="this.src='https://placehold.co/64x88/0f172a/94a3b8?text=CS'" class="w-14 h-20 object-cover rounded-lg border border-slate-800">
            <div>
                <p class="font-bold text-slate-100"><?php echo htmlspecialchars($info['title']); ?></p>
                <p class="text-xs text-slate-400 mt-1"><?php echo htmlspecialchars($info['cinema_name']); ?></p>
                <p class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($info['show_date'])); ?> • <?php echo substr($info['show_time'], 0, 5); ?></p>
            </div>
        </div>

        <!-- Ghế đã chọn -->
        <div class="py-5 border-b border-slate-800">
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Ghế đã chọn</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($pending['seats'] as $s): ?>
                    <span class="px-3 py-1 rounded-lg bg-amber-500/15 text-amber-400 text-xs font-bold border border-amber-500/30"><?php echo htmlspecialchars($s); ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bắp nước -->
        <?php if (!empty($comboListDetails)): ?>
        <div class="py-5 border-b border-slate-800">
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Bắp nước</p>
            <?php foreach ($comboListDetails as $c): ?>
                <div class="flex justify-between text-sm text-slate-300 mb-1">
                    <span><?php echo htmlspecialchars($c['name']); ?> × <?php echo $c['qty']; ?></span>
                    <span><?php echo money($c['total']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tổng tiền -->
        <div class="pt-5 space-y-1.5">
            <div class="flex justify-between text-sm text-slate-400">
                <span>Tiền ghế</span><span><?php echo money($seatTotal); ?></span>
            </div>
            <div class="flex justify-between text-sm text-slate-400">
                <span>Bắp nước</span><span><?php echo money($foodTotal); ?></span>
            </div>
            <div class="flex justify-between items-center pt-3 mt-2 border-t border-slate-800">
                <span class="font-bold text-slate-100">Tổng Thanh Toán</span>
                <span class="text-2xl font-extrabold text-rose-500"><?php echo money($grandTotal); ?></span>
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