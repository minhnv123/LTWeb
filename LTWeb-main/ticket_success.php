<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// Kiểm tra quyền đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

function money($n) {
    return number_format($n, 0, ',', '.') . 'đ';
}

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Truy vấn thông tin chi tiết vé
$stmt = $pdo->prepare("
    SELECT b.*, s.show_date, s.show_time, s.price,
           m.title, m.poster, m.duration,
           c.name AS cinema_name, c.address AS cinema_address,
           u.email
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    JOIN cinemas c ON c.id = s.cinema_id
    JOIN users u ON u.id = b.user_id
    WHERE b.booking_code = ?
");
$stmt->execute([$code]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra tồn tại vé và phân quyền
$currentUserId = $_SESSION['user']['id'] ?? 0;
$currentUserRole = $_SESSION['user']['role'] ?? '';

include_once 'header.php';

if (!$ticket || ((int)$ticket['user_id'] !== (int)$currentUserId && $currentUserRole !== 'admin')) {
    echo '<div class="max-w-2xl mx-auto px-4 py-24 text-center">
            <i class="fa-solid fa-ticket-simple text-slate-700 text-5xl mb-4"></i>
            <h1 class="text-xl font-bold text-slate-100">Không tìm thấy vé xem phim</h1>
            <p class="text-slate-400 text-sm mt-2">Mã vé không tồn tại hoặc bạn không có quyền truy cập vé này.</p>
            <a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>
          </div>';
    include_once 'footer.php';
    exit;
}

$seats = array_filter(array_map('trim', explode(',', $ticket['seats'] ?? '')));
$combos = json_decode($ticket['combos'] ?? '[]', true) ?: [];
?>

<!-- Thư viện QRCode.js với CDN ổn định -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <!-- Thẻ Vé Điện Tử -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl shadow-black/40">
        
        <!-- Đầu vé: Poster & Tên Phim -->
        <div class="p-6 flex items-center gap-4 border-b border-dashed border-slate-700/80 bg-slate-950/40">
            <img src="uploads/<?php echo htmlspecialchars($ticket['poster'] ?? ''); ?>" 
                 onerror="this.src='https://placehold.co/100x150/0f172a/94a3b8?text=NO+IMAGE'" 
                 alt="<?php echo htmlspecialchars($ticket['title'] ?? ''); ?>"
                 class="w-16 h-24 object-cover rounded-xl border border-slate-800 shadow-md">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 text-[11px] font-mono font-bold border border-emerald-500/30 mb-1.5">
                    <i class="fa-solid fa-circle-check mr-1"></i>ĐẶT VÉ THÀNH CÔNG
                </span>
                <h1 class="font-bold text-lg sm:text-xl text-slate-100 leading-tight"><?php echo htmlspecialchars($ticket['title']); ?></h1>
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                    <i class="fa-regular fa-clock text-slate-500"></i> <?php echo (int)($ticket['duration'] ?? 0); ?> phút
                    <span class="mx-1">•</span> Mã vé: <span class="font-mono text-amber-400 font-bold"><?php echo htmlspecialchars($ticket['booking_code']); ?></span>
                </p>
            </div>
        </div>

        <!-- Thân vé: Thông tin rạp, suất chiếu, ghế -->
        <div class="p-6 grid grid-cols-2 gap-y-5 gap-x-4 text-sm border-b border-dashed border-slate-700/80">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Rạp Chiếu</p>
                <p class="text-slate-200 font-semibold"><?php echo htmlspecialchars($ticket['cinema_name']); ?></p>
                <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1"><?php echo htmlspecialchars($ticket['cinema_address'] ?? ''); ?></p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Suất Chiếu</p>
                <p class="text-slate-200 font-semibold"><?php echo date('H:i', strtotime($ticket['show_time'])); ?> — <?php echo date('d/m/Y', strtotime($ticket['show_date'])); ?></p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Ghế Đặt</p>
                <p class="text-amber-400 font-bold font-mono text-base"><?php echo htmlspecialchars(implode(', ', $seats)); ?></p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Tổng Tiền</p>
                <p class="text-rose-500 font-bold text-base"><?php echo money($ticket['total_price']); ?></p>
            </div>

            <!-- Bắp nước nếu có -->
            <?php if (!empty($combos)): ?>
            <div class="col-span-2 border-t border-slate-800/80 pt-3 mt-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Bắp Nước Đã Đặt</p>
                <div class="space-y-1">
                    <?php foreach ($combos as $c): ?>
                        <div class="flex justify-between text-xs text-slate-300">
                            <span>+ <?php echo htmlspecialchars($c['name']); ?> (x<?php echo $c['qty']; ?>)</span>
                            <span class="font-medium text-slate-400"><?php echo money($c['total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Chân vé: Mã QR -->
        <div class="p-8 flex flex-col items-center justify-center gap-3 bg-slate-950/20">
            <div class="p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                <div id="qrcode"></div>
            </div>
            <p class="text-xs text-slate-400 text-center max-w-xs mt-1">
                Xuất trình mã QR này tại quầy rạp để đổi vé cứng hoặc quét mã vào phòng chiếu.
            </p>
            
            <div class="flex items-center gap-3 mt-4 print:hidden">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-print"></i> In vé
                </button>
                <a href="index.php" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var qrContainer = document.getElementById("qrcode");
        if (qrContainer) {
            new QRCode(qrContainer, {
                text: <?php echo json_encode($ticket['booking_code']); ?>,
                width: 150,
                height: 150,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    });
</script>

<?php include_once 'footer.php'; ?>