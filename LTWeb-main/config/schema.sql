CREATE DATABASE IF NOT EXISTS `cinema` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cinema`;

-- 1. Bảng Người dùng
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Bảng Phim (CỦA BẠN)
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `director` VARCHAR(255) DEFAULT NULL,
  `cast` TEXT DEFAULT NULL,
  `genre` VARCHAR(255) NOT NULL,
  `duration` INT NOT NULL,
  `release_date` DATE DEFAULT NULL,
  `poster` VARCHAR(255) NOT NULL DEFAULT 'default-poster.jpg',
  `description` TEXT DEFAULT NULL,
  `trailer_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('now_showing', 'coming_soon') DEFAULT 'now_showing',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Bảng Rạp
CREATE TABLE IF NOT EXISTS `cinemas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Bảng Lịch chiếu
CREATE TABLE IF NOT EXISTS `showtimes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `cinema_id` INT NOT NULL,
  `show_date` DATE NOT NULL,
  `show_time` TIME NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 75000.00,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`cinema_id`) REFERENCES `cinemas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Bảng Đặt vé
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `showtime_id` INT NOT NULL,
  `seats` VARCHAR(255) NOT NULL,
  `combos` TEXT DEFAULT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'used', 'cancelled') DEFAULT 'paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`showtime_id`) REFERENCES `showtimes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DỮ LIỆU MẪU BAN ĐẦU
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`) VALUES
(1, 'Admin CineStar', 'admin@gmail.com', '$2y$10$4vC8IuM3q.4w/x/4w9j4uO.yKkG.5lH8xJ1qP8kS2G8.9lH8xJ1qP', 'admin');

INSERT INTO `movies` (`id`, `title`, `director`, `cast`, `genre`, `duration`, `release_date`, `poster`, `description`, `status`) VALUES
(1, 'LẬT MẶT 7: MỘT ĐIỀU ƯỚC', 'Lý Hải', 'Thanh Hiền, Trương Minh Cường', 'Gia đình, Tâm lý', 138, '2026-04-26', 'lat-mat-7.jpg', 'Phim về tình mẫu tử cảm động.', 'now_showing'),
(2, 'DEADPOOL & WOLVERINE', 'Shawn Levy', 'Ryan Reynolds, Hugh Jackman', 'Hành động, Viễn tưởng', 128, '2026-07-26', 'deadpool.jpg', 'Phim hành động siêu hài của Marvel.', 'coming_soon');
(3,  'NHÀ BÀ NỮ', 'Trấn Thành', 'Trấn Thành, Lê Giang, Ngân Chi', 'Tâm lý, Hài', 116, '2023-01-22', 'nha-ba-nu.jpg', 'Bi hài kịch gia đình xoay quanh ba thế hệ phụ nữ sống chung một mái nhà.', 'now_showing'),
(4,  'BỐ GIÀ', 'Trấn Thành, Vũ Ngọc Đãng', 'Trấn Thành, Tuấn Trần', 'Tâm lý, Gia đình', 128, '2021-03-05', 'bo-gia.jpg', 'Câu chuyện gia đình xúc động giữa người cha nghèo và cậu con trai nhiều hoài bão.', 'now_showing'),
(5,  'ĐÀO, PHỞ VÀ PIANO', 'Phi Tiến Sơn', 'Doãn Quốc Đam, Cao Thị Thùy Linh', 'Lịch sử, Chiến tranh', 100, '2024-02-10', 'dao-pho-piano.jpg', 'Câu chuyện tình yêu giữa khói lửa Hà Nội mùa đông năm 1946.', 'now_showing'),
(6,  'EXHUMA: QUẬT MỘ TRÙNG MA', 'Jang Jae-hyun', 'Choi Min-sik, Kim Go-eun', 'Kinh dị, Bí ẩn', 134, '2024-02-22', 'exhuma.jpg', 'Một pháp sư và thầy phong thủy khai quật ngôi mộ cổ chứa lời nguyền bí ẩn.', 'now_showing'),
(7,  'GODZILLA X KONG: ĐẾ CHẾ MỚI', 'Adam Wingard', 'Rebecca Hall, Dan Stevens', 'Hành động, Viễn tưởng', 115, '2024-03-29', 'godzilla-kong.jpg', 'Hai quái vật huyền thoại buộc phải liên minh để đối đầu mối đe dọa khổng lồ.', 'now_showing'),
(8,  'KUNG FU PANDA 4', 'Mike Mitchell', 'Jack Black (lồng tiếng), Awkwafina (lồng tiếng)', 'Hoạt hình, Gia đình', 94, '2024-03-08', 'kungfu-panda-4.jpg', 'Po phải tìm người kế nhiệm để trở thành Long Chiến Thần tiếp theo.', 'now_showing'),
(9,  'INSIDE OUT 2', 'Kelsey Mann', 'Amy Poehler (lồng tiếng), Maya Hawke (lồng tiếng)', 'Hoạt hình, Gia đình', 96, '2024-06-14', 'inside-out-2.jpg', 'Riley bước vào tuổi dậy thì với sự xuất hiện của những cảm xúc mới.', 'coming_soon'),
(10, 'DEADPOOL & WOLVERINE', 'Shawn Levy', 'Ryan Reynolds, Hugh Jackman', 'Hành động, Hài', 128, '2024-07-26', 'deadpool.jpg', 'Deadpool bắt tay cùng Wolverine trong hành trình đa vũ trụ đầy hài hước.', 'coming_soon'),
(11, 'KẺ ĂN HỒN', 'Trần Hữu Tấn', 'Lâm Thanh Mỹ, Nguyên Thảo', 'Kinh dị, Tâm linh', 105, '2023-07-14', 'ke-an-hon.jpg', 'Bí ẩn rùng rợn bao trùm một ngôi làng sau đám cưới đầy điềm gở.', 'now_showing'),
(12, 'MÓNG VUỐT', 'Lê Thanh Sơn', 'Thái Hòa, Karen Nguyễn', 'Hành động, Kinh dị', 100, '2024-04-19', 'mong-vuot.jpg', 'Nhóm phượt thủ mắc kẹt giữa rừng sâu và bị một sinh vật bí ẩn săn đuổi.', 'now_showing'),
(13, 'CÁI GIÁ CỦA HẠNH PHÚC', 'Luk Vân', 'Thanh Thúy, Kiều Minh Tuấn', 'Tâm lý', 108, '2024-04-30', 'cai-gia-cua-hanh-phuc.jpg', 'Những góc khuất trong hôn nhân được phơi bày qua lăng kính đầy chân thực.', 'now_showing'),
(14, 'DUNE: HÀNH TINH CÁT 2', 'Denis Villeneuve', 'Timothée Chalamet, Zendaya', 'Viễn tưởng, Phiêu lưu', 166, '2024-03-01', 'dune-2.jpg', 'Paul Atreides tiếp tục hành trình báo thù và định mệnh trên hành tinh Arrakis.', 'now_showing'),
(15, 'WONKA', 'Paul King', 'Timothée Chalamet, Olivia Colman', 'Gia đình, Nhạc kịch', 116, '2023-12-15', 'wonka.jpg', 'Hành trình khởi nghiệp đầy màu sắc của chàng thợ làm socola trẻ tuổi Willy Wonka.', 'now_showing'),
(16, 'AQUAMAN VÀ VƯƠNG QUỐC THẤT LẠC', 'James Wan', 'Jason Momoa, Patrick Wilson', 'Hành động, Viễn tưởng', 124, '2023-12-22', 'aquaman-2.jpg', 'Aquaman phải liên minh với kẻ thù cũ để bảo vệ Atlantis khỏi hiểm họa cổ xưa.', 'now_showing'),
(17, 'CHỊ CHỊ EM EM 2', 'Vũ Thành Vinh', 'Ninh Dương Lan Ngọc, Ngọc Trinh', 'Tâm lý, Hài', 105, '2023-08-18', 'chi-chi-em-em-2.jpg', 'Cuộc chiến ngầm giữa hai người phụ nữ trong giới giải trí đầy thị phi.', 'now_showing'),
(18, 'ĐẤT RỪNG PHƯƠNG NAM', 'Nguyễn Quang Dũng', 'Hạo Khang, Tuấn Trần', 'Phiêu lưu, Gia đình', 110, '2023-10-13', 'dat-rung-phuong-nam.jpg', 'Hành trình phiêu lưu của cậu bé An giữa vùng sông nước Nam Bộ thời loạn lạc.', 'now_showing'),
(19, 'NGƯỜI VỢ CUỐI CÙNG', 'Victor Vũ', 'Kaity Nguyễn, Quang Thắng', 'Tâm lý, Cổ trang', 130, '2023-11-03', 'nguoi-vo-cuoi-cung.jpg', 'Bi kịch tình yêu của người vợ ba trong một gia đình quan lại thời phong kiến.', 'now_showing'),
(20, 'FURIOSA: MỘT SỬ THI MAD MAX', 'George Miller', 'Anya Taylor-Joy, Chris Hemsworth', 'Hành động, Viễn tưởng', 148, '2024-05-24', 'furiosa.jpg', 'Nguồn gốc của Furiosa trước khi trở thành chiến binh huyền thoại trong thế giới hậu tận thế.', 'coming_soon');

INSERT INTO `cinemas` (`id`, `name`, `address`) VALUES
(1, 'CineStar Quốc Thanh', '271 Nguyễn Trãi, Q.1, TP.HCM'),
(2, 'CineStar Hai Bà Trưng', '135 Hai Bà Trưng, Q.3, TP.HCM');
(3, 'CGV Vincom Đồng Khởi', '72 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP.HCM'),
(4, 'CGV Aeon Mall Tân Phú', '30 Bờ Bao Tân Thắng, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM'),
(5, 'Lotte Cinema Landmark 81', 'Vinhomes Central Park, 720A Điện Biên Phủ, Phường 22, Quận Bình Thạnh, TP.HCM'),
(6, 'Galaxy Cinema Nguyễn Du', '116 Nguyễn Du, Phường Bến Thành, Quận 1, TP.HCM'),
(7, 'BHD Star Bitexco', 'Tầng 3-4, Bitexco Financial Tower, 2 Hải Triều, Quận 1, TP.HCM'),
(8, 'Mega GS Cao Thắng', '19 Cao Thắng, Phường 2, Quận 3, TP.HCM'),
(9, 'Beta Cinemas Mỹ Đình', 'Tầng 3, TTTM The Garden, Mỹ Đình, Nam Từ Liêm, Hà Nội'),
(10, 'CGV Vincom Bà Triệu', 'Vincom Center Bà Triệu, 191 Bà Triệu, Hai Bà Trưng, Hà Nội');

INSERT INTO `showtimes` (`id`, `movie_id`, `cinema_id`, `show_date`, `show_time`, `price`) VALUES
(1, 1, 1, CURDATE(), '10:30:00', 65000),
(2, 1, 1, CURDATE(), '19:30:00', 95000),
(3, 1, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(4, 1, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(5, 1, 3, CURDATE(), '10:30:00', 65000),
(6, 1, 3, CURDATE(), '19:30:00', 95000),
(7, 1, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(8, 1, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(9, 2, 1, CURDATE(), '10:30:00', 65000),
(10, 2, 1, CURDATE(), '19:30:00', 95000),
(11, 2, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(12, 2, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(13, 2, 3, CURDATE(), '10:30:00', 65000),
(14, 2, 3, CURDATE(), '19:30:00', 95000),
(15, 2, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(16, 2, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(17, 6, 1, CURDATE(), '10:30:00', 65000),
(18, 6, 1, CURDATE(), '19:30:00', 95000),
(19, 6, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(20, 6, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(21, 6, 3, CURDATE(), '10:30:00', 65000),
(22, 6, 3, CURDATE(), '19:30:00', 95000),
(23, 6, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(24, 6, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(25, 7, 1, CURDATE(), '10:30:00', 65000),
(26, 7, 1, CURDATE(), '19:30:00', 95000),
(27, 7, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(28, 7, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(29, 7, 3, CURDATE(), '10:30:00', 65000),
(30, 7, 3, CURDATE(), '19:30:00', 95000),
(31, 7, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(32, 7, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(33, 12, 1, CURDATE(), '10:30:00', 65000),
(34, 12, 1, CURDATE(), '19:30:00', 95000),
(35, 12, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(36, 12, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(37, 12, 3, CURDATE(), '10:30:00', 65000),
(38, 12, 3, CURDATE(), '19:30:00', 95000),
(39, 12, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(40, 12, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(41, 14, 1, CURDATE(), '10:30:00', 65000),
(42, 14, 1, CURDATE(), '19:30:00', 95000),
(43, 14, 1, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(44, 14, 1, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000),
(45, 14, 3, CURDATE(), '10:30:00', 65000),
(46, 14, 3, CURDATE(), '19:30:00', 95000),
(47, 14, 3, CURDATE() + INTERVAL 1 DAY, '10:30:00', 65000),
(48, 14, 3, CURDATE() + INTERVAL 1 DAY, '19:30:00', 95000);