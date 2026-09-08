<?php
ob_start();
require_once 'config/dp.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$showtimeId = isset($_GET['showtime_id']) ? (int)$_GET['showtime_id'] : 0;

$sql = "
    SELECT s.id, s.show_date, s.show_time, s.price,
           m.title, m.poster, m.director, m.cast, m.genre, m.duration,
           c.name AS cinema_name
    FROM showtimes s
    JOIN movies m ON m.id = s.movie_id
    JOIN cinemas c ON c.id = s.cinema_id
    " . ($showtimeId ? "WHERE s.id = ?" : "") . "
    ORDER BY s.show_date ASC, s.show_time ASC
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute($showtimeId ? [$showtimeId] : []);
$showtime = $stmt->fetch();

include_once 'header.php';

if (!$showtime) {
    echo '<div class="max-w-2xl mx-auto px-4 py-24 text-center">';
    echo '<i class="fa-solid fa-clapperboard text-slate-700 text-4xl mb-4"></i>';
    echo '<h1 class="text-xl font-bold text-slate-100">Không tìm thấy suất chiếu</h1>';
    echo '<p class="text-slate-400 mt-2">Suất chiếu không tồn tại hoặc đã bị xoá. Vui lòng chọn suất chiếu khác.</p>';
    echo '<a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>';
    echo '</div>';
    include_once 'footer.php';
    exit;
}

$showtimeId = (int)$showtime['id'];

$movie = [
    'title'    => $showtime['title'],
    'poster'   => $showtime['poster'],
    'director' => $showtime['director'],
    'cast'     => $showtime['cast'],
    'genre'    => $showtime['genre'],
    'duration' => $showtime['duration'],
    'showtime' => substr($showtime['show_time'], 0, 5),
    'date'     => date('d/m/Y', strtotime($showtime['show_date'])),
    'room'     => $showtime['cinema_name'],
];

// Giá vé lấy từ CSDL; ghế VIP phụ thu thêm (chỉnh mức phụ thu tuỳ ý bạn)
$standardPrice = (float)$showtime['price'];
$vipSurcharge  = 20000;
$vipPrice      = $standardPrice + $vipSurcharge;

