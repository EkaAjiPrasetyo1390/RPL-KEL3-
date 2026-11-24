<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
requireAdmin();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hours = $_POST['hours'] ?? [];
    try {
        $pdo->beginTransaction();
        foreach ($hours as $id => $data) {
            $id = (int) $id;
            $isOpen = isset($data['is_open']) ? 1 : 0;
            $openTime = trim($data['open_time'] ?? '');
            $closeTime = trim($data['close_time'] ?? '');
            if ($isOpen) {
                if ($openTime === '' || $closeTime === '') {
                    throw new Exception('Jam operasional yang aktif harus memiliki jam buka dan jam tutup.');
                }
                if ($openTime >= $closeTime) {
                    throw new Exception('Jam buka harus lebih awal daripada jam tutup.');
                }
            }
            $stmt = $pdo->prepare("
                UPDATE operating_hours
                SET
                    is_open = ?,
                    open_time = ?,
                    close_time = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $isOpen,
                $openTime !== '' ? $openTime : '08:00:00',
                $closeTime !== '' ? $closeTime : '23:00:00',
                $id
            ]);
        }
        $pdo->commit();
        $message = 'Pengaturan jam operasional berhasil disimpan.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
$stmt = $pdo->query("
    SELECT
        id,
        day_of_week,
        day_name,
        is_open,
        open_time,
        close_time
    FROM operating_hours
    ORDER BY day_of_week
");
$operatingHours = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
</head>
<body>
<div class="admin-content">
<header class="top-header">
    <a
        href="index.php"
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
            Jam Operasional
        </h1>
        <p class="page-subtitle">
            Atur jam buka dan tutup lapangan.
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
<form method="POST">
    <div class="hours-card">
        <?php foreach ($operatingHours as $hour): ?>
            <div class="day-row">
                <div class="day-name">
                    <?= htmlspecialchars($hour['day_name']) ?>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle">
                        <input
                            type="checkbox"
                            name="hours[<?= (int)$hour['id'] ?>][is_open]"
                            <?= $hour['is_open'] ? 'checked' : '' ?>
                        >
                        <span class="slider"></span>
                    </label>
                </div>
                <div>
                    <label class="time-label">
                        Jam Mulai
                    </label>
                    <input
                        type="time"
                        class="time-input"
                        name="hours[<?= (int)$hour['id'] ?>][open_time]"
                        value="<?= htmlspecialchars(substr($hour['open_time'],0,5)) ?>"
                    >
                </div>
                <div>
                    <label class="time-label">
                        Jam Selesai
                    </label>
                    <input
                        type="time"
                        class="time-input"
                        name="hours[<?= (int)$hour['id'] ?>][close_time]"
                        value="<?= htmlspecialchars(substr($hour['close_time'],0,5)) ?>"
                    >
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="info-box">
        <span class="info-icon">
            i
        </span>
        <div>
            <div class="info-title">
                Informasi
            </div>
            Jam operasional akan mempengaruhi ketersediaan jam yang ditampilkan pada customer.
        </div>
    </div>
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
            Simpan Perubahan
        </button>
    </div>
</form>
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
<script>
document.querySelectorAll('.day-row').forEach(function(row) {
    const toggle = row.querySelector('input[type="checkbox"]');
    const dayName = row.querySelector('.day-name').textContent.trim();
    if (!toggle) {
        return;
    }
    let previousState = toggle.checked;
    toggle.addEventListener('change', function() {
        if (!this.checked) {
            const message =
                'Nonaktifkan Jam Operasional\n\n' +
                'Jika ' + dayName + ' dinonaktifkan:\n\n' +
                '• Customer tidak dapat melakukan reservasi baru pada hari ' + dayName + '.\n' +
                '• Slot ' + dayName + ' tidak akan tersedia pada jadwal reservasi customer.\n' +
                '• Reservasi yang sudah ada tidak otomatis dibatalkan.\n\n' +
                'Pengaturan baru berlaku setelah tombol Simpan Perubahan digunakan.\n\n' +
                'Lanjutkan menonaktifkan ' + dayName + '?';
            const confirmed = window.confirm(message);
            if (!confirmed) {
                this.checked = previousState;
                return;
            }
        }
        previousState = this.checked;
    });
});
</script>
</body>
</html>

