<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$allowedMethods = [
    'BNI',
    'Mandiri',
    'BCA',
    'BRI',
    'QRIS'
];

$selectedType = isset($_GET['type'])
    ? trim($_GET['type'])
    : '';

if ($selectedType !== '' && !in_array($selectedType, $allowedMethods, true)) {
    $selectedType = '';
}

$message = '';
$error = '';
$uploadedFilePath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedType = isset($_POST['type'])
        ? trim($_POST['type'])
        : '';

    if (!in_array($selectedType, $allowedMethods, true)) {

        $error = 'Metode pembayaran tidak valid.';

    } else {

        try {

            $stmt = $pdo->query("
                SELECT COALESCE(MAX(sort_order), 0) + 1
                FROM payment_methods
            ");

            $sortOrder = (int)$stmt->fetchColumn();

            /*
             * BANK
             */
            if ($selectedType !== 'QRIS') {

                $accountName = trim($_POST['account_name'] ?? '');
                $accountNumber = trim($_POST['account_number'] ?? '');

                if ($accountName === '') {
                    throw new Exception('Nama pemilik wajib diisi.');
                }

                if ($accountNumber === '') {
                    throw new Exception('Nomor rekening wajib diisi.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO payment_methods
                    (
                        type,
                        name,
                        account_name,
                        account_number,
                        qr_image,
                        is_active,
                        sort_order
                    )
                    VALUES
                    (?, ?, ?, ?, NULL, 1, ?)
                ");

                $stmt->execute([
                    $selectedType,
                    $selectedType,
                    $accountName,
                    $accountNumber,
                    $sortOrder
                ]);

            /*
             * QRIS
             */
            } else {

                $qrisName = trim($_POST['qris_name'] ?? '');

                if ($qrisName === '') {
                    throw new Exception('Nama QRIS wajib diisi.');
                }

                if (
                    !isset($_FILES['qr_image']) ||
                    $_FILES['qr_image']['error'] !== UPLOAD_ERR_OK
                ) {
                    throw new Exception('Foto QRIS wajib diupload.');
                }

                $file = $_FILES['qr_image'];

                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Ukuran foto QRIS maksimal 5 MB.');
                }

                $imageInfo = @getimagesize($file['tmp_name']);

                if ($imageInfo === false || empty($imageInfo['mime'])) {
                    throw new Exception('File QRIS harus berupa gambar yang valid.');
                }

                $mimeToExtension = [
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    'image/webp' => 'webp'
                ];

                $mime = $imageInfo['mime'];

                if (!isset($mimeToExtension[$mime])) {
                    throw new Exception('Format foto QRIS yang diperbolehkan: PNG, JPG, JPEG, atau WEBP.');
                }

                $extension = $mimeToExtension[$mime];

                $fileName =
                    'qris_' .
                    date('Ymd_His') .
                    '_' .
                    bin2hex(random_bytes(4)) .
                    '.' .
                    $extension;

                $uploadDirectory = __DIR__ . '/../../assets/images/payment';

                if (!is_dir($uploadDirectory)) {
                    if (!mkdir($uploadDirectory, 0755, true)) {
                        throw new Exception('Folder penyimpanan QRIS tidak dapat dibuat.');
                    }
                }

                $uploadedFilePath = $uploadDirectory . '/' . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
                    throw new Exception('Foto QRIS gagal disimpan.');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO payment_methods
                    (
                        type,
                        name,
                        account_name,
                        account_number,
                        qr_image,
                        is_active,
                        sort_order
                    )
                    VALUES
                    ('QRIS', ?, NULL, NULL, ?, 1, ?)
                ");

                $stmt->execute([
                    $qrisName,
                    $fileName,
                    $sortOrder
                ]);
            }

            header('Location: pembayaran.php?success=Metode pembayaran berhasil ditambahkan.');
            exit;

        } catch (Exception $e) {

            if (
                $uploadedFilePath !== null &&
                is_file($uploadedFilePath)
            ) {
                @unlink($uploadedFilePath);
            }

            $error = $e->getMessage();
        }
    }
}

$backLink = 'pembayaran.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>
<title>Jam Operasional - Ramuna Futsal</title>
<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}
body{
    font-family:Arial,Helvetica,sans-serif;
    background:#e9f2fb;
    color:#12345a;
}
.admin-content{
    width:min(100%,944px);
    min-height:100vh;
    margin:0 auto;
    background:#f7f5ef;
    padding-bottom:92px;
}
/* HEADER */
.top-header{
    min-height:120px;
    padding:17px 27px;
    display:flex;
    align-items:center;
    gap:8px;
    background:
        linear-gradient(
            135deg,
            rgba(3,35,78,.94),
            rgba(8,83,165,.70)
        ),
        url("../../assets/images/admin/admin-bg.jpg")
        center/cover no-repeat;
    border-radius:0 0 25px 25px;
    color:#fff;
}
.back-button{
    width:34px;
    height:34px;
    min-width:34px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    text-decoration:none;
}
.back-icon{
    width:23px;
    height:23px;
}
.back-icon svg{
    width:100%;
    height:100%;
    fill:none;
    stroke:currentColor;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.header-text{
    min-width:0;
}
.page-title{
    font-size:24px;
    line-height:1.15;
    font-weight:800;
    color:#fff;
    margin-bottom:4px;
}
.page-subtitle{
    font-size:12px;
    line-height:1.4;
    color:rgba(255,255,255,.86);
}
/* CONTENT */
.content{
    padding:18px 27px 0;
}
.hours-card{
    background:#fff;
    border:1px solid #dce7f0;
    border-radius:14px;
    box-shadow:0 3px 10px rgba(18,63,112,.06);
    overflow:hidden;
}
.day-row{
    min-height:57px;
    padding:8px 13px;
    display:grid;
    grid-template-columns:70px 48px 1fr 1fr;
    align-items:center;
    gap:8px;
    border-bottom:1px solid #edf1f5;
}
.day-row:last-child{
    border-bottom:0;
}
.day-name{
    font-size:11px;
    font-weight:700;
    color:#123f70;
}
/* TOGGLE */
.toggle-wrap{
    display:flex;
    align-items:center;
    justify-content:center;
}
.toggle{
    position:relative;
    width:34px;
    height:19px;
    display:inline-block;
}
.toggle input{
    opacity:0;
    width:0;
    height:0;
}
.slider{
    position:absolute;
    inset:0;
    cursor:pointer;
    background:#cbd5df;
    border-radius:20px;
    transition:.2s;
}
.slider:before{
    content:"";
    position:absolute;
    width:15px;
    height:15px;
    left:2px;
    top:2px;
    background:#fff;
    border-radius:50%;
    transition:.2s;
    box-shadow:0 1px 3px rgba(0,0,0,.18);
}
.toggle input:checked + .slider{
    background:#1678d2;
}
.toggle input:checked + .slider:before{
    transform:translateX(15px);
}
/* TIME */
.time-input{
    width:100%;
    height:34px;
    border:1px solid #c9d8e6;
    border-radius:7px;
    padding:0 9px;
    color:#123f70;
    background:#fff;
    font-size:10px;
    outline:none;
}
.time-input:focus{
    border-color:#1678d2;
}
.time-input:disabled{
    background:#f1f3f5;
    color:#a1aab3;
    cursor:not-allowed;
}
.time-label{
    display:block;
    font-size:8px;
    color:#8190a0;
    margin:0 0 3px 2px;
}
/* INFO */
.info-box{
    margin-top:14px;
    padding:12px 14px;
    display:flex;
    align-items:flex-start;
    gap:9px;
    background:#edf6ff;
    border:1px solid #cfe4f7;
    border-radius:11px;
    color:#58718a;
    font-size:10px;
    line-height:1.45;
}
.info-icon{
    width:19px;
    height:19px;
    min-width:19px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:#1769aa;
    color:#fff;
    font-size:11px;
    font-weight:800;
}
.info-title{
    font-weight:800;
    color:#164d79;
    margin-bottom:2px;
}
/* MESSAGE */
.alert{
    margin-bottom:12px;
    padding:10px 13px;
    border-radius:9px;
    font-size:10px;
    font-weight:700;
}
.alert.success{
    background:#e5f8ef;
    border:1px solid #bde8d0;
    color:#16804f;
}
.alert.error{
    background:#fff0f0;
    border:1px solid #f1c4c4;
    color:#b33a3a;
}
/* SAVE */
.save-wrap{
    margin-top:14px;
}
.save-button{
    width:100%;
    height:42px;
    border:0;
    border-radius:8px;
    background:#176fca;
    color:#fff;
    font-size:11px;
    font-weight:700;
    cursor:pointer;
}
.save-button:hover{
    background:#1262b5;
}
.save-icon{
    display:inline-flex;
    width:15px;
    height:15px;
    vertical-align:middle;
    margin-right:5px;
}
.save-icon svg{
    width:100%;
    height:100%;
    fill:none;
    stroke:currentColor;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}
/* BOTTOM NAV */
.bottom-nav{
    position:fixed;
    left:50%;
    bottom:0;
    transform:translateX(-50%);
    width:min(100%,944px);
    height:70px;
    background:#fff;
    border-top:1px solid #dce4eb;
    display:flex;
    align-items:center;
    justify-content:space-around;
    z-index:100;
    box-shadow:0 -4px 15px rgba(31,65,94,.08);
}
.nav-item{
    width:33.333%;
    height:100%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:5px;
    text-decoration:none;
    color:#8b96a3;
    font-size:12px;
    font-weight:700;
    text-shadow:0 1px 1px rgba(0,0,0,.08);
}
.nav-icon{
    width:21px;
    height:21px;
}
.nav-icon svg{
    width:100%;
    height:100%;
    fill:none;
    stroke:currentColor;
    stroke-width:1.9;
    stroke-linecap:round;
    stroke-linejoin:round;
}
/* MOBILE */
@media(max-width:700px){
    .admin-content{
        width:100%;
    }
    .top-header{
        min-height:120px;
        padding:17px 20px;
        border-radius:0 0 22px 22px;
    }
    .page-title{
        font-size:22px;
    }
    .page-subtitle{
        font-size:11px;
    }
    .content{
        padding:14px 12px 0;
    }
    .day-row{
        min-height:55px;
        padding:7px 9px;
        grid-template-columns:58px 37px 1fr 1fr;
        gap:6px;
    }
    .day-name{
        font-size:10px;
    }
    .toggle{
        width:32px;
        height:18px;
    }
    .slider:before{
        width:14px;
        height:14px;
    }
    .toggle input:checked + .slider:before{
        transform:translateX(14px);
    }
    .time-label{
        font-size:7px;
    }
    .time-input{
        height:32px;
        padding:0 6px;
        font-size:9px;
    }
    .info-box{
        font-size:9px;
    }
    .bottom-nav{
        width:100%;
        height:68px;
    }
}
</style>
<style>
.payment-choice-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-choice {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 58px;
    padding: 0 16px;
    background: #ffffff;
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    color: #12366d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
}

.payment-choice:hover {
    border-color: #2276c9;
    background: #f8fbff;
}

.payment-choice.disabled {
    color: #9aa8b9;
    background: #f5f7fa;
    cursor: default;
}

.payment-choice-status {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 600;
}

.payment-choice-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-choice-arrow svg {
    width: 17px;
    height: 17px;
    fill: none;
    stroke: #1e5fae;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.form-card {
    background: #ffffff;
    border: 1px solid #dbe5ef;
    border-radius: 16px;
    padding: 20px;
}

.form-title {
    color: #12366d;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 7px;
}

.form-description {
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
    margin-bottom: 20px;
}

.selected-method {
    padding: 11px 13px;
    margin-bottom: 18px;
    border-radius: 10px;
    background: #edf5ff;
    color: #185da7;
    font-size: 12px;
    font-weight: 800;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 7px;
    color: #12366d;
    font-size: 12px;
    font-weight: 800;
}

.form-input {
    width: 100%;
    height: 46px;
    padding: 0 13px;
    border: 1px solid #d6e1ed;
    border-radius: 10px;
    outline: none;
    color: #12366d;
    background: #ffffff;
    font-size: 13px;
}

.form-input:focus {
    border-color: #2276c9;
    box-shadow: 0 0 0 3px rgba(34,118,201,.10);
}

.form-note {
    margin-top: 7px;
    color: #71829a;
    font-size: 10px;
    line-height: 1.5;
}
.file-input {
    height: auto;
    min-height: 46px;
    padding: 10px 12px;
    background: #f8fafc;
    cursor: pointer;
}

.form-note strong {
    color: #12366d;
}
</style>
</head>
<body>
<div class="admin-content">
<header class="top-header">
    <a
        href="pembayaran.php"
        class="back-button"
        aria-label="Kembali"
    >
        <span class="back-icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 12H5"></path>
                <path d="M11 18l-6-6 6-6"></path>
            </svg>
        </span>
    </a>
    <div class="header-text">
        <h1 class="page-title">
    Tambah Metode Pembayaran
</h1>
        <p class="page-subtitle">
    Pilih metode pembayaran lalu lengkapi informasinya.
</p>
    </div>
</header>
<main class="content">

<?php if ($message !== ''): ?>
    <div class="alert success">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($selectedType === ''): ?>

    <div class="payment-choice-list">

        <?php foreach ($allowedMethods as $method): ?>

            <a
                href="tambah-pembayaran.php?type=<?= urlencode($method) ?>"
                class="payment-choice"
            >
                <span>
                    <?= htmlspecialchars($method) ?>
                </span>

                <span class="payment-choice-arrow">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            </a>

        <?php endforeach; ?>

    </div>

<?php else: ?>

    <div class="form-card">

        <div class="form-title">
            Informasi <?= htmlspecialchars($selectedType) ?>
        </div>

        <div class="form-description">

            <?php if ($selectedType === 'QRIS'): ?>

                Masukkan nama QRIS dan upload foto QRIS yang akan digunakan customer untuk melakukan pembayaran.

            <?php else: ?>

                Masukkan informasi rekening yang akan ditampilkan kepada customer.

            <?php endif; ?>

        </div>

        <div class="selected-method">
            Metode dipilih: <?= htmlspecialchars($selectedType) ?>
        </div>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="type"
                value="<?= htmlspecialchars($selectedType) ?>"
            >

            <?php if ($selectedType === 'QRIS'): ?>

                <div class="form-group">

                    <label class="form-label" for="qris_name">
                        Nama QRIS
                    </label>

                    <input
                        type="text"
                        id="qris_name"
                        name="qris_name"
                        class="form-input"
                        placeholder="Contoh: QRIS Ramuna Futsal"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['qris_name'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label" for="qr_image">
                        Foto QRIS
                    </label>

                    <input
                        type="file"
                        id="qr_image"
                        name="qr_image"
                        class="form-input file-input"
                        accept=".png,.jpg,.jpeg,.webp"
                        required
                    >

                    <div class="form-note">
                        Format yang diperbolehkan PNG, JPG, JPEG, atau WEBP. Maksimal 5 MB.
                    </div>

                </div>

            <?php else: ?>

                <div class="form-group">

                    <label class="form-label" for="account_name">
                        Nama Pemilik
                    </label>

                    <input
                        type="text"
                        id="account_name"
                        name="account_name"
                        class="form-input"
                        placeholder="Contoh: Ramuna Futsal"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['account_name'] ?? '') ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label class="form-label" for="account_number">
                        Nomor Rekening
                    </label>

                    <input
                        type="text"
                        id="account_number"
                        name="account_number"
                        class="form-input"
                        placeholder="Masukkan nomor rekening"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['account_number'] ?? '') ?>"
                        required
                    >

                </div>

            <?php endif; ?>

            <div class="save-wrap">

                <button
                    type="submit"
                    class="save-button"
                >
                    <span class="save-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 4h11l3 3v13H5z"></path>
                            <path d="M8 4v6h8V4"></path>
                            <path d="M8 20v-6h8v6"></path>
                        </svg>
                    </span>

                    Simpan Metode Pembayaran
                </button>

            </div>

        </form>

    </div>