// Lấy danh sách ghế đã bán CHO ĐÚNG suất chiếu này từ bảng bookings, thay vì mảng cứng
$soldStmt = $pdo->prepare("
    SELECT seats FROM bookings
    WHERE showtime_id = ? AND status IN ('pending','paid','used')
");
$soldStmt->execute([$showtimeId]);
$soldSeats = [];
foreach ($soldStmt->fetchAll() as $row) {
    foreach (explode(',', $row['seats']) as $s) {
        $soldSeats[] = trim($s);
    }
}

$foods = [
    ['id' => 1, 'name' => 'Combo Solo', 'description' => '1 Bắp + 1 Nước', 'price' => 65000],
    ['id' => 2, 'name' => 'Combo Couple', 'description' => '1 Bắp lớn + 2 Nước', 'price' => 95000],
    ['id' => 3, 'name' => 'Bắp rang bơ', 'description' => 'Bắp rang bơ size L', 'price' => 50000],
    ['id' => 4, 'name' => 'Nước ngọt', 'description' => 'Pepsi / Coca / 7Up', 'price' => 30000]
];

$rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$seatsPerRow = 12;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
            <div class="flex gap-4">
                <img src="uploads/<?= htmlspecialchars($movie['poster']) ?>" onerror="this.src='https://placehold.co/80x112/0f172a/94a3b8?text=CS'" class="w-20 h-28 object-cover rounded-lg border border-slate-800 flex-shrink-0">
                <div>
                    <h1 class="text-2xl font-bold text-slate-100"><?= htmlspecialchars($movie['title']) ?></h1>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-slate-400">
                        <span><i class="fa-solid fa-calendar-days mr-1.5 text-slate-500"></i><?= htmlspecialchars($movie['date']) ?></span>
                        <span><i class="fa-regular fa-clock mr-1.5 text-slate-500"></i><?= htmlspecialchars($movie['showtime']) ?></span>
                        <?php if (!empty($movie['duration'])): ?>
                            <span><i class="fa-solid fa-hourglass-half mr-1.5 text-slate-500"></i><?= (int)$movie['duration'] ?> phút</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($movie['genre'])): ?>
                        <p class="text-xs text-slate-500 mt-2"><span class="text-slate-400 font-semibold">Thể loại:</span> <?= htmlspecialchars($movie['genre']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($movie['director'])): ?>
                        <p class="text-xs text-slate-500 mt-1"><span class="text-slate-400 font-semibold">Đạo diễn:</span> <?= htmlspecialchars($movie['director']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($movie['cast'])): ?>
                        <p class="text-xs text-slate-500 mt-1"><span class="text-slate-400 font-semibold">Diễn viên:</span> <?= htmlspecialchars($movie['cast']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 bg-amber-500/10 border border-amber-500/30 text-amber-400 px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap">
                <?= htmlspecialchars($movie['room']) ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl">
                <div class="flex items-center justify-between mb-7">
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">Chọn ghế</h2>
                        <p class="text-sm text-slate-400 mt-1">Chọn ghế bạn muốn sử dụng</p>
                    </div>
                </div>

                <div class="flex flex-col items-center mb-10">
                    <div class="w-3/4 max-w-2xl h-2 bg-slate-200 rounded-full shadow-[0_8px_25px_rgba(248,250,252,0.25)]"></div>
                    <span class="text-xs text-slate-500 mt-3 tracking-[0.3em]">MÀN HÌNH</span>
                </div>

                <div class="space-y-3 overflow-x-auto pb-2">
                    <?php foreach ($rows as $row): ?>
                        <div class="flex items-center justify-center gap-1.5 sm:gap-2 min-w-[650px]">
                            <div class="w-6 text-center text-sm font-bold text-slate-400"><?= $row ?></div>
                            <?php for ($i = 1; $i <= $seatsPerRow; $i++): ?>
                                <?php
                                $seatCode = $row . $i;
                                $isSold = in_array($seatCode, $soldSeats);
                                $isVip = in_array($row, ['E', 'F', 'G', 'H']);
                                $price = $isVip ? $vipPrice : $standardPrice;
                                ?>
                                <button
                                    type="button"
                                    class="seat w-9 h-8 sm:w-10 sm:h-9 rounded-lg text-[10px] sm:text-xs font-semibold border transition-all duration-200 <?php if ($isSold): ?>bg-rose-600 border-rose-600 text-white cursor-not-allowed opacity-80<?php elseif ($isVip): ?>bg-slate-800 border-amber-500/70 text-slate-200 hover:bg-amber-500/20 hover:border-amber-500 hover:-translate-y-0.5<?php else: ?>bg-slate-700 border-slate-600 text-slate-200 hover:bg-rose-500/20 hover:border-rose-500 hover:-translate-y-0.5<?php endif; ?>"
                                    data-seat="<?= $seatCode ?>"
                                    data-type="<?= $isVip ? 'vip' : 'standard' ?>"
                                    data-price="<?= $price ?>"
                                    <?= $isSold ? 'disabled' : '' ?>
                                >
                                    <?= $seatCode ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex flex-wrap justify-center gap-5 mt-8 pt-5 border-t border-slate-800">
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span class="w-5 h-5 rounded-md bg-slate-700 border border-slate-600"></span>
                        Ghế trống
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span class="w-5 h-5 rounded-md bg-amber-500"></span>
                        Đang chọn
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span class="w-5 h-5 rounded-md bg-rose-600"></span>
                        Đã bán
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span class="w-5 h-5 rounded-md bg-slate-800 border border-amber-500/70"></span>
                        Ghế VIP
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-100">🍿 Bắp & Nước</h2>
                    <p class="text-sm text-slate-400 mt-1">Thêm bắp nước để thưởng thức trong suất chiếu</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($foods as $food): ?>
                        <div class="food-item bg-slate-950 border border-slate-800 rounded-xl p-4 transition-all duration-200 hover:border-rose-500/50">
                            <div class="flex justify-between gap-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-100"><?= htmlspecialchars($food['name']) ?></h3>
                                    <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($food['description']) ?></p>
                                    <p class="text-amber-400 font-semibold text-sm mt-2"><?= number_format($food['price'], 0, ',', '.') ?> đ</p>
                                </div>
                                <div class="flex items-center gap-2 self-center">
                                    <button type="button" class="food-minus w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:bg-rose-600 hover:border-rose-600 transition" data-id="<?= $food['id'] ?>">−</button>
                                    <span id="quantity-<?= $food['id'] ?>" class="w-6 text-center text-slate-100 font-semibold">0</span>
                                    <button type="button" class="food-plus w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:bg-rose-600 hover:border-rose-600 transition" data-id="<?= $food['id'] ?>">+</button>
                                </div>
                            </div>
                            <input type="hidden" id="food-price-<?= $food['id'] ?>" value="<?= $food['price'] ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl xl:sticky xl:top-24">
                <h2 class="text-xl font-bold text-slate-100 pb-4 border-b border-slate-800">Thông tin đặt vé</h2>

                <div class="py-5 border-b border-slate-800">
                    <div class="flex justify-between gap-4 mb-3">
                        <span class="text-sm text-slate-400">Số ghế</span>
                        <span id="seat-count" class="font-semibold text-slate-100">0</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-sm text-slate-400">Ghế đã chọn</span>
                        <span id="selected-seats" class="text-sm font-semibold text-amber-400 text-right max-w-[200px]">Chưa chọn</span>
                    </div>
                </div>

                <div class="py-5 border-b border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Tiền ghế</span>
                        <span id="seat-total" class="font-semibold text-slate-100">0 đ</span>
                    </div>
                </div>

                <div class="py-5 border-b border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Bắp & nước</span>
                        <span id="food-total" class="font-semibold text-slate-100">0 đ</span>
                    </div>
                </div>

                <div class="py-5">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-400">Tổng cộng</span>
                        <span id="grand-total" class="text-2xl font-bold text-rose-500">0 đ</span>
                    </div>
                </div>

                <form method="POST" action="checkout.php" id="booking-form">
                    <input type="hidden" name="showtime_id" value="<?= $showtimeId ?>">
                    <input type="hidden" name="seats" id="seats-input" value="">
                    <input type="hidden" name="combos" id="combos-input" value="">
                    <input type="hidden" name="total_price" id="total-input" value="0">
                    <button type="submit" id="continue-btn" disabled class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:bg-slate-700 disabled:text-slate-500 disabled:cursor-not-allowed text-white font-semibold shadow-lg shadow-rose-600/20 transition-all">
                        Tiếp tục thanh toán
                    </button>
                </form>

                <p class="text-xs text-slate-500 text-center mt-4">Vui lòng chọn ít nhất một ghế để tiếp tục.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Dữ liệu món ăn lấy từ PHP để JS biết tên/giá khi build "combos" gửi cho checkout.php
const foodsData = <?= json_encode($foods) ?>;

let selectedSeats = {};
let foodQuantities = {};

function formatMoney(number) {
    return new Intl.NumberFormat('vi-VN').format(number) + ' đ';
}

document.querySelectorAll('.seat:not(:disabled)').forEach(function(seat) {
    seat.addEventListener('click', function() {
        const seatCode = this.dataset.seat;
        const seatType = this.dataset.type;
        const price = parseInt(this.dataset.price);

        if (this.classList.contains('selected')) {
            this.classList.remove('selected', 'bg-amber-500', 'border-amber-500', 'text-slate-950', 'shadow-lg', 'shadow-amber-500/20');
            if (seatType === 'vip') {
                this.classList.add('bg-slate-800', 'border-amber-500/70', 'text-slate-200');
            } else {
                this.classList.add('bg-slate-700', 'border-slate-600', 'text-slate-200');
            }
            delete selectedSeats[seatCode];
        } else {
            this.classList.remove('bg-slate-700', 'bg-slate-800', 'border-slate-600', 'border-amber-500/70', 'text-slate-200');
            this.classList.add('selected', 'bg-amber-500', 'border-amber-500', 'text-slate-950', 'shadow-lg', 'shadow-amber-500/20');
            selectedSeats[seatCode] = { type: seatType, price: price };
        }
        updateSummary();
    });
});

document.querySelectorAll('.food-plus').forEach(function(button) {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        if (!foodQuantities[id]) { foodQuantities[id] = 0; }
        foodQuantities[id]++;
        updateFoodQuantity(id);
        updateSummary();
    });
});

