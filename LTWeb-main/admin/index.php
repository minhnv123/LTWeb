<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Đoạn code cũ của bạn tiếp tục từ đây...
require_once 'config/db.php'; 
include_once 'header.php';

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
        $poster   = htmlspecialchars($movie['poster'] ?? '');
        $title    = htmlspecialchars($movie['title'] ?? '');
        $genre    = htmlspecialchars($movie['genre'] ?? '');
        $rating   = htmlspecialchars($movie['rating'] ?? 'P');
        $id       = (int)$movie['id'];

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

<!-- BANNER SLIDER -->
<?php if (!empty($bannerMovies)): ?>
<section class="relative w-full h-[300px] sm:h-[420px] lg:h-[520px] overflow-hidden bg-slate-900" id="bannerSlider">
    <?php foreach ($bannerMovies as $i => $movie): ?>
        <div class="banner-slide absolute inset-0 transition-opacity duration-700 <?php echo $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>"
             data-index="<?php echo $i; ?>">
            <img src="uploads/<?php echo htmlspecialchars($movie['poster'] ?? ''); ?>"
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