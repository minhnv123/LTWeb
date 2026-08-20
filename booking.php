<?php
ob_start();

require_once 'config/dp.php';
include_once 'header.php';

/*
|--------------------------------------------------------------------------
| BOOKING PAGE
|--------------------------------------------------------------------------
| Tạm thời sử dụng dữ liệu mẫu.
| Sau khi có cấu trúc database showtimes / seats / bookings /
| booking_details, phần này sẽ được thay bằng truy vấn PDO.
|--------------------------------------------------------------------------
*/

// ---------------------------------------------------------
// DỮ LIỆU MẪU
// ---------------------------------------------------------

$movie = [
    'title' => 'Avengers: Endgame',
    'showtime' => '19:30',
    'date' => '19/08/2026',
    'room' => 'Cinema 01'
];

// Ghế đã bán
$soldSeats = [
    'A5',
    'A6',
    'B7',
    'B8',
    'C4',
    'D10',
    'D11',
    'F5'
];

// Giá ghế
$standardPrice = 75000;
$vipPrice = 95000;

// Bắp nước
$foods = [
    [
        'id' => 1,
        'name' => 'Combo Solo',
        'description' => '1 Bắp + 1 Nước',
        'price' => 65000
    ],
    [
        'id' => 2,
        'name' => 'Combo Couple',
        'description' => '1 Bắp lớn + 2 Nước',
        'price' => 95000
    ],
    [
        'id' => 3,
        'name' => 'Bắp rang bơ',
        'description' => 'Bắp rang bơ size L',
        'price' => 50000
    ],
    [
        'id' => 4,
        'name' => 'Nước ngọt',
        'description' => 'Pepsi / Coca / 7Up',
        'price' => 30000
    ]
];

$rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$seatsPerRow = 12;

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- =====================================================
         MOVIE / SHOWTIME INFORMATION
    ====================================================== -->

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-6 shadow-xl">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-slate-100">
                    <?= htmlspecialchars($movie['title']) ?>
                </h1>

                <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-400">

                    <span>
                        📅
                        <?= htmlspecialchars($movie['date']) ?>
                    </span>

                    <span>
                        🕐
                        <?= htmlspecialchars($movie['showtime']) ?>
                    </span>

                </div>

            </div>

            <div class="inline-flex items-center gap-2
                        bg-amber-500/10
                        border border-amber-500/30
                        text-amber-400
                        px-4 py-2
                        rounded-xl
                        text-sm font-semibold">

                🎬
                <?= htmlspecialchars($movie['room']) ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MAIN BOOKING LAYOUT
    ====================================================== -->

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">


        <!-- =================================================
             LEFT CONTENT
        ================================================== -->

        <div class="xl:col-span-2 space-y-6">


            <!-- =============================================
                 SEAT MAP
            ============================================== -->

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl">

                <div class="flex items-center justify-between mb-7">

                    <div>

                        <h2 class="text-xl font-bold text-slate-100">
                            Chọn ghế
                        </h2>

                        <p class="text-sm text-slate-400 mt-1">
                            Chọn ghế bạn muốn sử dụng
                        </p>

                    </div>

                </div>


                <!-- SCREEN -->

                <div class="flex flex-col items-center mb-10">

                    <div class="
                        w-3/4
                        max-w-2xl
                        h-2
                        bg-slate-200
                        rounded-full
                        shadow-[0_8px_25px_rgba(248,250,252,0.25)]
                    "></div>

                    <span class="text-xs text-slate-500 mt-3 tracking-[0.3em]">
                        MÀN HÌNH
                    </span>

                </div>


                <!-- SEAT MAP -->

                <div class="space-y-3 overflow-x-auto pb-2">

                    <?php foreach ($rows as $row): ?>

                        <div class="flex items-center justify-center gap-1.5 sm:gap-2 min-w-[650px]">

                            <!-- ROW LABEL -->

                            <div class="w-6 text-center text-sm font-bold text-slate-400">
                                <?= $row ?>
                            </div>


                            <?php for ($i = 1; $i <= $seatsPerRow; $i++): ?>

                                <?php

                                $seatCode = $row . $i;

                                $isSold = in_array($seatCode, $soldSeats);

                                /*
                                 * E-H là ghế VIP trong dữ liệu mẫu
                                 */
                                $isVip = in_array($row, ['E', 'F', 'G', 'H']);

                                $price = $isVip
                                    ? $vipPrice
                                    : $standardPrice;

                                ?>

                                <button
                                    type="button"
                                    class="
                                        seat
                                        w-9
                                        h-8
                                        sm:w-10
                                        sm:h-9
                                        rounded-lg
                                        text-[10px]
                                        sm:text-xs
                                        font-semibold
                                        border
                                        transition-all
                                        duration-200

                                        <?php if ($isSold): ?>

                                            bg-rose-600
                                            border-rose-600
                                            text-white
                                            cursor-not-allowed
                                            opacity-80

                                        <?php elseif ($isVip): ?>

                                            bg-slate-800
                                            border-amber-500/70
                                            text-slate-200
                                            hover:bg-amber-500/20
                                            hover:border-amber-500
                                            hover:-translate-y-0.5

                                        <?php else: ?>

                                            bg-slate-700
                                            border-slate-600
                                            text-slate-200
                                            hover:bg-rose-500/20
                                            hover:border-rose-500
                                            hover:-translate-y-0.5

                                        <?php endif; ?>
                                    "
                                    data-seat="<?= $seatCode ?>"
                                    data-type="<?= $isVip ? 'vip' : 'standard' ?>"
                                    data-price="<?= $price ?>"
                                    <?= $isSold ? 'disabled' : '' ?>
                                >
                                    <?= $seatCode ?>
                                </button>

                            <?php endfor; ?>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- LEGEND -->

                <div class="
                    flex
                    flex-wrap
                    justify-center
                    gap-5
                    mt-8
                    pt-5
                    border-t
                    border-slate-800
                ">

                    <!-- Empty -->

                    <div class="flex items-center gap-2 text-sm text-slate-400">

                        <span class="w-5 h-5 rounded-md bg-slate-700 border border-slate-600"></span>

                        Ghế trống

                    </div>


                    <!-- Selected -->

                    <div class="flex items-center gap-2 text-sm text-slate-400">

                        <span class="w-5 h-5 rounded-md bg-amber-500"></span>

                        Đang chọn

                    </div>


                    <!-- Sold -->

                    <div class="flex items-center gap-2 text-sm text-slate-400">

                        <span class="w-5 h-5 rounded-md bg-rose-600"></span>

                        Đã bán

                    </div>


                    <!-- VIP -->

                    <div class="flex items-center gap-2 text-sm text-slate-400">

                        <span class="w-5 h-5 rounded-md bg-slate-800 border border-amber-500/70"></span>

                        Ghế VIP

                    </div>

                </div>

            </div>


            <!-- =================================================
                 FOOD & DRINK
            ================================================== -->

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-xl font-bold text-slate-100">
                        🍿 Bắp & Nước
                    </h2>

                    <p class="text-sm text-slate-400 mt-1">
                        Thêm bắp nước để thưởng thức trong suất chiếu
                    </p>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <?php foreach ($foods as $food): ?>

                        <div class="
                            food-item
                            bg-slate-950
                            border
                            border-slate-800
                            rounded-xl
                            p-4
                            transition-all
                            duration-200
                            hover:border-rose-500/50
                        ">

                            <div class="flex justify-between gap-3">

                                <div class="flex-1">

                                    <h3 class="font-semibold text-slate-100">
                                        <?= htmlspecialchars($food['name']) ?>
                                    </h3>

                                    <p class="text-xs text-slate-400 mt-1">
                                        <?= htmlspecialchars($food['description']) ?>
                                    </p>

                                    <p class="text-amber-400 font-semibold text-sm mt-2">
                                        <?= number_format($food['price'], 0, ',', '.') ?> đ
                                    </p>

                                </div>


                                <!-- QUANTITY -->

                                <div class="flex items-center gap-2 self-center">

                                    <button
                                        type="button"
                                        class="
                                            food-minus
                                            w-8
                                            h-8
                                            rounded-lg
                                            bg-slate-800
                                            border
                                            border-slate-700
                                            text-slate-300
                                            hover:bg-rose-600
                                            hover:border-rose-600
                                            transition
                                        "
                                        data-id="<?= $food['id'] ?>"
                                    >
                                        −
                                    </button>


                                    <span
                                        id="quantity-<?= $food['id'] ?>"
                                        class="
                                            w-6
                                            text-center
                                            text-slate-100
                                            font-semibold
                                        "
                                    >
                                        0
                                    </span>


                                    <button
                                        type="button"
                                        class="
                                            food-plus
                                            w-8
                                            h-8
                                            rounded-lg
                                            bg-slate-800
                                            border
                                            border-slate-700
                                            text-slate-300
                                            hover:bg-rose-600
                                            hover:border-rose-600
                                            transition
                                        "
                                        data-id="<?= $food['id'] ?>"
                                    >
                                        +
                                    </button>

                                </div>

                            </div>


                            <input
                                type="hidden"
                                id="food-price-<?= $food['id'] ?>"
                                value="<?= $food['price'] ?>"
                            >

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- =================================================
             RIGHT - ORDER SUMMARY
        ================================================== -->

        <div>

            <div class="
                bg-slate-900
                border
                border-slate-800
                rounded-2xl
                p-5
                sm:p-6
                shadow-xl
                xl:sticky
                xl:top-24
            ">

                <h2 class="
                    text-xl
                    font-bold
                    text-slate-100
                    pb-4
                    border-b
                    border-slate-800
                ">
                    Thông tin đặt vé
                </h2>


                <!-- SELECTED SEATS -->

                <div class="py-5 border-b border-slate-800">

                    <div class="flex justify-between gap-4 mb-3">

                        <span class="text-sm text-slate-400">
                            Số ghế
                        </span>

                        <span
                            id="seat-count"
                            class="font-semibold text-slate-100"
                        >
                            0
                        </span>

                    </div>


                    <div class="flex justify-between gap-4">

                        <span class="text-sm text-slate-400">
                            Ghế đã chọn
                        </span>

                        <span
                            id="selected-seats"
                            class="
                                text-sm
                                font-semibold
                                text-amber-400
                                text-right
                                max-w-[200px]
                            "
                        >
                            Chưa chọn
                        </span>

                    </div>

                </div>


                <!-- SEAT PRICE -->

                <div class="py-5 border-b border-slate-800">

                    <div class="flex justify-between">

                        <span class="text-sm text-slate-400">
                            Tiền ghế
                        </span>

                        <span
                            id="seat-total"
                            class="font-semibold text-slate-100"
                        >
                            0 đ
                        </span>

                    </div>

                </div>


                <!-- FOOD PRICE -->

                <div class="py-5 border-b border-slate-800">

                    <div class="flex justify-between">

                        <span class="text-sm text-slate-400">
                            Bắp & nước
                        </span>

                        <span
                            id="food-total"
                            class="font-semibold text-slate-100"
                        >
                            0 đ
                        </span>

                    </div>

                </div>


                <!-- TOTAL -->

                <div class="py-5">

                    <div class="flex items-center justify-between gap-4">

                        <span class="text-slate-400">
                            Tổng cộng
                        </span>

                        <span
                            id="grand-total"
                            class="text-2xl font-bold text-rose-500"
                        >
                            0 đ
                        </span>

                    </div>

                </div>


                <!-- FORM -->

                <form
                    method="POST"
                    action="checkout.php"
                    id="booking-form"
                >

                    <!--
                        Sau này showtime_id sẽ lấy từ movie_detail.php
                    -->

                    <input
                        type="hidden"
                        name="showtime_id"
                        value="1"
                    >


                    <input
                        type="hidden"
                        name="selected_seats"
                        id="selected-seats-input"
                        value=""
                    >


                    <input
                        type="hidden"
                        name="foods"
                        id="foods-input"
                        value=""
                    >


                    <input
                        type="hidden"
                        name="total_amount"
                        id="total-input"
                        value="0"
                    >


                    <button
                        type="submit"
                        id="continue-btn"
                        disabled
                        class="
                            w-full
                            py-3
                            rounded-xl
                            bg-rose-600
                            hover:bg-rose-700
                            disabled:bg-slate-700
                            disabled:text-slate-500
                            disabled:cursor-not-allowed
                            text-white
                            font-semibold
                            shadow-lg
                            shadow-rose-600/20
                            transition-all
                        "
                    >
                        Tiếp tục thanh toán
                    </button>

                </form>


                <p class="text-xs text-slate-500 text-center mt-4">
                    Vui lòng chọn ít nhất một ghế để tiếp tục.
                </p>

            </div>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| BOOKING JAVASCRIPT
