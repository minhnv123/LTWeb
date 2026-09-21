<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php'; 

// 1. LẤY THAM SỐ LỌC THEO THỜI GIAN (Mặc định: 'all')
$filter = $_GET['filter'] ?? 'all';
$dateCondition = "";

switch ($filter) {
    case 'today':
        $dateCondition = " AND DATE(created_at) = CURDATE()";
        break;
    case 'this_month':
        $dateCondition = " AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
    default:
        $dateCondition = ""; // Tất cả thời gian
        break;
}

// 2. TRUY VẤN TỔNG DOANH THU THEO BỘ LỌC
try {
    // Kiểm tra và thực thi tính tổng doanh thu an toàn
    $sqlRevenue = "SELECT SUM(total_price) AS total_revenue FROM bookings WHERE (status = 'paid' OR status = 'completed' OR status = '1')" . $dateCondition;
    $stmtRevenue = $pdo->prepare($sqlRevenue);
    $stmtRevenue->execute();
    $resRevenue = $stmtRevenue->fetch(PDO::FETCH_ASSOC);
    $totalRevenue = $resRevenue['total_revenue'] ?? 0;
} catch (PDOException $e) {
    // Trường hợp dự án không phân biệt trạng thái đơn hàng
    try {
        $sqlRevenueFallback = "SELECT SUM(total_price) AS total_revenue FROM bookings WHERE 1=1" . $dateCondition;
        $stmtRevenueFallback = $pdo->prepare($sqlRevenueFallback);
        $stmtRevenueFallback->execute();
        $resRevenueFallback = $stmtRevenueFallback->fetch(PDO::FETCH_ASSOC);
        $totalRevenue = $resRevenueFallback['total_revenue'] ?? 0;
    } catch (PDOException $ex) {
        $totalRevenue = 0;
    }
}

// 3. TRUY VẤN THỐNG KÊ DOANH THU THEO PHIM (DÙNG CHO BIỂU ĐỒ CHART.JS)
$movieLabels = [];
$movieData = [];

try {
    $sqlChart = "SELECT m.title, SUM(b.total_price) AS revenue
                 FROM bookings b
                 JOIN showtimes s ON b.showtime_id = s.id
                 JOIN movies m ON s.movie_id = m.id
                 WHERE (b.status = 'paid' OR b.status = 'completed' OR b.status = '1')
                 GROUP BY m.id, m.title
                 ORDER BY revenue DESC
                 LIMIT 5"; // Top 5 phim doanh thu cao nhất
                 
    $stmtChart = $pdo->prepare($sqlChart);
    $stmtChart->execute();
    $chartResults = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    foreach ($chartResults as $row) {
        $movieLabels[] = $row['title'];
        $movieData[] = (float)$row['revenue'];
    }
} catch (PDOException $e) {
    // Nếu chưa có dữ liệu giao dịch thành công
    $movieLabels = [];
    $movieData = [];
}

include_once 'header.php';

// Hàm xử lý đường dẫn ảnh poster chuẩn cho trang Admin
function getAdminPosterUrl($poster) {
    $poster = trim($poster ?? '');
    if (empty($poster)) {
        return 'https://placehold.co/800x1200/0f172a/f8fafc?text=No+Image';
    }
    // Nếu là URL online (http/https)
    if (filter_var($poster, FILTER_VALIDATE_URL)) {
        return $poster;
    }
    // Lùi về thư mục gốc để tìm trong uploads hoặc assets
    if (file_exists(__DIR__ . '/../uploads/' . $poster)) {
        return '../uploads/' . $poster;
    } elseif (file_exists(__DIR__ . '/../assets/images/' . $poster)) {
        return '../assets/images/' . $poster;
    }
    
    return '../uploads/' . $poster;
}

// 1. LẤY DANH SÁCH THỂ LOẠI
$genreStmt = $pdo->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != ''");
$genreRaw  = $genreStmt->fetchAll(PDO::FETCH_COLUMN);
$genreList = [];

