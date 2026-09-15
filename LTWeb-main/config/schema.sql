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

UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=d1ZHdosjNX8' WHERE id = 1;  -- LẬT MẶT 7: MỘT ĐIỀU ƯỚC
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=Xithigfg7dA' WHERE id = 2;  -- DEADPOOL
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=IkaP0KJWTsQ' WHERE id = 3;  -- NHÀ BÀ NỮ
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=jluSu8Rw6YE&pp=ygURYuG7kSBnacOgIHRyYWlsZXI%3D' WHERE id = 4;  -- BỐ GIÀ
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=qn1t_biQigc&pp=ygUdxJHDoG8gcGjhu58gdsOgIHBpYW5vIHRyYWlsZXI%3D' WHERE id = 5;  -- ĐÀO, PHỞ VÀ PIANO
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=66K9-l0EkE0' WHERE id = 6;  -- EXHUMA: QUẬT MỘ TRÙNG MA
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=5XkgG_AAQs0&pp=ygUpZ29kemlsbGEgeCBrb25nIMSR4bq_IGNo4bq_IG3hu5tpIHRyYWlsZXLSBwkJJAwBhyohjO8%3D' WHERE id = 7;  -- GODZILLA X KONG: ĐẾ CHẾ MỚI
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=_inKs4eeHiI&pp=ygUXa3VuZyBmdSBwYW5kYSA0IHRyYWlsZXI%3D' WHERE id = 8;  -- KUNG FU PANDA 4
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=LEjhY15eCx0&pp=ygUUaW5zaWRlIG91dCAyIHRyYWlsZXI%3D' WHERE id = 9;  -- INSIDE OUT 2
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 10; -- DEADPOOL & WOLVERINE
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=xWh0g4rKGjI&pp=ygUWa-G6uyDEg24gaOG7k24gdHJhaWxlcg%3D%3D' WHERE id = 11; -- KẺ ĂN HỒN
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 12; -- MÓNG VUỐT
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=79BznZKQwIQ&pp=ygUkY8OhaSBnacOhIGPhu6dhIGjhuqFuaCBwaMO6YyB0cmFpbGVy' WHERE id = 13; -- CÁI GIÁ CỦA HẠNH PHÚC
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 14; -- DUNE: HÀNH TINH CÁT 2
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 15; -- WONKA
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 16; -- AQUAMAN VÀ VƯƠNG QUỐC THẤT LẠC
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 17; -- CHỊ CHỊ EM EM 2
UPDATE movies SET trailer_url = 'https://www.youtube.com/watch?v=hktzirCnJmQ&pp=ygUixJHhuqV0IHLhu6tuZyBwaMawxqFuZyBuYW0gdHJhaWxlcg%3D%3D' WHERE id = 18; -- ĐẤT RỪNG PHƯƠNG NAM
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 19; -- NGƯỜI VỢ CUỐI CÙNG
UPDATE movies SET trailer_url = 'DÁN_LINK_YOUTUBE_VÀO_ĐÂY' WHERE id = 20; -- FURIOSA: MỘT SỬ THI MAD MAX

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
(1, 1, 1, CURDATE(), '09:30:00', 55000),
(2, 1, 2, CURDATE(), '13:00:00', 65000),
(3, 1, 3, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(4, 1, 4, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(5, 2, 5, CURDATE(), '09:30:00', 55000),
(6, 2, 6, CURDATE(), '13:00:00', 65000),
(7, 2, 7, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(8, 2, 8, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(9, 3, 9, CURDATE(), '09:30:00', 55000),
(10, 3, 10, CURDATE(), '13:00:00', 65000),
(11, 3, 1, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(12, 3, 2, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(13, 4, 3, CURDATE(), '09:30:00', 55000),
(14, 4, 4, CURDATE(), '13:00:00', 65000),
(15, 4, 5, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(16, 4, 6, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(17, 5, 7, CURDATE(), '09:30:00', 55000),
(18, 5, 8, CURDATE(), '13:00:00', 65000),
(19, 5, 9, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(20, 5, 10, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(21, 6, 1, CURDATE(), '09:30:00', 55000),
(22, 6, 2, CURDATE(), '13:00:00', 65000),
(23, 6, 3, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(24, 6, 4, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(25, 7, 5, CURDATE(), '09:30:00', 55000),
(26, 7, 6, CURDATE(), '13:00:00', 65000),
(27, 7, 7, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(28, 7, 8, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(29, 8, 9, CURDATE(), '09:30:00', 55000),
(30, 8, 10, CURDATE(), '13:00:00', 65000),
(31, 8, 1, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(32, 8, 2, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(33, 9, 3, CURDATE(), '09:30:00', 55000),
(34, 9, 4, CURDATE(), '13:00:00', 65000),
(35, 9, 5, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(36, 9, 6, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(37, 10, 7, CURDATE(), '09:30:00', 55000),
(38, 10, 8, CURDATE(), '13:00:00', 65000),
(39, 10, 9, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(40, 10, 10, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(41, 11, 1, CURDATE(), '09:30:00', 55000),
(42, 11, 2, CURDATE(), '13:00:00', 65000),
(43, 11, 3, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(44, 11, 4, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(45, 12, 5, CURDATE(), '09:30:00', 55000),
(46, 12, 6, CURDATE(), '13:00:00', 65000),
(47, 12, 7, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(48, 12, 8, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(49, 13, 9, CURDATE(), '09:30:00', 55000),
(50, 13, 10, CURDATE(), '13:00:00', 65000),
(51, 13, 1, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(52, 13, 2, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(53, 14, 3, CURDATE(), '09:30:00', 55000),
(54, 14, 4, CURDATE(), '13:00:00', 65000),
(55, 14, 5, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(56, 14, 6, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(57, 15, 7, CURDATE(), '09:30:00', 55000),
(58, 15, 8, CURDATE(), '13:00:00', 65000),
(59, 15, 9, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(60, 15, 10, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(61, 16, 1, CURDATE(), '09:30:00', 55000),
(62, 16, 2, CURDATE(), '13:00:00', 65000),
(63, 16, 3, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(64, 16, 4, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(65, 17, 5, CURDATE(), '09:30:00', 55000),
(66, 17, 6, CURDATE(), '13:00:00', 65000),
(67, 17, 7, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(68, 17, 8, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(69, 18, 9, CURDATE(), '09:30:00', 55000),
(70, 18, 10, CURDATE(), '13:00:00', 65000),
(71, 18, 1, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(72, 18, 2, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(73, 19, 3, CURDATE(), '09:30:00', 55000),
(74, 19, 4, CURDATE(), '13:00:00', 65000),
(75, 19, 5, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(76, 19, 6, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000),
(77, 20, 7, CURDATE(), '09:30:00', 55000),
(78, 20, 8, CURDATE(), '13:00:00', 65000),
(79, 20, 9, CURDATE() + INTERVAL 1 DAY, '17:15:00', 85000),
(80, 20, 10, CURDATE() + INTERVAL 1 DAY, '20:30:00', 95000);