|--------------------------------------------------------------------------
*/

let selectedSeats = {};
let foodQuantities = {};


/*
|--------------------------------------------------------------------------
| FORMAT MONEY
|--------------------------------------------------------------------------
*/

function formatMoney(number) {

    return new Intl.NumberFormat('vi-VN').format(number) + ' đ';

}


/*
|--------------------------------------------------------------------------
| SEAT SELECTION
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.seat:not(:disabled)').forEach(function(seat) {

    seat.addEventListener('click', function() {

        const seatCode = this.dataset.seat;
        const seatType = this.dataset.type;
        const price = parseInt(this.dataset.price);


        /*
        | Ghế đang chọn → bỏ chọn
        */

        if (this.classList.contains('selected')) {

            this.classList.remove(
                'selected',
                'bg-amber-500',
                'border-amber-500',
                'text-slate-950',
                'shadow-lg',
                'shadow-amber-500/20'
            );


            /*
            | Trả lại màu ban đầu
            */

            if (seatType === 'vip') {

                this.classList.add(
                    'bg-slate-800',
                    'border-amber-500/70',
                    'text-slate-200'
                );

            } else {

                this.classList.add(
                    'bg-slate-700',
                    'border-slate-600',
                    'text-slate-200'
                );

            }


            delete selectedSeats[seatCode];

        }

        /*
        | Ghế chưa chọn → chọn
        */

        else {

            this.classList.remove(
                'bg-slate-700',
                'bg-slate-800',
                'border-slate-600',
                'border-amber-500/70',
                'text-slate-200'
            );


            this.classList.add(
                'selected',
                'bg-amber-500',
                'border-amber-500',
                'text-slate-950',
                'shadow-lg',
                'shadow-amber-500/20'
            );


            selectedSeats[seatCode] = {
                type: seatType,
                price: price
            };

        }


        updateSummary();

    });

});


