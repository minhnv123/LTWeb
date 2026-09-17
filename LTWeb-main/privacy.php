<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'header.php';
?>

<div class="bg-slate-950 text-slate-300 min-h-screen py-10 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-4xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-10 shadow-2xl">
        
        <!-- TIÊU ĐỀ TRANG -->
        <div class="border-b border-slate-800 pb-6 mb-8 text-center sm:text-left">
            <h1 class="text-2xl sm:text-3xl font-black text-rose-500 uppercase tracking-wider flex items-center gap-3 justify-center sm:justify-start">
                <i class="fa-solid fa-user-shield"></i> Chính Sách Bảo Mật Thông Tin
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Hệ thống Rạp chiếu phim CineStar (CineStar Cinema System Vietnam)
            </p>
        </div>

        <!-- NỘI DUNG CHÍNH SÁCH -->
        <div class="space-y-8 text-sm leading-relaxed text-slate-300">
            
            <!-- 1. MỤC ĐÍCH VÀ PHẠM VI -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 1. Mục đích và phạm vi thu thập
                </h2>
                <p>
                    Việc thu thập dữ liệu chủ yếu trên hệ thống đặt vé trực tuyến <strong class="text-rose-400">CineStar</strong> bao gồm: Email, số điện thoại, họ tên, mật khẩu đăng nhập, thông tin đặt vé của khách hàng (thành viên). Đây là các thông tin bắt buộc khi đăng ký sử dụng dịch vụ để hệ thống liên hệ xác nhận và gửi mã vé trực tuyến nhằm đảm bảo quyền lợi cho người tiêu dùng.
                </p>
                <p>
                    Trong quá trình giao dịch thanh toán tại website CineStar, chúng tôi chỉ lưu giữ thông tin chi tiết về đơn đặt vé đã thanh toán, các thông tin về số tài khoản ngân hàng hoặc thẻ thanh toán của thành viên sẽ <strong class="text-white">không được lưu giữ</strong> trên máy chủ của chúng tôi.
                </p>
                <p>
                    Các thành viên có trách nhiệm bảo mật và lưu giữ mọi hoạt động sử dụng dịch vụ dưới tên đăng ký, mật khẩu và hộp thư điện tử của mình. Ngoài ra, thành viên có trách nhiệm thông báo kịp thời cho Ban quản lý CineStar về những hành vi sử dụng trái phép, lạm dụng hoặc vi phạm bảo mật tài khoản.
                </p>
            </section>

            <!-- 2. PHẠM VI SỬ DỤNG -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 2. Phạm vi sử dụng thông tin
                </h2>
                <p>CineStar sử dụng thông tin thành viên cung cấp để:</p>
                <ul class="list-disc list-inside space-y-1 pl-2 text-slate-400">
                    <li>Cung cấp các dịch vụ đặt vé và thanh toán đến thành viên;</li>
                    <li>Gửi mã vé xem phim (Booking Code) và thông báo xác nhận giao dịch;</li>
                    <li>Ngăn ngừa các hoạt động phá hủy tài khoản người dùng hoặc các hoạt động giả mạo thành viên;</li>
                    <li>Liên lạc và giải quyết với thành viên trong những trường hợp đặc biệt;</li>
                    <li>Không sử dụng thông tin cá nhân ngoài mục đích xác nhận và liên hệ liên quan đến giao dịch tại CineStar.</li>
                </ul>
                <p>
                    <strong class="text-white">Trường hợp pháp lý:</strong> Công ty có trách nhiệm hợp tác cung cấp thông tin cá nhân thành viên khi có yêu cầu bằng văn bản từ cơ quan tư pháp (Viện kiểm sát, tòa án, cơ quan công an điều tra).
                </p>
            </section>

            <!-- 3. THỜI GIAN LƯU TRỮ -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 3. Thời gian lưu trữ thông tin
                </h2>
                <p>
                    Dữ liệu cá nhân của thành viên sẽ được lưu trữ cho đến khi có yêu cầu hủy bỏ từ khách hàng hoặc tự khách hàng đăng nhập thực hiện hủy bỏ. Trong mọi trường hợp còn lại, thông tin cá nhân khách hàng sẽ được bảo mật tuyệt đối trên máy chủ của CineStar.
                </p>
            </section>

            <!-- 4. ĐƠN VỊ TIẾP CẬN -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 4. Đối tượng tiếp cận thông tin
                </h2>
                <ul class="list-disc list-inside space-y-1 pl-2 text-slate-400">
                    <li>Ban quản lý hệ thống CineStar và các bộ phận hỗ trợ chăm sóc khách hàng.</li>
                    <li>Các cơ quan nhà nước có thẩm quyền khi có yêu cầu pháp lý cụ thể.</li>
                </ul>
            </section>

            <!-- 5. ĐƠN VỊ QUẢN LÝ -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 5. Địa chỉ đơn vị quản lý thông tin
                </h2>
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-1 text-slate-300">
                    <p class="font-bold text-rose-400">CINESTAR CINEMA SYSTEM VIETNAM</p>
                    <p><i class="fa-solid fa-location-dot text-rose-500 w-5"></i> Địa chỉ: 271 Nguyễn Trãi, Phường Nguyễn Cư Trinh, Quận 1, TP. Hồ Chí Minh</p>
                    <p><i class="fa-solid fa-envelope text-rose-500 w-5"></i> Email hỗ trợ: cskh@cinestar.com.vn</p>
                    <p><i class="fa-solid fa-phone text-rose-500 w-5"></i> Hotline: 1900 6017</p>
                </div>
            </section>

            <!-- 6. PHƯƠNG THỨC CHỈNH SỬA -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 6. Phương thức tiếp cận & chỉnh sửa dữ liệu
                </h2>
                <p>
                    Thành viên có quyền tự kiểm tra, cập nhật, điều chỉnh hoặc hủy bỏ thông tin cá nhân của mình bằng cách đăng nhập vào tài khoản trên website CineStar và truy cập vào mục <strong class="text-white">"Thông tin tài khoản"</strong> để thực hiện chỉnh sửa.
                </p>
            </section>

            <!-- 7. CAM KẾT BẢO MẬT -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 7. Cam kết bảo mật thông tin
                </h2>
                <p>
                    Thông tin cá nhân của thành viên trên hệ thống CineStar được cam kết bảo mật tuyệt đối. Việc thu thập và sử dụng thông tin chỉ được thực hiện khi có sự đồng ý của khách hàng đó.
                </p>
                <p>
                    Trong trường hợp máy chủ lưu trữ thông tin bị tấn công dẫn đến mất mát dữ liệu, CineStar sẽ có trách nhiệm thông báo vụ việc cho cơ quan chức năng điều tra xử lý kịp thời và thông báo cho thành viên được biết.
                </p>
            </section>

            <!-- 8. TIẾP NHẬN KHIẾU NẠI -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> 8. Cơ chế tiếp nhận & giải quyết khiếu nại
                </h2>
                <p>
                    Khi phát hiện thông tin cá nhân bị sử dụng sai mục đích hoặc phạm vi, khách hàng có thể gửi khiếu nại qua email <strong class="text-rose-400">cskh@cinestar.com.vn</strong> hoặc gọi trực tiếp đến Hotline <strong class="text-rose-400">1900 6017</strong>. 
                </p>
                <p>
                    Trong thời hạn 10 ngày làm việc kể từ ngày nhận được khiếu nại, Ban quản lý CineStar có trách nhiệm tìm nguyên nhân và đưa ra phương án giải quyết cụ thể cho khách hàng.
                </p>
            </section>

        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>