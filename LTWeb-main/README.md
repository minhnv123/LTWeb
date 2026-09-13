```text
# 🎬 CINESTAR - HỆ THỐNG ĐẶT VÉ XEM PHIM TRỰC TUYẾN

> **Đồ Án Môn Học:** Lập Trình Web (PHP & MySQL)  
> **Công Nghệ Sử Dụng:** PHP PDO, MySQL/MariaDB, JavaScript, TailwindCSS, FontAwesome, jQuery AJAX.

---

## 📌 GIỚI THIỆU ĐỒ ÁN

Hệ thống **CineStar** là website hỗ trợ khách hàng tìm kiếm phim, chọn suất chiếu, sơ đồ chọn ghế tương tác trực quan, thanh toán vé xem phim và bắp nước trực tuyến. Đồng thời cung cấp không gian quản trị (**Admin Dashboard**) giúp nhà quản lý dễ dàng quản lý phim, rạp chiếu, lịch chiếu, doanh thu và soát vé.

---

## ✨ TÍNH NĂNG CHÍNH

### 👤 1. Giao Diện Khách Hàng (Client Portal)
- 🏠 **Trang chủ:** Banner trình chiếu, danh sách Phim Đang Chiếu và Phim Sắp Chiếu.
- 🔍 **Tìm kiếm AJAX Realtime:** Tìm kiếm tên phim tức thì không cần tải lại trang.
- 🎯 **Bộ lọc thông minh:** Lọc phim theo thể loại, ngày chiếu.
- 🎬 **Chi tiết phim:** Xem nội dung, Đạo diễn, Diễn viên, Trailer Youtube nhúng trực tiếp.
- 🪑 **Sơ đồ chọn ghế (Seat Map):** Chọn ghế Thường / Ghế VIP tương tác theo thời gian thực, tự động khóa các ghế đã được đặt trước.
- 🍿 **Combo Bắp Nước:** Tùy chọn mua kèm combo bắp nước tự động tính tổng tiền.
- 🔐 **Xác minh Captcha:** Hệ thống Đăng ký / Đăng nhập có mã xác minh Captcha chống Bot tự động.
- 🎫 **Hoàn tất & Render Mã QR:** Hiện vé xem phim kèm mã QR Code độc bản dùng để soát vé.
- 📜 **Lịch sử đặt vé:** Quản lý danh sách các vé đã mua.

---

### 🛡️ 2. Giao Diện Quản Trị Viên (Admin Portal)
- 📊 **Bảng điều khiển (Dashboard):** Thống kê tổng số phim, suất chiếu, đơn đặt vé, tổng doanh thu và đơn đặt vé mới nhất.
- 🎥 **Quản lý Phim (CRUD):** Xem danh sách, thêm phim mới, upload poster (.jpg, .png, .webp), cập nhật thông tin và xóa phim.
- 🏛️ **Quản lý Rạp Chiếu (CRUD):** Thêm, sửa, xóa các rạp chiếu phim trong hệ thống và địa chỉ.
- 📅 **Quản lý Lịch Chiếu (CRUD):** Tạo lịch chiếu mới (gắn Phim với Rạp chiếu, Ngày/Giờ chiếu và cài đặt Giá vé).
- 🎟️ **Trang Soát Vé (Check-in):** Nhập mã vé hoặc quét mã QR Code để xác nhận soát vé cho khách hàng vào rạp.

---

## 🔑 TÀI KHOẢN DÙNG THỬ (DEMO ACCOUNTS)

| Vai Trò | Email | Mật Khẩu | Trang Đăng Nhập |
| :--- | :--- | :--- | :--- |
| **Quản Trị Viên (Admin)** | `admin@gmail.com` | `123456` | `/admin/admin-login.php` hoặc `/login.php` |
| **Khách Hàng (User)** | `user@gmail.com` | `123456` | `/login.php` |

---

## 📁 CẤU TRÚC THƯ MỤC DỰ ÁN

```text
/LTWeb-main
├── /admin                         <-- HỆ THỐNG QUẢN TRỊ (ADMIN)
│   ├── header.php                 <-- Master Header & Sidebar Admin
│   ├── footer.php                 <-- Master Footer Admin
│   ├── index.php                  <-- Admin Dashboard (Thống kê tổng quan & Doanh thu)
│   ├── movies_list.php            <-- Quản lý Phim (Danh sách & Xóa)
│   ├── movie_add.php              <-- Form Thêm phim mới & Upload poster
│   ├── movie_edit.php             <-- Form Chỉnh sửa thông tin phim
│   ├── cinemas_list.php           <-- Quản lý Rạp chiếu (Danh sách & Xóa)
│   ├── cinema_add.php             <-- Form Thêm rạp chiếu mới
│   ├── cinema_edit.php            <-- Form Cập nhật thông tin rạp
│   ├── showtimes_list.php         <-- Quản lý Lịch chiếu (Danh sách & Xóa)
│   ├── showtime_add.php           <-- Form Tạo suất chiếu mới
│   ├── showtime_edit.php          <-- Form Cập nhật suất chiếu
│   ├── check_ticket.php           <-- Trang Soát vé (Check-in mã vé / QR Code)
│   └── admin-login.php            <-- Trang đăng nhập riêng cho Admin
│
├── /config                        <-- CẤU HÌNH HỆ THỐNG
│   ├── db.php                     <-- Cấu hình kết nối PDO MySQL (Tự động nhận dạng cổng 3306/3333/3307)
│   └── schema.sql                 <-- CSDL MySQL khởi tạo bảng & dữ liệu mẫu
│
├── /uploads                       <-- LƯU TRỮ HÌNH ẢNH MEDIA
│   └── (poster_files...)          <-- Chứa ảnh Poster phim được Upload
│
├── header.php                     <-- [Master Header Client] Thanh Menu, Logo, Search AJAX
├── footer.php                     <-- [Master Footer Client] Thông tin rạp & Liên hệ
├── index.php                      <-- TRANG CHỦ CLIENT (Danh sách Phim Đang/Sắp Chiếu)
├── movie_detail.php               <-- Trang Chi tiết Phim & Lịch chiếu
├── ajax_search.php                <-- Xử lý gợi ý tìm kiếm realtime jQuery AJAX
├── booking.php                    <-- Trang Sơ đồ Chọn ghế (Seat Map) & Bắp nước
├── checkout.php                   <-- Trang Thanh toán & Lưu đơn đặt vé
├── ticket_success.php             <-- Trang Hoàn tất đặt vé & Tạo Mã QR Code
├── history.php                    <-- Lịch sử đặt vé của người dùng
├── profile.php                    <-- Thông tin cá nhân người dùng
├── captcha.php                    <-- Xử lý sinh phép tính Captcha xác minh anti-bot
├── login.php                      <-- Trang Đăng nhập Khách hàng / Admin
├── register.php                   <-- Trang Đăng ký Tài khoản
└── logout.php                     <-- Đăng xuất hệ thống
```

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN

1. **Sao chép thư mục dự án:**
   Đặt thư mục `LTWeb-main` vào thư mục `htdocs` của XAMPP (`C:\xampp\htdocs\LTWeb-main`).

2. **Khởi chạy XAMPP:**
   Mở **XAMPP Control Panel**, nhấn **Start** cả hai dịch vụ **Apache** và **MySQL**.

3. **Cấu hình Cơ sở dữ liệu:**
   - Mở `http://localhost/phpmyadmin/`
   - Tạo cơ sở dữ liệu có tên là `cinema` (Collation: `utf8mb4_unicode_ci`).
   - Import tệp `config/schema.sql` vào cơ sở dữ liệu `cinema`.

4. **Truy cập ứng dụng:**
   Mở trình duyệt và truy cập: **[http://localhost/LTWeb-main/](http://localhost/LTWeb-main/)**

---

### 📝 GHI CHÚ
- Đã cấu hình PDO kết nối linh hoạt cổng MySQL (`3306`, `3333`, `3307`).
- File `config/schema.sql` đã có sẵn dữ liệu phim, rạp chiếu, suất chiếu và tài khoản admin/user mặc định.

```

> [!IMPORTANT]
> **GHI CHÚ QUAN TRỌNG**
> - Tất cả mọi người **DÙNG CHUNG** Cơ sở dữ liệu cấu hình 100% trong file `cinema.sql`.
> - **KHÔNG** tự động đổi tên tệp, bảng tên hoặc dữ liệu cột tên trên máy cá nhân.
> - Hằng ngày trước khi làm nhớ `git pull` và làm xong việc nhớ `git push`.
