<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Lấy role và làm sạch dữ liệu
$userRole = strtolower(trim($_SESSION['user']['role'] ?? ''));

// 2. Kiểm tra quyền Admin dựa trên mảng $_SESSION['user']
if (!isset($_SESSION['user']) || $userRole !== 'admin') {
    header('Location:../login.php');
    exit();
}

require_once '../config/db.php';

// Xác định trang hiện tại để active menu
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineStar Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex">

    <!-- SIDEBAR BÊN TRÁI -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between min-h-screen fixed left-0 top-0 bottom-0 z-50">
        <div>
            <!-- LOGO -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800">
                <a href="index.php" class="flex items-center gap-2 text-rose-500 font-black text-xl">
                    <i class="fa-solid fa-film"></i>
                    <span>CINESTAR <span class="text-xs bg-rose-600 text-white px-2 py-0.5 rounded font-semibold">ADMIN</span></span>
                </a>
            </div>

            <!-- NAVIGATION MENU -->
            <nav class="p-4 space-y-1.5 text-sm font-medium">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo $currentPage == 'index.php' ? 'bg-rose-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'; ?>">
                    <i class="fa-solid fa-chart-pie w-5"></i> Bảng Điều Khiển
                </a>

                <a href="movies_list.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo (in_array($currentPage, ['movies_list.php', 'movie_add.php', 'movie_edit.php'])) ? 'bg-rose-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'; ?>">
                    <i class="fa-solid fa-clapperboard w-5"></i> Quản Lý Phim
                </a>

                <a href="cinemas_list.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo (in_array($currentPage, ['cinemas_list.php', 'cinema_add.php', 'cinema_edit.php'])) ? 'bg-rose-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'; ?>">
                    <i class="fa-solid fa-building-ngo w-5"></i> Quản Lý Rạp
                </a>

                <a href="showtimes_list.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo (in_array($currentPage, ['showtimes_list.php', 'showtime_add.php', 'showtime_edit.php'])) ? 'bg-rose-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'; ?>">
                    <i class="fa-solid fa-calendar-days w-5"></i> Lịch Chiếu
                </a>

                <a href="check_ticket.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo $currentPage == 'check_ticket.php' ? 'bg-rose-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'; ?>">
                    <i class="fa-solid fa-ticket w-5"></i> Soát Vé
                </a>
                
                <a href="../index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-all mt-6 border-t border-slate-800">
                    <i class="fa-solid fa-arrow-up-right-from-square w-5"></i> Xem Website Client
                </a>
            </nav>
        </div>

        <!-- THÔNG TIN TÀI KHOẢN -->
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-rose-600/20 text-rose-500 flex items-center justify-center font-bold text-sm shrink-0">
                    <?php echo mb_substr($_SESSION['user']['full_name'] ?? 'A', 0, 1, 'UTF-8'); ?>
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-slate-200 truncate"><?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin'); ?></p>
                    <p class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? 'admin@gmail.com'); ?></p>
                </div>
            </div>
            <!-- NÚT THOÁT -->
            <a href="logout.php" title="Đăng xuất" class="text-slate-400 hover:text-rose-400 p-2 text-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- KHU VỰC NỘI DUNG CHÍNH (BÊN PHẢI SIDEBAR) -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 p-8">