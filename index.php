<?php
require_once 'config/dp.php';
include_once 'header.php';

// ============================================================
// 1. LẤY DANH SÁCH THỂ LOẠI (dùng cho dropdown Bộ lọc)
// ============================================================
$genreStmt = $pdo->query("SELECT genre FROM movies");
$genreRaw  = $genreStmt->fetchAll(PDO::FETCH_COLUMN);
$genreList = [];
foreach ($genreRaw as $g) {
    foreach (explode(',', $g) as $part) {
        $part = trim($part);
        if ($part !== '' && !in_array($part, $genreList)) {
            $genreList[] = $part;
        }
    }
}
sort($genreList);

// ============================================================
// 2. ĐỌC THAM SỐ BỘ LỌC TỪ GET
// ============================================================
$filterGenre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$filterDate  = isset($_GET['date']) ? trim($_GET['date']) : '';
$searchTerm  = isset($_GET['search']) ? trim($_GET['search']) : '';

// ============================================================
// 3. HÀM DÙNG CHUNG: LẤY DANH SÁCH PHIM THEO STATUS + BỘ LỌC
// ============================================================
function getMovies($pdo, $status, $genre, $date, $search) {
    $sql = "SELECT DISTINCT m.* FROM movies m";
    $conditions = ["m.status = :status"];
    $params = [':status' => $status];

    // Nếu lọc theo ngày chiếu -> phải join sang bảng showtimes
    if ($date !== '') {
        $sql .= " INNER JOIN showtimes s ON s.movie_id = m.id";
        $conditions[] = "s.show_date = :sdate";
        $params[':sdate'] = $date;
    }

    if ($genre !== '') {
        $conditions[] = "m.genre LIKE :genre";
        $params[':genre'] = '%' . $genre . '%';
    }

    if ($search !== '') {
        $conditions[] = "(m.title LIKE :search OR m.director LIKE :search OR m.cast LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " WHERE " . implode(' AND ', $conditions) . " ORDER BY m.release_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

$nowShowingMovies = getMovies($pdo, 'now_showing', $filterGenre, $filterDate, $searchTerm);
$comingSoonMovies = getMovies($pdo, 'coming_soon', $filterGenre, $filterDate, $searchTerm);

// ============================================================
// 4. LẤY 5 PHIM MỚI NHẤT ĐANG CHIẾU CHO BANNER SLIDER
// ============================================================
$bannerStmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC LIMIT 5");
$bannerStmt->execute();
$bannerMovies = $bannerStmt->fetchAll();
?>

<!-- ============================================================ -->
<!-- 1. BANNER SLIDER -->
<!-- ============================================================ -->
<?php if (!empty($bannerMovies)): ?>
<section class="relative w-full h-[300px] sm:h-[420px] lg:h-[520px] overflow-hidden bg-slate-900" id="bannerSlider">
    <?php foreach ($bannerMovies as $i => $movie): ?>
        <div class="banner-slide absolute inset-0 transition-opacity duration-700 <?php echo $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>"
             data-index="<?php echo $i; ?>">
            <img src="uploads/<?php echo htmlspecialchars($movie['poster']); ?>"
                 onerror="this.src='https://placehold.co/1600x600/0f172a/f8fafc?text=CineStar'"
                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-10 max-w-3xl">
                <span class="inline-block bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">ĐANG CHIẾU</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white drop-shadow-lg mb-2">
                    <?php echo htmlspecialchars($movie['title']); ?>
                </h2>
                <p class="hidden sm:block text-slate-300 text-sm mb-4 line-clamp-2 max-w-xl">
                    <?php echo htmlspecialchars($movie['description'] ?? ''); ?>
                </p>
                <a href="movie_detail.php?id=<?php echo $movie['id']; ?>"
                   class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all">
                    <i class="fa-solid fa-ticket"></i> Đặt Vé Ngay
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Nút điều hướng -->
    <button id="bannerPrev" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-950/60 hover:bg-rose-600 text-white flex items-center justify-center transition-all">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button id="bannerNext" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-950/60 hover:bg-rose-600 text-white flex items-center justify-center transition-all">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

    <!-- Dấu chấm chỉ thị -->
    <div class="absolute bottom-3 right-4 z-20 flex gap-2" id="bannerDots">
        <?php foreach ($bannerMovies as $i => $movie): ?>
            <button class="banner-dot w-2.5 h-2.5 rounded-full transition-all <?php echo $i === 0 ? 'bg-rose-500 w-6' : 'bg-slate-500/60'; ?>" data-index="<?php echo $i; ?>"></button>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- ============================================================ -->
    <!-- 2. BỘ LỌC PHIM (Thể loại + Ngày chiếu) -->
    <!-- ============================================================ -->
    <form action="index.php" method="GET" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 mb-10 flex flex-wrap items-end gap-4">
        <?php if ($searchTerm !== ''): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <?php endif; ?>

        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Thể loại</label>
            <select name="genre" class="w-full bg-slate-950 border border-slate-800 text-slate-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-rose-500">
                <option value="">Tất cả thể loại</option>
                <?php foreach ($genreList as $g): ?>
                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo ($filterGenre === $g) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Ngày chiếu</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>"
                   class="w-full bg-slate-950 border border-slate-800 text-slate-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-rose-500">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-all">
                <i class="fa-solid fa-filter mr-1.5"></i>Lọc Phim
            </button>
            <?php if ($filterGenre !== '' || $filterDate !== '' || $searchTerm !== ''): ?>
                <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold px-5 py-2.5 rounded-xl text-sm transition-all">
                    Xóa Lọc
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <p class="text-slate-400 text-sm mb-6">
            Kết quả tìm kiếm cho "<span class="text-rose-400 font-semibold"><?php echo htmlspecialchars($searchTerm); ?></span>"
        </p>
    <?php endif; ?>

    <?php
    // Hàm hiển thị lưới danh sách phim (dùng chung cho 2 mục dưới)
    function renderMovieGrid($movies) {
        if (empty($movies)) {
            echo '<div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
                    <i class="fa-solid fa-clapperboard text-3xl mb-3 text-slate-600"></i>
                    <p>Không tìm thấy phim phù hợp.</p>
                  </div>';
            return;
        }
        echo '<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">';
        foreach ($movies as $movie) {
            $poster = htmlspecialchars($movie['poster']);
            $title  = htmlspecialchars($movie['title']);
            $genre  = htmlspecialchars($movie['genre']);
            echo '<a href="movie_detail.php?id=' . $movie['id'] . '" class="group block bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-rose-500/60 hover:-translate-y-1 transition-all duration-300">
                    <div class="aspect-[2/3] w-full overflow-hidden bg-slate-800">
                        <img src="uploads/' . $poster . '" onerror="this.src=\'https://placehold.co/400x600/0f172a/f8fafc?text=' . urlencode($title) . '\'"
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

    <!-- ============================================================ -->
    <!-- 3. PHIM ĐANG CHIẾU -->
    <!-- ============================================================ -->
    <div class="flex items-center gap-2 mb-5">
        <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
        <h2 class="text-xl font-bold text-white">Phim Đang Chiếu</h2>
    </div>
    <?php renderMovieGrid($nowShowingMovies); ?>

    <!-- ============================================================ -->
    <!-- 4. PHIM SẮP CHIẾU -->
    <!-- ============================================================ -->
    <div class="flex items-center gap-2 mt-12 mb-5">
        <span class="w-1.5 h-6 bg-amber-400 rounded-full"></span>
        <h2 class="text-xl font-bold text-white">Phim Sắp Chiếu</h2>
    </div>
    <?php renderMovieGrid($comingSoonMovies); ?>

</div>

<!-- ============================================================ -->
<!-- JS: BANNER SLIDER TỰ ĐỘNG CHẠY -->
<!-- ============================================================ -->
<script>
(function () {
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.banner-dot');
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

    function startAutoplay() {
        timer = setInterval(next, 5000);
    }
    function stopAutoplay() {
        clearInterval(timer);
    }

    document.getElementById('bannerNext').addEventListener('click', () => { next(); stopAutoplay(); startAutoplay(); });
    document.getElementById('bannerPrev').addEventListener('click', () => { prev(); stopAutoplay(); startAutoplay(); });
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
