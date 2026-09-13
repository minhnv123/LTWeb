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

INSERT INTO `cinemas` (`id`, `name`, `address`) VALUES
(1, 'CineStar Quốc Thanh', '271 Nguyễn Trãi, Q.1, TP.HCM'),
(2, 'CineStar Hai Bà Trưng', '135 Hai Bà Trưng, Q.3, TP.HCM');