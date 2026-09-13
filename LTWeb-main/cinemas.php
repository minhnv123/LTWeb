<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';
include_once 'header.php';

// 1. Lấy danh sách tất cả các rạp
$stmtCinemas = $pdo->query("SELECT * FROM cinemas ORDER BY id ASC");
$cinemas = $stmtCinemas->fetchAll(PDO::FETCH_ASSOC);

// 2. Lấy danh sách lịch chiếu kèm thông tin phim cho từng rạp
$stmtShowtimes = $pdo->query("
    SELECT s.*, m.title AS movie_title, m.poster, m.duration
    FROM showtimes s
    JOIN movies m ON m.id = s.movie_id
    WHERE s.show_date >= CURDATE()
    ORDER BY s.show_date ASC, s.show_time ASC
");
$rawShowtimes = $stmtShowtimes->fetchAll(PDO::FETCH_ASSOC);

// Nhóm lịch chiếu theo cinema_id
$showtimesByCinema = [];
foreach ($rawShowtimes as $st) {
    $showtimesByCinema[$st['cinema_id']][] = $st;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
            <span class="w-2 h-8 bg-rose-600 rounded-full"></span>
            Hệ Thống Rạp CineStar
        </h1>
        <p class="text-slate-400 text-sm mt-1">Danh sách cụm rạp và lịch chiếu mới nhất</p>
    </div>

    <?php if (empty($cinemas)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
            <i class="fa-solid fa-building-circle-xmark text-4xl mb-3 text-slate-600"></i>
            <p>Hiện chưa có thông tin rạp chiếu nào.</p>
        </div>
    <?php else: ?>
        <div class="space-y-8">
            <?php foreach ($cinemas as $cinema): ?>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <!-- Thông tin rạp -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
                        <div>
                            <h2 class="text-xl font-bold text-rose-500 flex items-center gap-2">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($cinema['name']); ?>
                            </h2>
                            <p class="text-slate-300 text-sm mt-1">
                                <?php echo htmlspecialchars($cinema['address']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Lịch chiếu tại rạp -->
                    <div class="mt-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Lịch chiếu sắp tới</h3>
                        
                        <?php if (isset($showtimesByCinema[$cinema['id']]) && !empty($showtimesByCinema[$cinema['id']])): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($showtimesByCinema[$cinema['id']] as $st): ?>
                                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
                                        <img src="uploads/<?php echo htmlspecialchars($st['poster'] ?? ''); ?>" 
                                             onerror="this.src='https://placehold.co/100x150/0f172a/94a3b8?text=NO+IMAGE'" 
                                             alt="<?php echo htmlspecialchars($st['movie_title']); ?>"
                                             class="w-12 h-16 object-cover rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($st['movie_title']); ?></h4>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                <i class="fa-regular fa-calendar mr-1"></i><?php echo date('d/m/Y', strtotime($st['show_date'])); ?>
                                            </p>
                                            <div class="mt-2 flex items-center justify-between">
                                                <span class="text-amber-400 font-mono text-sm font-bold">
                                                    <?php echo date('H:i', strtotime($st['show_time'])); ?>
                                                </span>
                                                <a href="movie_detail.php?id=<?php echo (int)$st['movie_id']; ?>" 
                                                   class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg transition-all">
                                                    Đặt vé
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-slate-500 italic">Chưa có lịch chiếu cho rạp này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>