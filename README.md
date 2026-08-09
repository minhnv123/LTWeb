```text
/CineStar
│
├── /admin                         <-- KHÔNG GIAN QUẢN TRỊ VIÊN (ADMIN)
│   ├── config.php                 <-- File cấu hình kết nối CSDL MySQL cho trang Admin
│   ├── index.php                  <-- Admin Dashboard (Thống kê tổng quan phim, doanh thu)
│   ├── movies_list.php            <-- Danh sách Phim & Chức năng Xóa phim (CRUD)
│   ├── movie_add.php              <-- Form Thêm phim mới & Upload poster (.jpg, .png)
│   ├── movie_edit.php             <-- Form Cập nhật thông tin & Thay đổi poster phim
│   ├── showtimes_list.php         <-- Quản lý Lịch chiếu (Gắn Phim với Phòng chiếu, Ngày/Giờ)
│   ├── showtime_add.php           <-- Form Tạo suất chiếu mới & Phân trang danh sách
│   └── check_ticket.php           <-- Trang Soát vé (Nhân viên nhập mã vé/quét QR để check-in)
│
├── /uploads                       <-- THƯ MỤC LƯU TRỮ TÀI NGUYÊN MEDIA
│   └── (poster_files...)          <-- Chứa tất cả ảnh Poster phim được Upload từ trang Admin
│
├── /css                           <-- Chứa các file định dạng giao diện (Style sheets)
├── /js                            <-- Chứa các file xử lý Javascript/jQuery
│
├── config.php                     <-- File cấu hình kết nối CSDL MySQL dùng chung cho Client
├── header.php                     <-- [Master Header] Thanh Menu, Logo, Thanh tìm kiếm, Đăng nhập
├── footer.php                     <-- [Master Footer] Thông tin rạp, liên hệ, chính sách
│
├── index.php                      <-- TRANG CHỦ CLIENT (Banner Slider, Danh sách Phim chiếu/Sắp chiếu)
├── movie_detail.php               <-- Trang Chi tiết Phim (Nội dung phim, Trailer Youtube, chọn Suất chiếu)
├── ajax_search.php                <-- Xử lý tìm kiếm phim tự động theo thời gian thực bằng jQuery AJAX
│
├── booking.php                    <-- Trang Sơ đồ Chọn ghế (Seat Map) & Tính tiền bắp nước tự động
├── checkout.php                   <-- Trang Thanh toán & Lưu đơn đặt vé vào CSDL
└── ticket_success.php             <-- Trang Hoàn tất (Hiện thông tin vé & Render mã QR Code)
```

> [!IMPORTANT]
> **GHI CHÚ QUAN TRỌNG**
> - Tất cả mọi người **DÙNG CHUNG** Cơ sở dữ liệu cấu hình 100% trong file `cinema.sql`.
> - **KHÔNG** tự động đổi tên tệp, bảng tên hoặc dữ liệu cột tên trên máy cá nhân.
> - Hằng ngày trước khi làm nhớ `git pull` và làm xong việc nhớ `git push`.
