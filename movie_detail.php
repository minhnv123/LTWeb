<?php
require_once 'config/database.php';
include_once 'header.php';

$movieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ============================================================
// 1. LẤY THÔNG TIN PHIM
// ============================================================
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    echo '<div class="max-w-3xl mx-auto px-4 py-24 text-center">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-4xl mb-4"></i>
            <h1 class="text-xl font-bold text-slate-100">Không tìm thấy phim</h1>
            <p class="text-slate-400 mt-2">Phim bạn tìm không tồn tại hoặc đã bị gỡ bỏ.</p>
            <a href="index.php" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-all">Về Trang Chủ</a>
          </div>';
    include_once 'footer.php';
    exit;
}

// ============================================================
// 2. LẤY DANH SÁCH SUẤT CHIẾU (kèm tên rạp), nhóm theo Ngày
// ============================================================
$stmt = $pdo->prepare("
    SELECT s.id AS showtime_id, s.show_date, s.show_time, s.price,
           c.id AS cinema_id, c.name AS cinema_name, c.address AS cinema_address
    FROM showtimes s
    JOIN cinemas c ON c.id = s.cinema_id
    WHERE s.movie_id = ? AND s.show_date >= CURDATE()
    ORDER BY s.show_date ASC, s.show_time ASC
");
$stmt->execute([$movieId]);
$showtimes = $stmt->fetchAll();

// Nhóm suất chiếu: [ngày][rạp][] = suất chiếu
$grouped = [];
foreach ($showtimes as $s) {
    $grouped[$s['show_date']][$s['cinema_name']]['address'] = $s['cinema_address'];
    $grouped[$s['show_date']][$s['cinema_name']]['slots'][] = $s;
}
$dateKeys = array_keys($grouped);

// ============================================================
// 3. CHUYỂN LINK YOUTUBE THƯỜNG -> LINK NHÚNG (EMBED)
// ============================================================
function toYoutubeEmbed($url) {
    if (empty($url)) return null;
    $videoId = null;

    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url, $m)) {
        $videoId = $m[1];
    }
    return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
}
$embedUrl = toYoutubeEmbed($movie['trailer_url'] ?? '');

