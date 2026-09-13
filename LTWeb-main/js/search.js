$(function () {
    let searchTimer = null;

    // Với mỗi ô input tìm kiếm (desktop & mobile), tìm dropdown gợi ý đi kèm ngay sau nó
    $('.js-search-input').on('input focus', function () {
        const $input = $(this);
        const $results = $input.closest('form').find('.js-search-results');
        const keyword = $input.val().trim();

        clearTimeout(searchTimer);

        if (keyword.length < 1) {
            $results.addClass('hidden').empty();
            return;
        }

        // Debounce 300ms để tránh gọi AJAX liên tục khi gõ nhanh
        searchTimer = setTimeout(function () {
            $results.html('<div class="px-4 py-4 text-center text-sm text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang tìm...</div>').removeClass('hidden');

            $.ajax({
                url: 'ajax_search.php',
                method: 'GET',
                data: { q: keyword },
                success: function (html) {
                    $results.html(html).removeClass('hidden');
                },
                error: function () {
                    $results.html('<div class="px-4 py-4 text-center text-sm text-rose-400">Có lỗi xảy ra, vui lòng thử lại.</div>');
                }
            });
        }, 300);
    });

    // Bấm ra ngoài thì ẩn dropdown gợi ý
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.js-search-input, .js-search-results').length) {
            $('.js-search-results').addClass('hidden');
        }
    });

    // Nhấn phím Esc để đóng dropdown
    $('.js-search-input').on('keydown', function (e) {
        if (e.key === 'Escape') {
            $(this).closest('form').find('.js-search-results').addClass('hidden');
        }
    });
});
