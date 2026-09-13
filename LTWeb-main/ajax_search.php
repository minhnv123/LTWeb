<?php
// File này chỉ trả về 1 đoạn HTML nhỏ (không include header/footer)
// vì được gọi bằng jQuery AJAX để nhúng trực tiếp vào dropdown gợi ý.
require_once 'config/db.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

// Chưa gõ đủ ký tự -> không trả kết quả gì
if (mb_strlen($keyword) < 1) {
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, title, poster, genre, release_date, status
     FROM movies
     WHERE title LIKE :kw
     ORDER BY
        CASE WHEN status = 'now_showing' THEN 0 ELSE 1 END,
        release_date DESC
     LIMIT 6"
);
$stmt->execute([':kw' => '%' . $keyword . '%']);
$results = $stmt->fetchAll();

if (empty($results)) {
    echo '<div class="px-4 py-6 text-center text-sm text-slate-400">
            <i class="fa-solid fa-circle-exclamation mr-1.5"></i>Không tìm thấy phim nào khớp với "' . htmlspecialchars($keyword) . '"
          </div>';
    exit;
}

foreach ($results as $movie) {
    $title  = htmlspecialchars($movie['title']);
    // Kiểm tra nếu poster rỗng thì để ảnh mặc định
    $poster = !empty($movie['poster']) ? 'uploads/' . htmlspecialchars($movie['poster']) : 'https://placehold.co/80x120/0f172a/f8fafc?text=Poster';
    $genre  = htmlspecialchars($movie['genre'] ?? '');
    
    $badge  = ($movie['status'] ?? '') === 'now_showing'
        ? '<span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">ĐANG CHIẾU</span>'
        : '<span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded">SẮP CHIẾU</span>';

    echo '<a href="movie_detail.php?id=' . $movie['id'] . '" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-800 transition-colors border-b border-slate-800 last:border-0">
            <img src="' . $poster . '" onerror="this.src=\'https://placehold.co/80x120/0f172a/f8fafc?text=Poster\'"
                 class="w-10 h-14 object-cover rounded-md flex-shrink-0" alt="' . $title . '">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">' . $title . '</p>
                <p class="text-xs text-slate-500 truncate mt-0.5">' . $genre . '</p>
                <div class="mt-1">' . $badge . '</div>
            </div>
          </a>';
}
exit;