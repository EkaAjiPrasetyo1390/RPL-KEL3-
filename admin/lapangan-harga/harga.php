<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$message = '';
$error = '';

/*
|--------------------------------------------------------------------------
| Ambil daftar lapangan
|--------------------------------------------------------------------------
*/
try {
    $stmtFields = $pdo->query("
        SELECT id, name, status
        FROM fields
        ORDER BY id ASC
    ");

    $fields = $stmtFields->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $fields = [];
}

/*
|--------------------------------------------------------------------------
| Tentukan lapangan aktif
|--------------------------------------------------------------------------
*/
$fieldId = isset($_GET['field_id']) ? (int) $_GET['field_id'] : 0;

if ($fieldId <= 0 && !empty($fields)) {
    $fieldId = (int) $fields[0]['id'];
}

/*
|--------------------------------------------------------------------------
| Simpan harga
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_harga'])) {

    $fieldIdPost = isset($_POST['field_id']) ? (int) $_POST['field_id'] : 0;
    $applyAll = isset($_POST['apply_all']) && $_POST['apply_all'] === '1';

    $rentalStart = $_POST['rental_start'] ?? [];
    $rentalEnd = $_POST['rental_end'] ?? [];
    $rentalPrice = $_POST['rental_price'] ?? [];

    $bookingStart = $_POST['booking_start'] ?? [];
    $bookingEnd = $_POST['booking_end'] ?? [];
    $bookingPrice = $_POST['booking_price'] ?? [];

    try {

        if ($fieldIdPost <= 0) {
            throw new Exception('Lapangan belum dipilih.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi data harga
        |--------------------------------------------------------------------------
        */
        $rentalRows = [];

        for ($i = 0; $i < count($rentalStart); $i++) {

            $start = trim($rentalStart[$i] ?? '');
            $end = trim($rentalEnd[$i] ?? '');
            $price = trim($rentalPrice[$i] ?? '');

            if ($start === '' && $end === '' && $price === '') {
                continue;
            }

            if ($start === '' || $end === '' || $price === '') {
                throw new Exception('Data harga sewa belum lengkap.');
            }

            $priceNumber = (float) preg_replace('/[^0-9]/', '', $price);

            if ($priceNumber <= 0) {
                throw new Exception('Harga sewa harus lebih dari 0.');
            }

            $rentalRows[] = [
                'start' => $start,
                'end' => $end,
                'price' => $priceNumber
            ];
        }

        $bookingRows = [];

        for ($i = 0; $i < count($bookingStart); $i++) {

            $start = trim($bookingStart[$i] ?? '');
            $end = trim($bookingEnd[$i] ?? '');
            $price = trim($bookingPrice[$i] ?? '');

            if ($start === '' && $end === '' && $price === '') {
                continue;
            }

            if ($start === '' || $end === '' || $price === '') {
                throw new Exception('Data harga booking belum lengkap.');
            }

            $priceNumber = (float) preg_replace('/[^0-9]/', '', $price);

            if ($priceNumber <= 0) {
                throw new Exception('Harga booking harus lebih dari 0.');
            }

            $bookingRows[] = [
                'start' => $start,
                'end' => $end,
                'price' => $priceNumber
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Tentukan lapangan yang akan disimpan
        |--------------------------------------------------------------------------
        */
        if ($applyAll) {

            $targetFieldIds = [];

            foreach ($fields as $field) {
                if ($field['status'] === 'active') {
                    $targetFieldIds[] = (int) $field['id'];
                }
            }

        } else {

            $targetFieldIds = [$fieldIdPost];
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan dengan transaction
        |--------------------------------------------------------------------------
        */
        $pdo->beginTransaction();

        foreach ($targetFieldIds as $targetFieldId) {

            $deleteRental = $pdo->prepare("
                DELETE FROM field_rental_prices
                WHERE field_id = ?
            ");

            $deleteRental->execute([$targetFieldId]);

            $deleteBooking = $pdo->prepare("
                DELETE FROM field_booking_prices
                WHERE field_id = ?
            ");

            $deleteBooking->execute([$targetFieldId]);

            /*
            |--------------------------------------------------------------------------
            | Insert harga sewa
            |--------------------------------------------------------------------------
            */
            $insertRental = $pdo->prepare("
                INSERT INTO field_rental_prices
                (field_id, start_time, end_time, price_per_hour)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($rentalRows as $row) {

                $insertRental->execute([
                    $targetFieldId,
                    $row['start'],
                    $row['end'],
                    $row['price']
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Insert harga booking
            |--------------------------------------------------------------------------
            */
            $insertBooking = $pdo->prepare("
                INSERT INTO field_booking_prices
                (field_id, start_time, end_time, booking_fee)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($bookingRows as $row) {

                $insertBooking->execute([
                    $targetFieldId,
                    $row['start'],
                    $row['end'],
                    $row['price']
                ]);
            }
        }

        $pdo->commit();

        header(
            'Location: harga.php?field_id=' .
            $fieldIdPost .
            '&success=1'
        );

        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| Pesan berhasil
|--------------------------------------------------------------------------
*/
if (isset($_GET['success'])) {
    $message = 'Pengaturan harga berhasil disimpan.';
}

/*
|--------------------------------------------------------------------------
| Ambil data harga lapangan aktif
|--------------------------------------------------------------------------
*/
$rentalPrices = [];
$bookingPrices = [];

if ($fieldId > 0) {

    try {

        $stmtRental = $pdo->prepare("
            SELECT id, start_time, end_time, price_per_hour
            FROM field_rental_prices
            WHERE field_id = ?
            ORDER BY start_time ASC
        ");

        $stmtRental->execute([$fieldId]);
        $rentalPrices = $stmtRental->fetchAll(PDO::FETCH_ASSOC);

        $stmtBooking = $pdo->prepare("
            SELECT id, start_time, end_time, booking_fee
            FROM field_booking_prices
            WHERE field_id = ?
            ORDER BY start_time ASC
        ");

        $stmtBooking->execute([$fieldId]);
        $bookingPrices = $stmtBooking->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $rentalPrices = [];
        $bookingPrices = [];
    }
}

/*
|--------------------------------------------------------------------------
| Jika belum ada data harga, tampilkan contoh default
|--------------------------------------------------------------------------
*/
if (empty($rentalPrices)) {

    $rentalPrices = [
        [
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'price_per_hour' => 90000
        ],
        [
            'start_time' => '17:00:00',
            'end_time' => '23:00:00',
            'price_per_hour' => 130000
        ]
    ];
}

if (empty($bookingPrices)) {

    $bookingPrices = [
        [
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'booking_fee' => 30000
        ],
        [
            'start_time' => '17:00:00',
            'end_time' => '23:00:00',
            'booking_fee' => 50000
        ]
    ];
}

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/
function formatRupiah($value)
{
    return number_format((float) $value, 0, ',', '.');
}

function formatTime($value)
{
    return substr($value, 0, 5);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pengaturan Harga - Ramuna Futsal</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e9f2fb;
            color: #12345a;
        }

        .admin-content {
            width: min(100%, 1180px);
            min-height: 100vh;
            margin: 0 auto;
            background: #f7f5ef;
            padding-bottom: 105px;
        }

        /* HEADER */

        .top-header {
            background: #ffffff;
            padding: 18px 27px 12px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 0;
            background: transparent;
            color: #12345a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .back-icon {
            width: 20px;
            height: 20px;
        }

        .back-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            color: #123f70;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 12px;
            line-height: 1.5;
            color: #788797;
        }

        /* CONTENT */

        .content {
            padding: 5px 14px 0;
        }

        .section-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 12px 10px;
            margin-bottom: 10px;
            border: 1px solid #e1ebf4;
            box-shadow: 0 3px 10px rgba(25, 65, 100, .06);
        }

        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: #123f70;
            margin-bottom: 8px;
        }

        /* FIELD SELECT */

        .field-select-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .field-option {
            position: relative;
        }

        .field-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .field-option label {
            min-height: 31px;
            padding: 7px 5px;
            border: 1px solid #cbd9e7;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            color: #506a82;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .field-option input:checked + label {
            background: #edf6ff;
            border-color: #2878bd;
            color: #1769aa;
        }

        .check-icon {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #1c78bd;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 9px;
        }

        .field-option input:checked + label .check-icon {
            display: flex;
        }

        .apply-all {
            width: 100%;
            height: 31px;
            margin-top: 7px;
            border: 1px solid #2878bd;
            border-radius: 7px;
            background: #ffffff;
            color: #1769aa;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .apply-all.active {
            background: #1769aa;
            color: #ffffff;
        }

        /* PRICE SECTION */

        .price-heading {
            margin-bottom: 7px;
        }

        .price-title {
            font-size: 13px;
            font-weight: 800;
            color: #123f70;
        }

        .price-description {
            font-size: 9px;
            color: #8793a0;
            margin-top: 2px;
        }

        .price-table-header,
        .price-row {
            display: grid;
            grid-template-columns: 24px 1fr 1fr 78px 22px;
            gap: 5px;
            align-items: center;
        }

        .price-table-header {
            padding: 0 3px 4px;
            color: #8793a0;
            font-size: 8px;
        }

        .price-row {
            margin-bottom: 5px;
        }

        .clock-icon {
            width: 16px;
            height: 16px;
        }

        .clock-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: #58758f;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .time-input,
        .price-input {
            width: 100%;
            height: 27px;
            border: 1px solid #cad9e7;
            border-radius: 6px;
            padding: 0 7px;
            outline: none;
            background: #ffffff;
            color: #234765;
            font-size: 9px;
        }

        .time-input:focus,
        .price-input:focus {
            border-color: #2878bd;
        }

        .delete-row {
            width: 21px;
            height: 21px;
            border: 0;
            background: transparent;
            color: #e13737;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-row svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .add-row {
            width: 100%;
            height: 27px;
            border: 1px solid #2878bd;
            border-radius: 6px;
            background: #ffffff;
            color: #1769aa;
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
        }

        /* INFO */

        .info-box {
            display: flex;
            gap: 7px;
            margin-top: 8px;
            padding: 9px 10px;
            background: #edf6ff;
            border: 1px solid #cfe4f7;
            border-radius: 8px;
            color: #58718a;
            font-size: 8px;
            line-height: 1.5;
        }

        .info-icon {
            flex: 0 0 16px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #1769aa;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 10px;
        }

        /* MESSAGE */

        .message {
            padding: 9px 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .message.success {
            background: #eaf8ef;
            color: #218653;
            border: 1px solid #bfe7cd;
        }

        .message.error {
            background: #fff0f0;
            color: #c63c3c;
            border: 1px solid #f0c2c2;
        }

        /* SAVE BUTTONS */

        .save-actions {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 6px;
            margin-top: 10px;
        }

        .cancel-button,
        .save-button {
            height: 36px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .cancel-button {
            border: 0;
            background: #e8eef5;
            color: #718196;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .save-button {
            border: 0;
            background: #0878e8;
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(8, 120, 232, .18);
        }

        .save-icon {
            display: inline-flex;
            vertical-align: middle;
            margin-right: 5px;
        }

        .save-icon svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* BOTTOM NAV */

        .bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: min(100%, 1180px);
            height: 70px;
            background: #ffffff;
            border-top: 1px solid #dce4eb;
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 100;
            box-shadow: 0 -4px 15px rgba(31, 65, 94, .08);
        }

        .nav-item {
            width: 33.333%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            color: #8b96a3;
            font-size: 12px;
            font-weight: 700;
            text-shadow: 0 1px 1px rgba(0,0,0,.08);
        }

        .nav-icon {
            width: 21px;
            height: 21px;
        }

        .nav-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @media (min-width: 701px) {

            .admin-content {
                min-height: 100vh;
            }

            .top-header {
                padding-left: 27px;
                padding-right: 27px;
            }

            .content {
                max-width: 700px;
                margin: 0 auto;
            }
        }

        @media (max-width: 380px) {

            .price-table-header,
            .price-row {
                grid-template-columns: 19px 1fr 1fr 70px 19px;
                gap: 3px;
            }

            .time-input,
            .price-input {
                padding: 0 5px;
                font-size: 8px;
            }
        }

    /* FINAL HEADER OVERRIDE */

.top-header {
    min-height: 120px !important;
    height: 120px !important;
    padding: 17px 27px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;

    background:
        linear-gradient(135deg, rgba(3, 35, 78, 0.94), rgba(8, 83, 165, 0.70)),
        url("../../assets/images/admin/admin-bg.jpg") center center / cover no-repeat !important;

    border-radius: 0 0 25px 25px !important;
    color: #ffffff !important;
}

.top-header .back-button {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    flex: 0 0 34px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    padding: 0 !important;
    margin: 0 !important;

    background: transparent !important;
    color: #ffffff !important;
    border: 0 !important;
    text-decoration: none !important;
}

.top-header .back-icon {
    width: 23px !important;
    height: 23px !important;
    display: block !important;
}

.top-header .back-icon svg {
    width: 100% !important;
    height: 100% !important;

    fill: none !important;
    stroke: #ffffff !important;
    stroke-width: 2.2 !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
}

.top-header .header-text {
    min-width: 0 !important;
    display: block !important;
}

.top-header .page-title {
    margin: 0 0 4px 0 !important;
    padding: 0 !important;

    color: #ffffff !important;
    font-size: 24px !important;
    line-height: 1.15 !important;
    font-weight: 800 !important;
}

.top-header .page-subtitle {
    margin: 0 !important;
    padding: 0 !important;

    color: rgba(255,255,255,.86) !important;
    font-size: 12px !important;
    line-height: 1.4 !important;
}

@media (max-width: 700px) {

    .top-header {
        min-height: 120px !important;
        height: 120px !important;
        padding: 17px 20px !important;
        border-radius: 0 0 22px 22px !important;
    }

    .top-header .back-button {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        flex-basis: 32px !important;
    }

    .top-header .back-icon {
        width: 22px !important;
        height: 22px !important;
    }

    .top-header .page-title {
        font-size: 22px !important;
    }

    .top-header .page-subtitle {
        font-size: 11px !important;
    }
}
/* =========================================================
   DESKTOP CONTAINER ALIGNMENT
   Header, Main, dan Bottom Nav harus sejajar
   ========================================================= */
@media (min-width: 701px) {
    .admin-content {
        width: min(100%, 944px) !important;
        max-width: 944px !important;
        margin: 0 auto !important;
    }
    .top-header {
        width: 100% !important;
    }
    .content {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding-left: 27px !important;
        padding-right: 27px !important;
    }
    .bottom-nav {
        width: min(100%, 944px) !important;
        max-width: 944px !important;
    }
}
</style>

</head>

<body>

<div class="admin-content">

    <!-- HEADER -->
    <header class="top-header">

        <a href="index.php" class="back-button" aria-label="Kembali">

            <span class="back-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M19 12H5"></path>
                    <path d="M11 18l-6-6 6-6"></path>
                </svg>
            </span>

        </a>

        <div class="header-text">

            <h1 class="page-title">
                Pengaturan Harga
            </h1>

            <p class="page-subtitle">
                Atur harga sewa dan harga booking berdasarkan jam.
            </p>

        </div>

    </header>


    <main class="content">

        <?php if ($message): ?>

            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST" id="priceForm">

            <input
                type="hidden"
                name="field_id"
                id="fieldId"
                value="<?= $fieldId ?>"
            >

            <input
                type="hidden"
                name="apply_all"
                id="applyAll"
                value="0"
            >

            <!-- PILIH LAPANGAN -->

            <section class="section-card">

                <div class="section-title">
                    Pilih Lapangan / Terapkan Harga
                </div>

                <div class="field-select-row">

                    <?php foreach ($fields as $field): ?>

                        <div class="field-option">

                            <input
                                type="checkbox"
                                name="field_selector"
                                id="field_<?= (int) $field['id'] ?>"
                                value="<?= (int) $field['id'] ?>"
                                <?= (int) $field['id'] === $fieldId ? 'checked' : '' ?>
                            >

                            <label for="field_<?= (int) $field['id'] ?>">

                                <span class="check-icon">✓</span>

                                <?= htmlspecialchars($field['name']) ?>

                            </label>

                        </div>

                    <?php endforeach; ?>

                </div>


                <button
                    type="button"
                    class="apply-all"
                    id="applyAllButton"
                >
                    + Terapkan ke Semua
                </button>

            </section>


            <!-- HARGA SEWA -->

            <section class="section-card">

                <div class="price-heading">

                    <div class="price-title">
                        Harga Sewa (per jam)
                    </div>

                    <div class="price-description">
                        Harga yang dibayar customer saat menggunakan lapangan.
                    </div>

                </div>


                <div class="price-table-header">

                    <span></span>
                    <span>Jam Mulai</span>
                    <span>Jam Selesai</span>
                    <span>Rp / Jam</span>
                    <span></span>

                </div>


                <div id="rentalRows">

                    <?php foreach ($rentalPrices as $price): ?>

                        <div class="price-row">

                            <span class="clock-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8.5"></circle>
                                    <path d="M12 7v5l3 2"></path>
                                </svg>
                            </span>

                            <input
                                type="time"
                                class="time-input"
                                name="rental_start[]"
                                value="<?= htmlspecialchars(formatTime($price['start_time'])) ?>"
                            >

                            <input
                                type="time"
                                class="time-input"
                                name="rental_end[]"
                                value="<?= htmlspecialchars(formatTime($price['end_time'])) ?>"
                            >

                            <input
                                type="number"
                                class="price-input"
                                name="rental_price[]"
                                value="<?= (int) $price['price_per_hour'] ?>"
                                min="0"
                                step="1000"
                            >

                            <button
                                type="button"
                                class="delete-row"
                                onclick="removeRow(this)"
                                aria-label="Hapus rentang"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 7h16"></path>
                                    <path d="M9 7V4h6v3"></path>
                                    <path d="M7 7l1 13h8l1-13"></path>
                                    <path d="M10 11v5M14 11v5"></path>
                                </svg>
                            </button>

                        </div>

                    <?php endforeach; ?>

                </div>


                <button
                    type="button"
                    class="add-row"
                    onclick="addRentalRow()"
                >
                    + &nbsp;Tambah Rentang Harga
                </button>

            </section>


            <!-- HARGA BOOKING -->

            <section class="section-card">

                <div class="price-heading">

                    <div class="price-title">
                        Harga Booking (uang muka)
                    </div>

                    <div class="price-description">
                        Harga yang dibayar customer saat melakukan reservasi.
                    </div>

                </div>


                <div class="price-table-header">

                    <span></span>
                    <span>Jam Mulai</span>
                    <span>Jam Selesai</span>
                    <span>Harga Booking</span>
                    <span></span>

                </div>


                <div id="bookingRows">

                    <?php foreach ($bookingPrices as $price): ?>

                        <div class="price-row">

                            <span class="clock-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8.5"></circle>
                                    <path d="M12 7v5l3 2"></path>
                                </svg>
                            </span>

                            <input
                                type="time"
                                class="time-input"
                                name="booking_start[]"
                                value="<?= htmlspecialchars(formatTime($price['start_time'])) ?>"
                            >

                            <input
                                type="time"
                                class="time-input"
                                name="booking_end[]"
                                value="<?= htmlspecialchars(formatTime($price['end_time'])) ?>"
                            >

                            <input
                                type="number"
                                class="price-input"
                                name="booking_price[]"
                                value="<?= (int) $price['booking_fee'] ?>"
                                min="0"
                                step="1000"
                            >

                            <button
                                type="button"
                                class="delete-row"
                                onclick="removeRow(this)"
                                aria-label="Hapus rentang"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 7h16"></path>
                                    <path d="M9 7V4h6v3"></path>
                                    <path d="M7 7l1 13h8l1-13"></path>
                                    <path d="M10 11v5M14 11v5"></path>
                                </svg>
                            </button>

                        </div>

                    <?php endforeach; ?>

                </div>


                <button
                    type="button"
                    class="add-row"
                    onclick="addBookingRow()"
                >
                    + &nbsp;Tambah Rentang Harga
                </button>


                <div class="info-box">

                    <span class="info-icon">i</span>

                    <span>
                        Harga yang diatur akan berlaku untuk lapangan yang dipilih.
                        Gunakan tombol Terapkan ke Semua jika ingin menggunakan pengaturan yang sama untuk semua lapangan.
                    </span>

                </div>

            </section>


            <!-- BUTTON -->

            <div class="save-actions">

                <a
                    href="index.php"
                    class="cancel-button"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    name="simpan_harga"
                    value="1"
                    class="save-button"
                >

                    <span class="save-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 4h12l2 2v14H5z"></path>
                            <path d="M8 4v6h8V4"></path>
                            <path d="M8 20v-6h8v6"></path>
                        </svg>
                    </span>

                    Simpan

                </button>

            </div>

        </form>

    </main>


    <!-- BOTTOM NAV -->

    <nav class="bottom-nav">

        <a href="../index.php" class="nav-item">

            <span class="nav-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 10.5 12 3l9 7.5"></path>
                    <path d="M5.5 9.5V21h13V9.5"></path>
                    <path d="M9.5 21v-6h5v6"></path>
                </svg>
            </span>

            <span>Beranda</span>

        </a>


        <a href="../reservasi/index.php" class="nav-item">

            <span class="nav-icon">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M7 3v4M17 3v4M3 10h18"></path>
                </svg>
            </span>

            <span>Kelola Reservasi</span>

        </a>


        <a href="../akun.php" class="nav-item">

            <span class="nav-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21c.8-4 3.5-6 8-6s7.2 2 8 6"></path>
                </svg>
            </span>

            <span>Akun</span>

        </a>

    </nav>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Ganti lapangan
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('input[name="field_selector"]').forEach(function(input) {

        input.addEventListener('change', function() {

            if (document.getElementById('applyAll').value === '1') {
                document.getElementById('applyAll').value = '0';
                document.getElementById('applyAllButton').classList.remove('active');
            }

            window.location.href =
                'harga.php?field_id=' + this.value;
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Terapkan ke semua
    |--------------------------------------------------------------------------
    */
    document.getElementById('applyAllButton').addEventListener('click', function() {

        const applyAll = document.getElementById('applyAll');

        if (applyAll.value === '0') {

            /*
             * Aktifkan mode Terapkan ke Semua
             */
            applyAll.value = '1';

            this.classList.add('active');

            this.innerHTML = '✓ Harga akan diterapkan ke semua lapangan';

            /*
             * Semua lapangan otomatis terpilih
             */
            document.querySelectorAll('input[name="field_selector"]').forEach(function(input) {
                input.checked = true;
            });

        } else {

            /*
             * Nonaktifkan mode Terapkan ke Semua
             */
            applyAll.value = '0';

            this.classList.remove('active');

            this.innerHTML = '+ Terapkan ke Semua';

            /*
             * Kembalikan pilihan hanya ke lapangan aktif
             */
            document.querySelectorAll('input[name="field_selector"]').forEach(function(input) {
                input.checked = false;
            });

            const currentFieldId = document.getElementById('fieldId').value;

            const currentField = document.getElementById(
                'field_' + currentFieldId
            );

            if (currentField) {
                currentField.checked = true;
            }

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Format input harga
    |--------------------------------------------------------------------------
    */
    document.addEventListener('input', function(event) {

        if (event.target.classList.contains('price-input')) {

            let value = event.target.value.replace(/[^0-9]/g, '');

            event.target.value = value;

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Hapus rentang
    |--------------------------------------------------------------------------
    */
    function removeRow(button) {

        const row = button.closest('.price-row');

        if (row) {
            row.remove();
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Tambah harga sewa
    |--------------------------------------------------------------------------
    */
    function addRentalRow() {

        const container = document.getElementById('rentalRows');

        const row = document.createElement('div');

        row.className = 'price-row';

        row.innerHTML = `
            <span class="clock-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8.5"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </span>

            <input
                type="time"
                class="time-input"
                name="rental_start[]"
            >

            <input
                type="time"
                class="time-input"
                name="rental_end[]"
            >

            <input
                type="number"
                class="price-input"
                name="rental_price[]"
                min="0"
                step="1000"
                placeholder="Rp"
            >

            <button
                type="button"
                class="delete-row"
                onclick="removeRow(this)"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M4 7h16"></path>
                    <path d="M9 7V4h6v3"></path>
                    <path d="M7 7l1 13h8l1-13"></path>
                    <path d="M10 11v5M14 11v5"></path>
                </svg>
            </button>
        `;

        container.appendChild(row);

    }


    /*
    |--------------------------------------------------------------------------
    | Tambah harga booking
    |--------------------------------------------------------------------------
    */
    function addBookingRow() {

        const container = document.getElementById('bookingRows');

        const row = document.createElement('div');

        row.className = 'price-row';

        row.innerHTML = `
            <span class="clock-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8.5"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </span>

            <input
                type="time"
                class="time-input"
                name="booking_start[]"
            >

            <input
                type="time"
                class="time-input"
                name="booking_end[]"
            >

            <input
                type="number"
                class="price-input"
                name="booking_price[]"
                min="0"
                step="1000"
                placeholder="Rp"
            >

            <button
                type="button"
                class="delete-row"
                onclick="removeRow(this)"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M4 7h16"></path>
                    <path d="M9 7V4h6v3"></path>
                    <path d="M7 7l1 13h8l1-13"></path>
                    <path d="M10 11v5M14 11v5"></path>
                </svg>
            </button>
        `;

        container.appendChild(row);

    }

</script>

</body>
</html>





