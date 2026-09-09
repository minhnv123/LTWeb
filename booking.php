<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$showtime_id = intval($_GET['showtime_id'] ?? 0);
if ($showtime_id <= 0) {
    header("Location: index.php");
    exit();
}

// Lấy đầy đủ thông tin Suất chiếu + Phim + Rạp
$stmt = $pdo->prepare("
    SELECT s.*, 
           m.title AS movie_title, m.poster, m.duration,
           c.name AS cinema_name, c.address AS cinema_address
    FROM showtimes s 
    JOIN movies m ON s.movie_id = m.id 
    LEFT JOIN cinemas c ON s.cinema_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$showtime_id]);
$showtime = $stmt->fetch();

if (!$showtime) {
    header("Location: index.php");
    exit();
}

// Lấy danh sách ghế đã đặt từ booking_details hoặc bookings
$bookedSeats = [];
try {
    $stmtBooked = $pdo->prepare("
        SELECT bd.seat_number 
        FROM booking_details bd 
        JOIN bookings b ON b.id = bd.booking_id 
        WHERE b.showtime_id = ? AND b.status IN ('pending','paid','used')
    ");
    $stmtBooked->execute([$showtime_id]);
    $bookedSeats = $stmtBooked->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $bookedSeats = [];
}

$basePrice = $showtime['price'] ?? 75000;
$vipPrice = $basePrice + 20000;

// Danh sách Combo Bắp Nước
$combosList = [
    ['id' => 'cb1', 'name' => 'Combo Solo (1 Bắp + 1 Nước)', 'price' => 69000],
    ['id' => 'cb2', 'name' => 'Combo Couple (1 Bắp lớn + 2 Nước)', 'price' => 99000],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn ghế - <?= htmlspecialchars($showtime['movie_title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen pb-12">

    <main class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">
            <i class="fa-solid fa-couch text-yellow-500 mr-2"></i>Chọn Ghế & Dịch Vụ
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- CỘT TRÁI: SƠ ĐỒ GHẾ + COMBO -->
            <div class="lg:col-span-2 space-y-8">
                <!-- 1. SƠ ĐỒ CHỌN GHẾ DẠNG LƯỚI -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <div class="w-full bg-gradient-to-r from-transparent via-yellow-500 to-transparent h-1 rounded-full mb-2"></div>
                    <p class="text-center text-xs uppercase tracking-widest font-bold text-slate-400 mb-8">Màn Hình Chiếu</p>

                    <div class="grid grid-cols-8 gap-2 max-w-md mx-auto mb-8">
                        <?php 
                        $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
                        foreach ($rows as $row) {
                            $isVipRow = in_array($row, ['D', 'E', 'F']);
                            $price = $isVipRow ? $vipPrice : $basePrice;
                            
                            for ($i = 1; $i <= 8; $i++) {
                                $seatCode = $row . $i;
                                $isBooked = in_array($seatCode, $bookedSeats);
                                $seatType = $isVipRow ? 'VIP' : 'Thường';

                                // CSS phân biệt 4 loại ghế
                                if ($isBooked) {
                                    $seatClass = 'bg-red-900/80 text-red-300 border border-red-700/50 cursor-not-allowed opacity-80';
                                } elseif ($isVipRow) {
                                    $seatClass = 'bg-purple-950 hover:bg-purple-800 text-purple-200 border border-purple-500/60';
                                } else {
                                    $seatClass = 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700';
                                }

                                $disabledAttr = $isBooked ? 'disabled' : '';

                                echo '<button type="button" ';
                                echo 'data-seat="' . $seatCode . '" ';
                                echo 'data-price="' . $price . '" ';
                                echo 'data-type="' . $seatType . '" ';
                                echo $disabledAttr . ' ';
                                echo 'class="seat-btn aspect-square rounded-lg text-xs font-bold transition-all flex items-center justify-center ' . $seatClass . '">';
                                echo $seatCode;
                                echo '</button>';
                            }
                        } 
                        ?>
                    </div>

                    <!-- CHÚ THÍCH 4 LOẠI GHẾ -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-slate-300 border-t border-slate-800 pt-4">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-slate-800 border border-slate-700 inline-block"></span> Ghế Thường
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-purple-950 border border-purple-500/60 inline-block"></span> Ghế VIP
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-yellow-500 inline-block"></span> Đang chọn
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-red-900/80 border border-red-700 inline-block"></span> Đã đặt
                        </div>
                    </div>
                </div>

                <!-- 2. CHỌN BẮP NƯỚC / COMBO -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-800 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-burger text-yellow-500"></i> Chọn Bắp Nước / Combo
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($combosList as $cb): ?>
                            <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-white text-sm"><?= htmlspecialchars($cb['name']) ?></p>
                                    <p class="text-yellow-400 text-xs font-mono mt-1"><?= number_format($cb['price'], 0, ',', '.') ?> đ</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="updateCombo('<?= $cb['id'] ?>', '<?= htmlspecialchars($cb['name']) ?>', <?= $cb['price'] ?>, -1)" class="w-7 h-7 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-bold transition-all">-</button>
                                    <span id="qty-<?= $cb['id'] ?>" class="text-sm font-bold text-white min-w-[16px] text-center">0</span>
                                    <button type="button" onclick="updateCombo('<?= $cb['id'] ?>', '<?= htmlspecialchars($cb['name']) ?>', <?= $cb['price'] ?>, 1)" class="w-7 h-7 bg-yellow-500 hover:bg-yellow-600 text-slate-900 rounded-lg font-bold transition-all">+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: HIỂN THỊ THÔNG TIN CHI TIẾT ĐỐI CHIẾU (KHÔNG CÓ PHÒNG CHIẾU) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl h-fit sticky top-6 space-y-6">
                
                <!-- 1. THÔNG TIN PHIM & RẠP CHIẾU -->
                <div class="border-b border-slate-800 pb-5 space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-film text-yellow-500"></i> Thông Tin Đặt Vé
                    </h3>
                    
                    <div class="flex gap-4">
                        <img src="<?= htmlspecialchars($showtime['poster'] ?? 'https://via.placeholder.com/100x150') ?>" 
                             alt="<?= htmlspecialchars($showtime['movie_title']) ?>" 
                             class="w-20 h-28 object-cover rounded-lg border border-slate-700 shadow-md">
                        <div class="space-y-1.5 flex-1">
                            <h4 class="font-bold text-white text-base leading-snug"><?= htmlspecialchars($showtime['movie_title']) ?></h4>
                            <p class="text-xs text-slate-400">
                                <i class="fa-regular fa-clock text-slate-500 mr-1"></i>Thời lượng: <?= $showtime['duration'] ?? 120 ?> phút
                            </p>
                        </div>
                    </div>

                    <!-- KHỐI ĐỐI CHIẾU RẠP & GIỜ CHIẾU -->
                    <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800/80 space-y-2.5 text-xs">
                        <div class="flex items-start gap-2 text-slate-300">
                            <i class="fa-solid fa-location-dot text-red-500 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-white block"><?= htmlspecialchars($showtime['cinema_name'] ?? 'Rạp Lotte / CGV Cinema') ?></span>
                                <?php if (!empty($showtime['cinema_address'])): ?>
                                    <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($showtime['cinema_address']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-yellow-400 font-semibold border-t border-slate-800/60 pt-2.5">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>
                                Suất chiếu: <?= isset($showtime['start_time']) ? date('H:i - d/m/Y', strtotime($showtime['start_time'])) : (isset($showtime['showtime']) ? date('H:i - d/m/Y', strtotime($showtime['showtime'])) : '19:30 - Hôm nay') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. CHI TIẾT TÍNH TIỀN TỰ ĐỘNG -->
                <div>
                    <h3 class="text-base font-bold text-white mb-3">Tóm Tắt Chi Phí</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-400">
                            <span>Ghế chọn:</span>
                            <span id="selected-seats-text" class="font-bold text-yellow-400">Chưa chọn</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Tiền ghế:</span>
                            <span id="seat-total-text" class="font-mono text-slate-200">0 đ</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Bắp nước:</span>
                            <span id="combo-total-text" class="font-mono text-slate-200">0 đ</span>
                        </div>
                        <div class="border-t border-slate-800 pt-3 flex justify-between text-base font-bold text-white">
                            <span>Tổng tiền:</span>
                            <span id="total-price-text" class="text-yellow-400 font-mono text-xl">0 đ</span>
                        </div>
                    </div>
                </div>

                <!-- 3. FORM GỬI THÔNG TIN VỀ CHECKOUT.PHP -->
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="showtime_id" value="<?= $showtime['id'] ?>">
                    <input type="hidden" name="seats" id="seats-input" value="">
                    <input type="hidden" name="seats_json" id="seats-json-input" value="">
                    <input type="hidden" name="combos_json" id="combos-json-input" value="[]">
                    <input type="hidden" name="total_price" id="total-price-input" value="0">
                    
                    <button type="submit" id="btn-submit" disabled 
                            class="w-full bg-slate-800 text-slate-500 font-bold py-3.5 rounded-xl transition-all text-sm cursor-not-allowed">
                        Thanh Toán
                    </button>
                </form>
            </div>

        </div>
    </main>

    <script>
        const selectedSeats = [];
        const selectedCombos = {};
        
        const seatBtns = document.querySelectorAll('.seat-btn:not([disabled])');
        const selectedSeatsText = document.getElementById('selected-seats-text');
        const seatTotalText = document.getElementById('seat-total-text');
        const comboTotalText = document.getElementById('combo-total-text');
        const totalPriceText = document.getElementById('total-price-text');
        
        const seatsInput = document.getElementById('seats-input');
        const seatsJsonInput = document.getElementById('seats-json-input');
        const combosJsonInput = document.getElementById('combos-json-input');
        const totalPriceInput = document.getElementById('total-price-input');
        const btnSubmit = document.getElementById('btn-submit');

        // Xử lý sự kiện click chọn / bỏ chọn ghế
        seatBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const seat = btn.getAttribute('data-seat');
                const price = parseFloat(btn.getAttribute('data-price'));
                const type = btn.getAttribute('data-type');
                
                const index = selectedSeats.findIndex(s => s.code === seat);

                if (index > -1) {
                    selectedSeats.splice(index, 1);
                    // Đưa ghế về màu ban đầu
                    if (type === 'VIP') {
                        btn.className = "seat-btn aspect-square rounded-lg text-xs font-bold transition-all flex items-center justify-center bg-purple-950 hover:bg-purple-800 text-purple-200 border border-purple-500/60";
                    } else {
                        btn.className = "seat-btn aspect-square rounded-lg text-xs font-bold transition-all flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700";
                    }
                } else {
                    selectedSeats.push({ code: seat, price: price, type: type });
                    // Đổi sang trạng thái màu vàng đang chọn
                    btn.className = "seat-btn aspect-square rounded-lg text-xs font-bold transition-all flex items-center justify-center bg-yellow-500 text-slate-950 shadow-lg shadow-yellow-500/30 scale-105";
                }
                calculateTotal();
            });
        });

        // Tăng / giảm số lượng combo
        function updateCombo(id, name, price, delta) {
            if (!selectedCombos[id]) {
                selectedCombos[id] = { id: id, name: name, price: price, qty: 0 };
            }
            selectedCombos[id].qty += delta;
            if (selectedCombos[id].qty < 0) selectedCombos[id].qty = 0;
            
            document.getElementById(`qty-${id}`).textContent = selectedCombos[id].qty;
            calculateTotal();
        }

        // Cập nhật tổng tiền và trạng thái nút thanh toán
        function calculateTotal() {
            const seatTotal = selectedSeats.reduce((sum, s) => sum + s.price, 0);
            
            let comboTotal = 0;
            const comboArray = [];
            for (let key in selectedCombos) {
                if (selectedCombos[key].qty > 0) {
                    comboTotal += selectedCombos[key].price * selectedCombos[key].qty;
                    comboArray.push(selectedCombos[key]);
                }
            }

            const grandTotal = seatTotal + comboTotal;
            const seatCodes = selectedSeats.map(s => s.code);

            selectedSeatsText.textContent = seatCodes.length > 0 ? seatCodes.join(', ') : 'Chưa chọn';
            seatTotalText.textContent = seatTotal.toLocaleString('vi-VN') + ' đ';
            comboTotalText.textContent = comboTotal.toLocaleString('vi-VN') + ' đ';
            totalPriceText.textContent = grandTotal.toLocaleString('vi-VN') + ' đ';

            seatsInput.value = seatCodes.join(',');
            seatsJsonInput.value = JSON.stringify(selectedSeats);
            combosJsonInput.value = JSON.stringify(comboArray);
            totalPriceInput.value = grandTotal;

            if (selectedSeats.length > 0) {
                btnSubmit.disabled = false;
                btnSubmit.className = "w-full bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold py-3.5 rounded-xl transition-all text-sm cursor-pointer shadow-lg shadow-yellow-500/20";
            } else {
                btnSubmit.disabled = true;
                btnSubmit.className = "w-full bg-slate-800 text-slate-500 font-bold py-3.5 rounded-xl transition-all text-sm cursor-not-allowed";
            }
        }
    </script>
</body>
</html>