foreach ($genreRaw as $g) {
    $parts = explode(',', $g);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '' && !in_array($part, $genreList, true)) {
            $genreList[] = $part;
        }
    }
}
sort($genreList);

// 2. LẤY THAM SỐ BỘ LỌC
$filterGenre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$searchTerm  = isset($_GET['search']) ? trim($_GET['search']) : '';

function escapeLike($string) {
    return addcslashes($string, '%_\\');
}

// 3. HÀM LẤY DANH SÁCH PHIM
function getMovies($pdo, $status, $genre, $search) {
    $sql = "SELECT DISTINCT m.* FROM movies m";
    $conditions = ["m.status = :status"];
    $params = [':status' => $status];

    if ($genre !== '') {
        $conditions[] = "m.genre LIKE :genre";
        $params[':genre'] = '%' . escapeLike($genre) . '%';
    }

    if ($search !== '') {
        $conditions[] = "(m.title LIKE :search OR m.director LIKE :search OR m.cast LIKE :search)";
        $params[':search'] = '%' . escapeLike($search) . '%';
    }

    $sql .= " WHERE " . implode(' AND ', $conditions) . " ORDER BY m.release_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$nowShowingMovies = getMovies($pdo, 'now_showing', $filterGenre, $searchTerm);
$comingSoonMovies = getMovies($pdo, 'coming_soon', $filterGenre, $searchTerm);

// 4. LẤY BANNER SLIDER
$bannerStmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC LIMIT 5");
$bannerStmt->execute();
$bannerMovies = $bannerStmt->fetchAll(PDO::FETCH_ASSOC);

// 5. HÀM HIỂN THỊ LƯỚI PHIM
function renderMovieGrid($movies) {
    if (empty($movies)) {
        echo '<div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
                <p>Không tìm thấy phim phù hợp.</p>
              </div>';
        return;
    }
    echo '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-5">';
    foreach ($movies as $movie) {
        $posterUrl = getAdminPosterUrl($movie['poster'] ?? '');
        $title     = htmlspecialchars($movie['title'] ?? '');
        $genre     = htmlspecialchars($movie['genre'] ?? '');
        $rating    = htmlspecialchars($movie['rating'] ?? 'P');
        $id        = (int)$movie['id'];

        $badgeBg = 'bg-green-600';
        if ($rating === 'K')   $badgeBg = 'bg-blue-600';
        if ($rating === 'T13') $badgeBg = 'bg-amber-500';
        if ($rating === 'T16') $badgeBg = 'bg-orange-600';
        if ($rating === 'T18') $badgeBg = 'bg-rose-600';

        echo '<a href="movie_detail.php?id=' . $id . '" class="group block bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-rose-500/60 transition-all duration-300 relative">
                <div class="aspect-[2/3] w-full overflow-hidden bg-slate-800 relative">
                    <span class="absolute top-2 left-2 z-10 ' . $badgeBg . ' text-white text-[10px] font-extrabold px-2 py-0.5 rounded-md">
                        ' . $rating . '
                    </span>
<img src="' . $posterUrl . '" onerror="this.src=\'https://placehold.co/400x600/0f172a/f8fafc?text=' . urlencode($title) . '\'"
                         alt="' . $title . '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-3">
                    <h3 class="text-sm font-bold text-white line-clamp-2 group-hover:text-rose-400 transition-colors">' . $title . '</h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-1">' . $genre . '</p>
                </div>
              </a>';
    }
    echo '</div>';
}
?>

<!-- NHÚNG THƯ VIỆN CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

    <!-- KHỐI HEADER BẢNG ĐIỀU KHIỂN & BỘ LỌC THỜI GIAN -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Bảng Điều Khiển Quản Trị</h1>
            <p class="text-slate-400 text-sm mt-1">Báo cáo doanh thu và chỉ số hoạt động hệ thống CineStar</p>
        </div>

        <!-- Form lọc doanh thu theo thời gian -->
        <form method="GET" action="index.php" class="flex items-center gap-3 bg-slate-900 border border-slate-800 p-2.5 rounded-xl">
            <?php if ($filterGenre !== ''): ?>
                <input type="hidden" name="genre" value="<?php echo htmlspecialchars($filterGenre); ?>">
            <?php endif; ?>
            <?php if ($searchTerm !== ''): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <?php endif; ?>
            <label for="filter" class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Thời gian:</label>
            <select name="filter" id="filter" onchange="this.form.submit()" 
                    class="bg-slate-950 border border-slate-800 text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-rose-500">
                <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Tất cả thời gian</option>
                <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>Hôm nay</option>
                <option value="this_month" <?= $filter == 'this_month' ? 'selected' : '' ?>>Tháng này</option>
            </select>
        </form>
    </div>

    <!-- KHỐI CARD THỐNG KÊ DOANH THU -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between z-10 relative">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Doanh Thu 
                        <?php 
                            if ($filter == 'today') echo '(Hôm nay)';
                            elseif ($filter == 'this_month') echo '(Tháng này)';
                            else echo '(Tất cả)';
                        ?>
                    </p>
                    <h3 class="text-2xl font-black text-emerald-400 mt-2">
                        <?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ
                    </h3>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-400 flex items-center gap-1">
                <span class="text-emerald-400 font-semibold">↑ Cập nhật tự động</span> từ các giao dịch thành công
            </div>
        </div>
    </div>

    <!-- KHỐI BIỂU ĐỒ DOANH THU TOP PHIM (CHART.JS) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Top 5 Phim Có Doanh Thu Cao Nhất
            </h2>
            <span class="text-xs text-slate-500">Đơn vị: VNĐ</span>
        </div>
        <div class="relative h-64 sm:h-72 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

</div>

<!-- BANNER SLIDER -->
<?php if (!empty($bannerMovies)): ?>
<section class="relative w-full h-[300px] sm:h-[420px] lg:h-[520px] overflow-hidden bg-slate-900" id="bannerSlider">
    <?php foreach ($bannerMovies as $i => $movie): ?>
        <?php $bannerPoster = getAdminPosterUrl($movie['poster'] ?? ''); ?>
        <div class="banner-slide absolute inset-0 transition-opacity duration-700 <?php echo $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>"
             data-index="<?php echo $i; ?>">
<<<<<<< HEAD
            <img src="<?php echo $bannerPoster; ?>"
=======
            <img src="../uploads/<?php echo htmlspecialchars($movie['poster'] ?? ''); ?>"
>>>>>>> 4043c9bbef9437dcf07783004f662ee7d1f03c4d
                 onerror="this.src='https://placehold.co/1600x600/0f172a/f8fafc?text=CineStar'"
                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-10 max-w-3xl">
                <span class="inline-block bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">ĐANG CHIẾU</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white mb-2">
                    <?php echo htmlspecialchars($movie['title']); ?>
                </h2>
                <p class="hidden sm:block text-slate-300 text-sm mb-4 line-clamp-2 max-w-xl">
                    <?php echo htmlspecialchars($movie['description'] ?? ''); ?>
                </p>
                <a href="movie_detail.php?id=<?php echo (int)$movie['id']; ?>"
                   class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all">
                    Đặt Vé Ngay
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <button id="bannerPrev" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 px-3 py-2 rounded-xl bg-slate-950/70 hover:bg-rose-600 text-white text-xs font-bold transition-all">
        Trước
    </button>
    <button id="bannerNext" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 px-3 py-2 rounded-xl bg-slate-950/70 hover:bg-rose-600 text-white text-xs font-bold transition-all">
        Sau
    </button>

    <div class="absolute bottom-3 right-4 z-20 flex gap-2" id="bannerDots">
        <?php foreach ($bannerMovies as $i => $movie): ?>
            <button class="banner-dot w-2.5 h-2.5 rounded-full transition-all <?php echo $i === 0 ? 'bg-rose-500 w-6' : 'bg-slate-500/60'; ?>" data-index="<?php echo $i; ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- BỘ LỌC PHIM -->
    <form action="index.php" method="GET" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 mb-10 flex flex-col sm:flex-row items-end gap-4">
        <?php if ($searchTerm !== ''): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <?php endif; ?>

        <div class="w-full sm:flex-1">
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Thể loại</label>
            <select name="genre" class="w-full bg-slate-950 border border-slate-800 text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-rose-500">
                <option value="">Tất cả thể loại</option>
                <?php foreach ($genreList as $g): ?>
                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo ($filterGenre === $g) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit" class="flex-1 sm:flex-none bg-rose-600 hover:bg-rose-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-all cursor-pointer">
                Lọc Phim
            </button>
            <?php if ($filterGenre !== '' || $searchTerm !== ''): ?>
                <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold px-5 py-2.5 rounded-xl text-sm transition-all inline-block text-center">
                    Bỏ Lọc
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <p class="text-slate-400 text-sm mb-6">
            Kết quả tìm kiếm cho "<span class="text-rose-400 font-semibold"><?php echo htmlspecialchars($searchTerm); ?></span>"
        </p>
    <?php endif; ?>

    <!-- PHIM ĐANG CHIẾU -->
    <div class="flex items-center gap-2 mb-5">
        <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
        <h2 class="text-xl font-bold text-white">Phim Đang Chiếu</h2>
    </div>
    <?php renderMovieGrid($nowShowingMovies); ?>

    <!-- PHIM SẮP CHIẾU -->
    <div class="flex items-center gap-2 mt-12 mb-5">
        <span class="w-1.5 h-6 bg-amber-400 rounded-full"></span>
        <h2 class="text-xl font-bold text-white">Phim Sắp Chiếu</h2>
    </div>
    <?php renderMovieGrid($comingSoonMovies); ?>

</div>

<!-- SCRIPT BIỂU ĐỒ CHART.JS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    const movieLabels = <?= json_encode($movieLabels) ?>;
    const movieData = <?= json_encode($movieData) ?>;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: movieLabels.length > 0 ? movieLabels : ['Chưa có doanh thu'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: movieData.length > 0 ? movieData : [0],
                backgroundColor: 'rgba(244, 63, 94, 0.8)', // Tông màu Rose chuẩn CineStar
                borderColor: 'rgba(244, 63, 94, 1)',
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94A3B8', font: { family: 'sans-serif', size: 12 } }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#94A3B8' },
                    grid: { color: 'rgba(51, 65, 85, 0.3)' }
                },
                y: {
                    ticks: {
                        color: '#94A3B8',
                        callback: function(val) {
                            return val.toLocaleString('vi-VN') + ' đ';
                        }
                    },
                    grid: { color: 'rgba(51, 65, 85, 0.3)' }
                }
            }
        }
    });
});
</script>

