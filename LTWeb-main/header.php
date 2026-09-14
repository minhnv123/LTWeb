<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra xem có đơn hàng pending hay không để hiển thị badge thông báo
$hasPendingBooking = isset($_SESSION['pending_booking']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineStar - Đặt Vé Xem Phim Online</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Google Plus Jakarta Sans (Thay đổi font chữ mới) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
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
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-rose-600 via-pink-600 to-amber-500 p-0.5 group-hover:scale-105 transition-transform">
                    <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center font-black text-rose-500 text-xl tracking-tighter">
                        CS
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
            <form action="index.php" method="GET" class="hidden md:flex flex-1 max-w-md mx-4 relative" autocomplete="off">
                <input
                    type="text"
                    name="search"
                    id="searchInputDesktop"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    placeholder="Tìm tên phim, diễn viên, đạo diễn..."
                    class="js-search-input w-full bg-slate-900/90 text-sm text-slate-100 px-4 py-2.5 rounded-full border border-slate-800 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all placeholder:text-slate-500"
                />
                <div id="searchResultsDesktop" class="js-search-results hidden absolute top-full left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden max-h-[420px] overflow-y-auto z-50"></div>
            </form>

            <!-- Navigation Actions -->
            <nav class="flex items-center gap-2 sm:gap-3">
                <!-- Trang Chủ -->
                <a href="index.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <span>Trang Chủ</span>
                </a>

                <!-- Hệ Thống Rạp -->
                <a href="cinemas.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <span>Hệ Thống Rạp</span>
                </a>

                <!-- Vé Của Tôi / Lịch Sử -->
                <a href="<?php echo $hasPendingBooking ? 'checkout.php' : 'history.php'; ?>" class="relative flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                    <span>Vé Của Tôi</span>
                    <?php if ($hasPendingBooking): ?>
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Tài Khoản (Đăng Nhập / Profile & Đăng Xuất) -->
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="flex items-center gap-2">
                        <!-- Nút Trang Admin (Chỉ hiện khi tài khoản có role admin) -->
                        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500 hover:text-slate-950 transition-all" title="Trang Quản Trị">
                                <span>Admin</span>
                            </a>
                        <?php endif; ?>

                        <!-- Nút Profile -->
                        <a href="profile.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-all" title="Thông tin cá nhân">
                            <span><?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Tài khoản'); ?></span>
                        </a>

                        <!-- Nút Đăng Xuất -->
                        <a href="logout.php" title="Đăng xuất" class="px-3 py-2 bg-slate-900 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-800 rounded-xl text-sm transition-all flex items-center justify-center font-medium">
                            Đăng xuất
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold bg-slate-800 text-slate-200 hover:text-white hover:bg-rose-600 transition-all">
                        <span>Đăng Nhập</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Mobile Search Input -->
        <div class="md:hidden px-4 pb-3 relative">
            <form action="index.php" method="GET" class="relative" autocomplete="off">
                <input
                    type="text"
                    name="search"
                    id="searchInputMobile"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    placeholder="Tìm phim, đạo diễn, diễn viên..."
                    class="js-search-input w-full bg-slate-900 text-sm text-slate-100 px-4 py-2 rounded-full border border-slate-800 focus:outline-none focus:border-rose-500"
                />
                <div id="searchResultsMobile" class="js-search-results hidden absolute top-full left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden max-h-[360px] overflow-y-auto z-50"></div>
            </form>
        </div>
    </header>

    <!-- NỘI DUNG TRANG WEB SẼ HIỂN THỊ TỪ ĐÂY -->
    <main class="flex-grow"></main>