$statusLabel = $movie['status'] === 'now_showing' ? 'Đang Chiếu' : 'Sắp Chiếu';
$statusColor = $movie['status'] === 'now_showing' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border-amber-500/30';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- ============================================================ -->
    <!-- 1. THÔNG TIN PHIM -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-8 mb-12">
        <!-- Poster -->
        <div class="rounded-2xl overflow-hidden border border-slate-800 shadow-xl shadow-black/30 h-fit">
            <img src="uploads/<?php echo htmlspecialchars($movie['poster']); ?>"
                 onerror="this.src='https://placehold.co/500x750/0f172a/f8fafc?text=<?php echo urlencode($movie['title']); ?>'"
                 alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-full object-cover">
        </div>

        <!-- Thông tin -->
        <div>
            <span class="inline-block text-xs font-bold px-3 py-1 rounded-full border mb-3 <?php echo $statusColor; ?>">
                <?php echo $statusLabel; ?>
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-4"><?php echo htmlspecialchars($movie['title']); ?></h1>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 text-sm">
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold mb-1">Đạo diễn</p>
                    <p class="text-slate-200 font-medium"><?php echo htmlspecialchars($movie['director'] ?: 'Đang cập nhật'); ?></p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold mb-1">Thời lượng</p>
                    <p class="text-slate-200 font-medium"><?php echo (int) $movie['duration']; ?> phút</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold mb-1">Thể loại</p>
                    <p class="text-slate-200 font-medium"><?php echo htmlspecialchars($movie['genre']); ?></p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold mb-1">Khởi chiếu</p>
                    <p class="text-slate-200 font-medium">
                        <?php echo $movie['release_date'] ? date('d/m/Y', strtotime($movie['release_date'])) : 'Đang cập nhật'; ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($movie['cast'])): ?>
                <div class="mb-6 text-sm">
                    <p class="text-slate-500 text-xs uppercase font-semibold mb-1">Diễn viên</p>
                    <p class="text-slate-300"><?php echo htmlspecialchars($movie['cast']); ?></p>
                </div>
            <?php endif; ?>

            <div class="text-sm">
                <p class="text-slate-500 text-xs uppercase font-semibold mb-1.5">Nội dung phim</p>
                <p class="text-slate-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($movie['description'] ?: 'Đang cập nhật nội dung phim.')); ?></p>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 2. TRAILER YOUTUBE -->
    <!-- ============================================================ -->
    <?php if ($embedUrl): ?>
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-5">
            <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
            <h2 class="text-xl font-bold text-white">Trailer</h2>
        </div>
        <div class="relative w-full aspect-video rounded-2xl overflow-hidden border border-slate-800">
            <iframe src="<?php echo htmlspecialchars($embedUrl); ?>"
                    class="absolute inset-0 w-full h-full"
                    title="Trailer <?php echo htmlspecialchars($movie['title']); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- 3. CHỌN SUẤT CHIẾU -->
    <!-- ============================================================ -->
    <div>
        <div class="flex items-center gap-2 mb-5">
            <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
            <h2 class="text-xl font-bold text-white">Chọn Suất Chiếu</h2>
        </div>

        <?php if (empty($dateKeys)): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
                <i class="fa-solid fa-calendar-xmark text-3xl mb-3 text-slate-600"></i>
                <p>Hiện chưa có lịch chiếu cho phim này. Vui lòng quay lại sau.</p>
            </div>
        <?php else: ?>
            <!-- Tabs chọn ngày -->
            <div class="flex flex-wrap gap-2 mb-6" id="dateTabs">
                <?php foreach ($dateKeys as $i => $date): ?>
                    <button type="button" data-target="date-panel-<?php echo $i; ?>"
                            class="date-tab-btn px-4 py-2 rounded-xl text-sm font-semibold border transition-all <?php echo $i === 0 ? 'bg-rose-600 border-rose-600 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-rose-500'; ?>">
                        <?php echo date('d/m', strtotime($date)); ?>
                        <span class="block text-[10px] font-normal opacity-80"><?php echo date('D', strtotime($date)); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Nội dung theo từng ngày -->
            <?php foreach ($dateKeys as $i => $date): ?>
                <div id="date-panel-<?php echo $i; ?>" class="date-panel <?php echo $i === 0 ? '' : 'hidden'; ?> space-y-5">
                    <?php foreach ($grouped[$date] as $cinemaName => $data): ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                            <h3 class="font-bold text-white flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-location-dot text-rose-500"></i><?php echo htmlspecialchars($cinemaName); ?>
                            </h3>
                            <p class="text-xs text-slate-500 mb-4 ml-6"><?php echo htmlspecialchars($data['address']); ?></p>
                            <div class="flex flex-wrap gap-2.5 ml-6">
                                <?php foreach ($data['slots'] as $slot): ?>
                                    <a href="booking.php?showtime_id=<?php echo $slot['showtime_id']; ?>"
                                       class="flex flex-col items-center px-4 py-2 rounded-xl border border-slate-700 bg-slate-950 hover:bg-rose-600 hover:border-rose-600 text-slate-200 hover:text-white transition-all text-sm font-semibold">
                                        <?php echo date('H:i', strtotime($slot['show_time'])); ?>
                                        <span class="text-[10px] font-normal opacity-80">
                                            <?php echo number_format($slot['price'], 0, ',', '.'); ?>đ
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.date-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.date-tab-btn').forEach(b => {
            b.classList.remove('bg-rose-600', 'border-rose-600', 'text-white');
            b.classList.add('bg-slate-900', 'border-slate-800', 'text-slate-300');
        });
        this.classList.add('bg-rose-600', 'border-rose-600', 'text-white');
        this.classList.remove('bg-slate-900', 'border-slate-800', 'text-slate-300');

        document.querySelectorAll('.date-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById(this.dataset.target).classList.remove('hidden');
    });
});
</script>

<?php include_once 'footer.php'; ?>