/*
|--------------------------------------------------------------------------
| FOOD + BUTTON
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.food-plus').forEach(function(button) {

    button.addEventListener('click', function() {

        const id = this.dataset.id;

        if (!foodQuantities[id]) {
            foodQuantities[id] = 0;
        }

        foodQuantities[id]++;

        updateFoodQuantity(id);
        updateSummary();

    });

});


/*
|--------------------------------------------------------------------------
| FOOD - BUTTON
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.food-minus').forEach(function(button) {

    button.addEventListener('click', function() {

        const id = this.dataset.id;

        if (!foodQuantities[id]) {
            foodQuantities[id] = 0;
        }

        if (foodQuantities[id] > 0) {
            foodQuantities[id]--;
        }

        updateFoodQuantity(id);
        updateSummary();

    });

});


/*
|--------------------------------------------------------------------------
| UPDATE FOOD QUANTITY
|--------------------------------------------------------------------------
*/

function updateFoodQuantity(id) {

    const element = document.getElementById(
        'quantity-' + id
    );

    element.textContent = foodQuantities[id] || 0;

}


/*
|--------------------------------------------------------------------------
| CALCULATE SEAT TOTAL
|--------------------------------------------------------------------------
*/

function calculateSeatTotal() {

    let total = 0;

    Object.keys(selectedSeats).forEach(function(seatCode) {

        total += selectedSeats[seatCode].price;

    });

    return total;

}


/*
|--------------------------------------------------------------------------
| CALCULATE FOOD TOTAL
|--------------------------------------------------------------------------
*/

function calculateFoodTotal() {

    let total = 0;

    Object.keys(foodQuantities).forEach(function(id) {

        const quantity = foodQuantities[id];

        const priceElement = document.getElementById(
            'food-price-' + id
        );

        if (!priceElement) {
            return;
        }

        const price = parseInt(
            priceElement.value
        );

        total += quantity * price;

    });

    return total;

}


/*
|--------------------------------------------------------------------------
| UPDATE SUMMARY
|--------------------------------------------------------------------------
*/

function updateSummary() {

    const seatCodes = Object.keys(selectedSeats);

    const seatCount = seatCodes.length;

    const seatTotal = calculateSeatTotal();

    const foodTotal = calculateFoodTotal();

    const grandTotal = seatTotal + foodTotal;


    /*
    | Số ghế
    */

    document.getElementById(
        'seat-count'
    ).textContent = seatCount;


    /*
    | Danh sách ghế
    */

    document.getElementById(
        'selected-seats'
    ).textContent =
        seatCount > 0
            ? seatCodes.join(', ')
            : 'Chưa chọn';


    /*
    | Tiền ghế
    */

    document.getElementById(
        'seat-total'
    ).textContent = formatMoney(seatTotal);


    /*
    | Tiền bắp nước
    */

    document.getElementById(
        'food-total'
    ).textContent = formatMoney(foodTotal);


    /*
    | Tổng tiền
    */

    document.getElementById(
        'grand-total'
    ).textContent = formatMoney(grandTotal);


    /*
    | Gửi dữ liệu sang checkout.php
    */

    document.getElementById(
        'selected-seats-input'
    ).value = JSON.stringify(selectedSeats);


    document.getElementById(
        'foods-input'
    ).value = JSON.stringify(foodQuantities);


    document.getElementById(
        'total-input'
    ).value = grandTotal;


    /*
    | Enable checkout
    */

    const continueButton = document.getElementById(
        'continue-btn'
    );


    if (seatCount > 0) {

        continueButton.disabled = false;

    } else {

        continueButton.disabled = true;

    }

}


/*
|--------------------------------------------------------------------------
| INITIALIZE
|--------------------------------------------------------------------------
*/

updateSummary();

</script>


<?php include_once 'footer.php'; ?>
