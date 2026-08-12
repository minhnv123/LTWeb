<?php
require_once 'dp.php';

// Kiểm tra đăng nhập & redirect PHẢI chạy trước khi include header.php (vì header.php đã in HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include_once 'header.php';

function money($n) {
    return number_format($n, 0, ',', '.') . 'đ';
}

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

$stmt = $pdo->prepare("
    SELECT b.*, s.show_date, s.show_time, s.price,
           m.title, m.poster, m.duration,
           c.name AS cinema_name, c.address AS cinema_address,
           u.full_name, u.email
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    JOIN cinemas c ON c.id = s.cinema_id
    JOIN users u ON u.id = b.user_id
    WHERE b.booking_code = ?
");
$stmt->execute([$code]);
$ticket = $stmt->fetch();

// Chỉ cho phép chủ vé (hoặc admin) xem lại vé của mình
if (!$ticket || ((int)$ticket['user_id'] !== (int)$_SESSION['user']['id'] && ($_SESSION['user']['role'] ?? '') !== 'admin')) {
    echo '<div class="max-w-2xl mx-auto px-4 py-24 text-center">';
    echo '<i class="fa-solid fa-ticket-simple text-slate-700 text-4xl mb-4"></i>';
    echo '<h1 class="text-xl font-bold text-slate-100">Không tìm thấy vé</h1>';
    echo '<p class="text-slate-400 mt-2">Mã vé không hợp lệ hoặc bạn không có quyền xem vé này.</p>';
    echo '<a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>';
    echo '</div>';
    include_once 'footer.php';
    exit;
}

$seats = array_filter(array_map('trim', explode(',', $ticket['seats'])));
$combos = json_decode($ticket['combos'] ?? '[]', true) ?: [];
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-check text-emerald-400 text-2xl"></i>
        </div>
        <h1 class="text-xl font-extrabold text-slate-100">Đặt Vé Thành Công!</h1>
        <p class="text-sm text-slate-400 mt-1">Vé điện tử của bạn đã sẵn sàng. Vui lòng xuất trình mã QR tại rạp.</p>
    </div>

    <!-- VÉ ĐIỆN TỬ -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="p-6 flex items-center gap-4 border-b border-dashed border-slate-700">
            <img src="uploads/<?php echo htmlspecialchars($ticket['poster']); ?>" onerror="this.src='https://placehold.co/64x88/0f172a/94a3b8?text=CS'" class="w-16 h-24 object-cover rounded-lg border border-slate-800">
            <div>
                <p class="font-bold text-lg text-slate-100"><?php echo htmlspecialchars($ticket['title']); ?></p>
                <p class="text-xs text-slate-400 mt-1"><?php echo (int)$ticket['duration']; ?> phút</p>
                <span class="inline-block mt-2 px-2.5 py-1 rounded-md bg-rose-500/15 text-rose-400 text-[11px] font-bold border border-rose-500/30 tracking-wide">
                    <?php echo htmlspecialchars($ticket['booking_code']); ?>
                </span>
            </div>
        </div>

        <div class="p-6 grid grid-cols-2 gap-y-4 gap-x-4 text-sm border-b border-dashed border-slate-700">
            <div>
                <p class="text-[11px] uppercase text-slate-500">Rạp Chiếu</p>
                <p class="text-slate-200 font-semibold"><?php echo htmlspecialchars($ticket['cinema_name']); ?></p>
                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($ticket['cinema_address']); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase text-slate-500">Suất Chiếu</p>
                <p class="text-slate-200 font-semibold"><?php echo date('d/m/Y', strtotime($ticket['show_date'])); ?> — <?php echo substr($ticket['show_time'], 0, 5); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase text-slate-500">Ghế</p>
                <p class="text-amber-400 font-bold"><?php echo htmlspecialchars(implode(', ', $seats)); ?></p>
            </div>
            <div>
                <p class="text-[11px] uppercase text-slate-500">Khách Hàng</p>
                <p class="text-slate-200 font-semibold"><?php echo htmlspecialchars($ticket['full_name']); ?></p>
            </div>
            <?php if (!empty($combos)): ?>
            <div class="col-span-2">
                <p class="text-[11px] uppercase text-slate-500 mb-1">Bắp Nước</p>
                <?php foreach ($combos as $c): ?>
                    <p class="text-slate-300 text-xs"><?php echo htmlspecialchars($c['name']); ?> × <?php echo (int)$c['qty']; ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="p-6 flex flex-col items-center gap-3">
            <div id="qrcode" class="p-3 bg-white rounded-xl"></div>
            <p class="text-xs text-slate-500">Mã vé: <span class="text-slate-300 font-mono"><?php echo htmlspecialchars($ticket['booking_code']); ?></span></p>
            <p class="text-lg font-extrabold text-rose-500 mt-1"><?php echo money($ticket['total_price']); ?></p>
        </div>
    </div>

    <div class="flex gap-3 mt-6">
        <button onclick="window.print()" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition-all">
            <i class="fa-solid fa-print mr-1.5"></i>In Vé
        </button>
        <a href="index.php" class="flex-1 text-center py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold transition-all shadow-lg shadow-rose-600/20">
            <i class="fa-solid fa-house mr-1.5"></i>Về Trang Chủ
        </a>
    </div>
</div>

<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?php echo htmlspecialchars($ticket['booking_code']); ?>",
        width: 160,
        height: 160,
        colorDark: "#020617",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
    });
</script>

<?php include_once 'footer.php'; ?>