<!-- SCRIPT BANNER SLIDER -->
<script>
(function () {
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.banner-dot');
    const prevBtn = document.getElementById('bannerPrev');
    const nextBtn = document.getElementById('bannerNext');

    if (slides.length <= 1) return;

    let current = 0;
    let timer = null;

    function goTo(index) {
        slides[current].classList.remove('opacity-100', 'z-10');
        slides[current].classList.add('opacity-0', 'z-0');
        dots[current].classList.remove('bg-rose-500', 'w-6');
        dots[current].classList.add('bg-slate-500/60');

        current = (index + slides.length) % slides.length;

        slides[current].classList.remove('opacity-0', 'z-0');
        slides[current].classList.add('opacity-100', 'z-10');
        dots[current].classList.remove('bg-slate-500/60');
        dots[current].classList.add('bg-rose-500', 'w-6');
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAutoplay() { timer = setInterval(next, 5000); }
    function stopAutoplay() { clearInterval(timer); }

    if (nextBtn) nextBtn.addEventListener('click', () => { next(); stopAutoplay(); startAutoplay(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { prev(); stopAutoplay(); startAutoplay(); });

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.index, 10));
            stopAutoplay(); startAutoplay();
        });
    });

    startAutoplay();
})();
</script>

<?php include_once 'footer.php'; ?>