<?php endif; ?>

</main>
<nav class="bottom-nav">
    <a
        href="../index.php"
        class="nav-item"
    >
        <span class="nav-icon">
            <svg viewBox="0 0 24 24">
                <path d="M3 10.5 12 3l9 7.5"></path>
                <path d="M5.5 9.5V21h13V9.5"></path>
                <path d="M9.5 21v-6h5v6"></path>
            </svg>
        </span>
        <span>
            Beranda
        </span>
    </a>
    <a
        href="../reservasi/index.php"
        class="nav-item"
    >
        <span class="nav-icon">
            <svg viewBox="0 0 24 24">
                <rect
                    x="3"
                    y="5"
                    width="18"
                    height="16"
                    rx="2"
                ></rect>
                <path d="M7 3v4"></path>
                <path d="M17 3v4"></path>
                <path d="M3 10h18"></path>
            </svg>
        </span>
        <span>
            Kelola Reservasi
        </span>
    </a>
    <a
        href="../akun/index.php"
        class="nav-item"
    >
        <span class="nav-icon">
            <svg viewBox="0 0 24 24">
                <circle
                    cx="12"
                    cy="8"
                    r="4"
                ></circle>
                <path
                    d="M4 21c.8-4 3.5-6 8-6s7.2 2 8 6"
                ></path>
            </svg>
        </span>
        <span>
            Akun
        </span>
    </a>
</nav>
</div>

</body>
</html>

