<?php
// 1. Nhúng Header Admin (Đã bao gồm kiểm tra quyền, kết nối CSDL và Sidebar)
include_once 'header.php';

// 2. Query lấy dữ liệu thống kê tổng quan cho Dashboard
try {
    $total_movies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $total_showtimes = $pdo->query("SELECT COUNT(*) FROM showtimes")->fetchColumn();
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
} catch (PDOException $e) {
    // Trường hợp CSDL chưa có một số bảng
    $total_movies = $total_showtimes = $total_bookings = $total_users = 0;
}
?>

<!-- TIÊU ĐỀ TRANG -->
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-rose-500"></i> Bảng Điều Khiển Quản Trị
        </h1>
        <p class="text-xs text-slate-400 mt-1">Chào mừng trở lại, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
    </div>
</div>

<!-- 1. CÁC THẺ THỐNG KÊ TỔNG QUAN -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 uppercase font-semibold mb-1">Tổng Số Phim</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($total_movies); ?></h3>
        </div>
        <div class="w-12 h-12 bg-rose-500/10 text-rose-500 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-clapperboard"></i>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 uppercase font-semibold mb-1">Lịch Chiếu</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($total_showtimes); ?></h3>
        </div>
        <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 uppercase font-semibold mb-1">Vé Đã Đặt</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($total_bookings); ?></h3>
        </div>
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-ticket"></i>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400 uppercase font-semibold mb-1">Khách Hàng</p>
            <h3 class="text-2xl font-bold text-white"><?php echo number_format($total_users); ?></h3>
        </div>
        <div class="w-12 h-12 bg-purple-500/10 text-purple-500 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
</div>

<!-- 2. PHÍM TẮT TRUY CẬP NHANH CÁC CHỨC NĂNG THEO BẢNG YÊU CẦU -->
<h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
    <i class="fa-solid fa-bolt text-amber-500"></i> Lối Tắt Chức Năng Quản Lý
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Module 3: Quản Lý Phim -->
    <a href="movies_list.php" class="block bg-slate-900 hover:bg-slate-800/80 border border-slate-800 rounded-2xl p-6 transition-all group">
        <div class="w-10 h-10 bg-rose-600/20 text-rose-500 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-film"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-1">Quản Lý Phim (CRUD)</h3>
        <p class="text-xs text-slate-400">Xem danh sách phim, thêm phim mới, sửa thông tin và upload poster phim.</p>
    </a>

    <!-- Module 4: Lịch Chiếu -->
    <a href="showtimes_list.php" class="block bg-slate-900 hover:bg-slate-800/80 border border-slate-800 rounded-2xl p-6 transition-all group">
        <div class="w-10 h-10 bg-blue-600/20 text-blue-500 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-clock"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-1">Quản Lý Lịch Chiếu</h3>
        <p class="text-xs text-slate-400">Tạo suất chiếu gắn phim với phòng chiếu, phân trang danh sách lịch chiếu.</p>
    </a>

    <!-- Module 4: Soát Vé -->
    <a href="check_ticket.php" class="block bg-slate-900 hover:bg-slate-800/80 border border-slate-800 rounded-2xl p-6 transition-all group">
        <div class="w-10 h-10 bg-emerald-600/20 text-emerald-500 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-qrcode"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-1">Soát Vé (Check Ticket)</h3>
        <p class="text-xs text-slate-400">Nhập mã vé hoặc quét QR để kiểm tra và chuyển trạng thái vé của khách.</p>
    </a>
</div>

<?php
// 3. Nhúng Footer Admin
include_once 'footer.php';
?>