<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Giả định số lượng vé đã đặt
$bookingCount = isset($_SESSION['bookings']) ? count($_SESSION['bookings']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineStar - Đặt Vé Xem Phim Online</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Font Google Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* slate-950 */
            color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between">

    <!-- HEADER COMPONENT -->
    <header class="sticky top-0 z-40 bg-slate-950/90 backdrop-blur-md border-b border-slate-800 text-white transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 cursor-pointer group select-none flex-shrink-0 text-decoration-none">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-rose-600 via-pink-600 to-amber-500 p-0.5 shadow-lg shadow-rose-600/30 group-hover:scale-105 transition-transform">
                    <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                        <i class="fa-solid fa-film text-rose-500 text-lg group-hover:rotate-6 transition-transform"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-2xl tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                            CINE<span class="text-rose-500">STAR</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-widest px-1.5 py-0.5 bg-rose-500/20 text-rose-400 rounded border border-rose-500/30">
                            VIP
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">ĐẶT VÉ XEM PHIM ONLINE</p>
                </div>
            </a>

            <!-- Search Bar (Desktop) -->
            <form action="index.php" method="GET" class="hidden md:flex flex-1 max-w-md mx-4 relative">
                <i class="fa-solid fa-magnifying-glass w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    placeholder="Tìm tên phim, diễn viên, đạo diễn..."
                    class="w-full bg-slate-900/90 text-sm text-slate-100 pl-10 pr-4 py-2.5 rounded-full border border-slate-800 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all placeholder:text-slate-500"
                />
            </form>

            <!-- Navigation Actions -->
            <nav class="flex items-center gap-2 sm:gap-3">
                <!-- Trang Chủ -->
                <a href="index.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <i class="fa-solid fa-clapperboard text-rose-500"></i>
                    <span class="hidden sm:inline">Trang Chủ</span>
                </a>

                <!-- Hệ Thống Rạp -->
                <a href="cinemas.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <i class="fa-solid fa-location-dot text-amber-400"></i>
                    <span class="hidden sm:inline">Hệ Thống Rạp</span>
                </a>

                <!-- Vé Của Tôi / Lịch Sử -->
                <a href="profile.php" class="relative flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <i class="fa-solid fa-ticket text-rose-400"></i>
                    <span class="hidden sm:inline">Vé Của Tôi</span>
                    <?php if ($bookingCount > 0): ?>
                        <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-slate-950 animate-bounce">
                            <?php echo $bookingCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Tài Khoản (Đăng Nhập / Đăng Xuất) -->
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="logout.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold bg-rose-600 text-white shadow-md shadow-rose-600/30 hover:bg-rose-700 transition-all" title="Đăng xuất">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline"><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold bg-slate-800 text-slate-200 hover:text-white hover:bg-rose-600 transition-all">
                        <i class="fa-solid fa-user text-slate-400"></i>
                        <span class="hidden sm:inline">Đăng Nhập</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Mobile Search Input -->
        <div class="md:hidden px-4 pb-3">
            <form action="index.php" method="GET" class="relative">
                <i class="fa-solid fa-magnifying-glass w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Tìm phim, đạo diễn, diễn viên..."
                    class="w-full bg-slate-900 text-sm text-slate-100 pl-10 pr-4 py-2 rounded-full border border-slate-800 focus:outline-none focus:border-rose-500"
                />
            </form>
        </div>
    </header>

    <!-- NỘI DUNG TRANG WEB SẼ HIỂN THỊ TỪ ĐÂY -->
    <main class="flex-grow">