document.querySelectorAll('.food-minus').forEach(function(button) {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        if (!foodQuantities[id]) { foodQuantities[id] = 0; }
        if (foodQuantities[id] > 0) { foodQuantities[id]--; }
        updateFoodQuantity(id);
        updateSummary();
    });
});

function updateFoodQuantity(id) {
    const element = document.getElementById('quantity-' + id);
    element.textContent = foodQuantities[id] || 0;
}

function calculateSeatTotal() {
    let total = 0;
    Object.keys(selectedSeats).forEach(function(seatCode) {
        total += selectedSeats[seatCode].price;
    });
    return total;
}

function calculateFoodTotal() {
    let total = 0;
    Object.keys(foodQuantities).forEach(function(id) {
        const quantity = foodQuantities[id];
        const priceElement = document.getElementById('food-price-' + id);
        if (!priceElement) { return; }
        const price = parseInt(priceElement.value);
        total += quantity * price;
    });
    return total;
}

function updateSummary() {
    const seatCodes = Object.keys(selectedSeats);
    const seatCount = seatCodes.length;
    const seatTotal = calculateSeatTotal();
    const foodTotal = calculateFoodTotal();
    const grandTotal = seatTotal + foodTotal;

    document.getElementById('seat-count').textContent = seatCount;
    document.getElementById('selected-seats').textContent = seatCount > 0 ? seatCodes.join(', ') : 'Chưa chọn';
    document.getElementById('seat-total').textContent = formatMoney(seatTotal);
    document.getElementById('food-total').textContent = formatMoney(foodTotal);
    document.getElementById('grand-total').textContent = formatMoney(grandTotal);

    // checkout.php cần "seats" là chuỗi các mã ghế cách nhau bằng dấu phẩy
    document.getElementById('seats-input').value = seatCodes.join(',');

    // checkout.php cần "combos" là JSON mảng các object {name, price, qty}
    const combos = Object.keys(foodQuantities)
        .filter(function(id) { return foodQuantities[id] > 0; })
        .map(function(id) {
            const food = foodsData.find(function(f) { return f.id == id; });
            return { name: food.name, price: food.price, qty: foodQuantities[id] };
        });
    document.getElementById('combos-input').value = JSON.stringify(combos);

    document.getElementById('total-input').value = grandTotal;

    const continueButton = document.getElementById('continue-btn');
    if (seatCount > 0) {
        continueButton.disabled = false;
    } else {
        continueButton.disabled = true;
    }
}

updateSummary();
</script>

<?php include_once 'footer.php'; ?>
