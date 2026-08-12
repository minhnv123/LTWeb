<?php
require_once 'dp.php';

// Bắt buộc đăng nhập trước khi đặt vé (phải kiểm tra & redirect TRƯỚC khi include header.php,
// vì header.php đã bắt đầu in ra HTML nên không thể header() sau đó)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'booking.php' . (isset($_GET['showtime_id']) ? '?showtime_id=' . (int)$_GET['showtime_id'] : '');
    header('Location: login.php');
    exit;
}

include_once 'header.php';

$showtimeId = isset($_GET['showtime_id']) ? (int)$_GET['showtime_id'] : 0;

// Lấy thông tin suất chiếu + phim + rạp
$stmt = $pdo->prepare("
    SELECT s.id AS showtime_id, s.show_date, s.show_time, s.price,
           m.id AS movie_id, m.title, m.poster, m.duration, m.genre,
           c.id AS cinema_id, c.name AS cinema_name, c.address AS cinema_address
    FROM showtimes s
    JOIN movies m ON m.id = s.movie_id
    JOIN cinemas c ON c.id = s.cinema_id
    WHERE s.id = ?
");
$stmt->execute([$showtimeId]);
$showtime = $stmt->fetch();

if (!$showtime) {
    echo '<div class="max-w-3xl mx-auto px-4 py-24 text-center">';
    echo '<i class="fa-solid fa-triangle-exclamation text-amber-500 text-4xl mb-4"></i>';
    echo '<h1 class="text-xl font-bold text-slate-100">Không tìm thấy suất chiếu</h1>';
    echo '<p class="text-slate-400 mt-2">Suất chiếu bạn chọn không tồn tại hoặc đã bị gỡ bỏ.</p>';
    echo '<a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>';
    echo '</div>';
    include_once 'footer.php';
    exit;
}

// Lấy danh sách ghế đã bán cho suất chiếu này (từ các booking đã thanh toán / đã dùng)
$soldStmt = $pdo->prepare("SELECT seats FROM bookings WHERE showtime_id = ? AND status IN ('pending','paid','used')");
$soldStmt->execute([$showtimeId]);
$soldSeats = [];
foreach ($soldStmt->fetchAll() as $row) {
    foreach (explode(',', $row['seats']) as $s) {
        $s = trim($s);
        if ($s !== '') {
            $soldSeats[$s] = true;
        }
    }
}

// Sơ đồ ghế: 8 hàng (A-H) x 10 ghế/hàng
$rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$seatsPerRow = 10;
$seatPrice = (float)$showtime['price'];

// Danh sách combo bắp nước
$combos = [
    ['id' => 1, 'name' => 'Bắp Rang Bơ (Lớn)', 'price' => 55000, 'icon' => 'fa-solid fa-bucket'],
    ['id' => 2, 'name' => 'Nước Ngọt (Lớn)', 'price' => 25000, 'icon' => 'fa-solid fa-glass-water'],
    ['id' => 3, 'name' => 'Combo Đôi (2 Bắp + 2 Nước)', 'price' => 120000, 'icon' => 'fa-solid fa-champagne-glasses'],
    ['id' => 4, 'name' => 'Combo Nhóm (3 Bắp + 4 Nước)', 'price' => 190000, 'icon' => 'fa-solid fa-people-group'],
];

function money($n) {
    return number_format($n, 0, ',', '.') . 'đ';
}
?>
<style>
    .seat-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        font-size: 11px; font-weight: 600;
        display: flex; align-items: center; justify-content: center;
        transition: all .15s ease;
        border: 1px solid transparent;
        cursor: pointer;
        user-select: none;
    }
    .seat-available { background: #1E293B; color: #94A3B8; border-color: #1E293B; }
    .seat-available:hover { border-color: #F43F5E; color: #F8FAFC; transform: translateY(-1px); }
    .seat-selected { background: #F59E0B; color: #020617; border-color: #F59E0B; }
    .seat-sold { background: #7F1D1D; color: #fca5a5; opacity: .55; cursor: not-allowed; }
    .combo-qty-btn {
        width: 26px; height: 26px; border-radius: 6px;
        background: #1E293B; color: #F8FAFC;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; transition: all .15s ease;
    }
    .combo-qty-btn:hover { background: #F43F5E; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Thông tin phim & suất chiếu -->
    <div class="flex flex-wrap items-center gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-8">
        <img src="uploads/<?php echo htmlspecialchars($showtime['poster']); ?>" onerror="this.src='https://placehold.co/80x110/0f172a/94a3b8?text=CineStar'" class="w-16 h-24 object-cover rounded-lg border border-slate-800" alt="poster">
        <div class="flex-1 min-w-[200px]">
            <h1 class="text-lg sm:text-xl font-extrabold text-slate-100"><?php echo htmlspecialchars($showtime['title']); ?></h1>
            <p class="text-xs text-slate-400 mt-1"><?php echo htmlspecialchars($showtime['genre']); ?> • <?php echo (int)$showtime['duration']; ?> phút</p>
        </div>
        <div class="flex flex-wrap gap-4 text-xs text-slate-300">
            <div><i class="fa-solid fa-location-dot text-amber-400 mr-1.5"></i><?php echo htmlspecialchars($showtime['cinema_name']); ?></div>
            <div><i class="fa-solid fa-calendar-days text-rose-400 mr-1.5"></i><?php echo date('d/m/Y', strtotime($showtime['show_date'])); ?></div>
            <div><i class="fa-solid fa-clock text-rose-400 mr-1.5"></i><?php echo substr($showtime['show_time'], 0, 5); ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- SƠ ĐỒ CHỌN GHẾ -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-8">
                <div class="w-full h-2 rounded-full bg-gradient-to-r from-transparent via-rose-500/70 to-transparent mb-1"></div>
                <p class="text-center text-[11px] uppercase tracking-widest text-slate-500 mb-8">Màn Hình</p>

                <div id="seatMap" class="flex flex-col items-center gap-2 mb-8 overflow-x-auto">
                    <?php foreach ($rows as $row): ?>
                        <div class="flex items-center gap-2">
                            <span class="w-4 text-xs text-slate-500 font-bold"><?php echo $row; ?></span>
                            <div class="flex gap-1.5">
                                <?php for ($n = 1; $n <= $seatsPerRow; $n++):
                                    $seatCode = $row . $n;
                                    $isSold = isset($soldSeats[$seatCode]);
                                ?>
                                    <button type="button"
                                        class="seat-btn <?php echo $isSold ? 'seat-sold' : 'seat-available'; ?>"
                                        data-seat="<?php echo $seatCode; ?>"
                                        <?php echo $isSold ? 'disabled' : ''; ?>>
                                        <?php echo $n; ?>
                                    </button>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex flex-wrap justify-center gap-6 text-xs text-slate-400 border-t border-slate-800 pt-5">
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded seat-available inline-block"></span> Ghế Trống</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded seat-selected inline-block"></span> Đang Chọn</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-4 rounded seat-sold inline-block"></span> Đã Bán</div>
                </div>
            </div>

            <!-- COMBO BẮP NƯỚC -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-8 mt-6">
                <h2 class="text-sm font-bold text-slate-100 uppercase tracking-wide mb-5">
                    <i class="fa-solid fa-mug-saucer text-amber-400 mr-2"></i>Bắp Nước (Tùy Chọn)
                </h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <?php foreach ($combos as $combo): ?>
                        <div class="flex items-center justify-between gap-3 bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 combo-item"
                             data-name="<?php echo htmlspecialchars($combo['name']); ?>"
                             data-price="<?php echo $combo['price']; ?>">
                            <div class="flex items-center gap-3">
                                <i class="<?php echo $combo['icon']; ?> text-amber-400"></i>
                                <div>
                                    <p class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($combo['name']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo money($combo['price']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="combo-qty-btn combo-minus">−</button>
                                <span class="combo-qty w-4 text-center text-sm font-bold text-slate-100">0</span>
                                <button type="button" class="combo-qty-btn combo-plus">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- TÓM TẮT ĐƠN HÀNG -->
        <div class="lg:col-span-1">
            <form action="checkout.php" method="POST" id="bookingForm" class="sticky top-24 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <input type="hidden" name="showtime_id" value="<?php echo (int)$showtime['showtime_id']; ?>">
                <input type="hidden" name="seats" id="seatsInput" value="">
                <input type="hidden" name="combos" id="combosInput" value="">
                <input type="hidden" name="total_price" id="totalPriceInput" value="0">

                <h2 class="text-sm font-bold text-slate-100 uppercase tracking-wide mb-4">Tóm Tắt Đơn Hàng</h2>

                <div class="flex justify-between text-sm text-slate-400 mb-2">
                    <span>Ghế đã chọn</span>
                    <span id="seatCount" class="text-slate-200 font-semibold">0</span>
                </div>
                <p id="seatList" class="text-xs text-amber-400 min-h-[1rem] mb-3">Chưa chọn ghế nào</p>

                <div class="border-t border-slate-800 my-3"></div>

                <div class="flex justify-between text-sm text-slate-400 mb-1">
                    <span>Tiền ghế (<?php echo money($seatPrice); ?> / ghế)</span>
                    <span id="seatTotal" class="text-slate-200">0đ</span>
                </div>
                <div class="flex justify-between text-sm text-slate-400 mb-3">
                    <span>Bắp nước</span>
                    <span id="comboTotal" class="text-slate-200">0đ</span>
                </div>

                <div class="border-t border-slate-800 my-3"></div>

                <div class="flex justify-between items-center mb-6">
                    <span class="text-sm font-bold text-slate-100">Tổng Cộng</span>
                    <span id="grandTotal" class="text-xl font-extrabold text-rose-500">0đ</span>
                </div>

                <button type="submit" id="submitBtn" disabled
                    class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:bg-slate-800 disabled:text-slate-500 disabled:cursor-not-allowed text-white text-sm font-bold transition-all shadow-lg shadow-rose-600/20">
                    <i class="fa-solid fa-lock mr-1.5"></i>Tiến Hành Thanh Toán
                </button>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    const seatPrice = <?php echo (float)$seatPrice; ?>;
    let selectedSeats = [];

    function formatMoney(n) {
        return n.toLocaleString('vi-VN') + 'đ';
    }

    function getCombos() {
        const combos = [];
        $('.combo-item').each(function () {
            const qty = parseInt($(this).find('.combo-qty').text(), 10);
            if (qty > 0) {
                combos.push({
                    name: $(this).data('name'),
                    price: parseFloat($(this).data('price')),
                    qty: qty
                });
            }
        });
        return combos;
    }

    function recalc() {
        const seatTotal = selectedSeats.length * seatPrice;
        const combos = getCombos();
        const comboTotal = combos.reduce((sum, c) => sum + (c.price * c.qty), 0);
        const grandTotal = seatTotal + comboTotal;

        $('#seatCount').text(selectedSeats.length);
        $('#seatList').text(selectedSeats.length ? selectedSeats.sort().join(', ') : 'Chưa chọn ghế nào');
        $('#seatTotal').text(formatMoney(seatTotal));
        $('#comboTotal').text(formatMoney(comboTotal));
        $('#grandTotal').text(formatMoney(grandTotal));

        $('#seatsInput').val(selectedSeats.join(','));
        $('#combosInput').val(JSON.stringify(combos));
        $('#totalPriceInput').val(grandTotal);

        $('#submitBtn').prop('disabled', selectedSeats.length === 0);
    }

    // Chọn / bỏ chọn ghế
    $('.seat-available').on('click', function () {
        const seat = $(this).data('seat');
        if ($(this).hasClass('seat-selected')) {
            $(this).removeClass('seat-selected').addClass('seat-available');
            selectedSeats = selectedSeats.filter(s => s !== seat);
        } else {
            $(this).removeClass('seat-available').addClass('seat-selected');
            selectedSeats.push(seat);
        }
        recalc();
    });

    // Tăng / giảm số lượng combo
    $('.combo-plus').on('click', function () {
        const $qty = $(this).siblings('.combo-qty');
        $qty.text(parseInt($qty.text(), 10) + 1);
        recalc();
    });
    $('.combo-minus').on('click', function () {
        const $qty = $(this).siblings('.combo-qty');
        const current = parseInt($qty.text(), 10);
        if (current > 0) $qty.text(current - 1);
        recalc();
    });

    $('#bookingForm').on('submit', function (e) {
        if (selectedSeats.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 ghế trước khi thanh toán.');
        }
    });

    recalc();
});
</script>

<?php include_once 'footer.php'; ?>
