<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

requireAdmin();

$today = date('Y-m-d');

/* Reservasi hari ini */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM reservations
    WHERE date = ?
    AND status != 'cancelled'
");
$stmt->execute([$today]);
$reservationsToday = (int) $stmt->fetchColumn();

/* Menunggu konfirmasi */
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM reservations
    WHERE status = 'pending'
");
$waitingConfirmation = (int) $stmt->fetchColumn();

/* Reservasi mendatang */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM reservations
    WHERE date >= ?
    AND status = 'confirmed'
");
$stmt->execute([$today]);
$upcomingReservations = (int) $stmt->fetchColumn();

/* Perlu perhatian */
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT reservation_id)
    FROM reservation_payments
    WHERE status = 'rejected'
");
$needAttention = (int) $stmt->fetchColumn();

$days = [
    'Sunday' => 'Min',
    'Monday' => 'Sen',
    'Tuesday' => 'Sel',
    'Wednesday' => 'Rab',
    'Thursday' => 'Kam',
    'Friday' => 'Jum',
    'Saturday' => 'Sab'
];

$months = [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'Mei',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Agu',
    9 => 'Sep',
    10 => 'Okt',
    11 => 'Nov',
    12 => 'Des'
];

$dayName = $days[date('l')];
$dateText = $dayName . ', ' . date('d') . ' ' . $months[(int) date('n')] . ' ' . date('Y');
$timeText = date('H:i') . ' WIB';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Beranda Admin - Ramuna Futsal</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-page">

<main class="admin-content">

<!-- =========================
     HERO
========================= -->

<section class="admin-hero">

    <div class="hero-inner">

        <div class="admin-brand-row">

            <div class="admin-brand"><img src="../assets/images/brand/ramuna-logo.png" alt="Ramuna Futsal" class="admin-logo"></div>

            <div class="admin-time">

                <?= $dateText ?><br>

                <strong>
                    <?= $timeText ?>
                </strong>

            </div>

        </div>

        <div class="hero-title">

            Selamat Datang,

            <span>Admin!</span>

        </div>

        <div class="hero-description">
            Bersama Ramuna Futsal, wujudkan pengalaman
            bermain yang lebih baik.
        </div>

        <div class="hero-line"></div>

    </div>

</section>


<!-- =========================
     DASHBOARD BODY
========================= -->

<div class="dashboard-body">


<!-- =====================
     RINGKASAN
====================== -->

<section class="dashboard-card summary-card">

    <div class="section-heading">

        <h2>
            Ringkasan Hari Ini
        </h2>

        <div class="date-label">

            <svg class="svg-icon" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>

            <?= $dateText ?>

        </div>

    </div>


    <div class="summary-grid">


        <div class="summary-item">

            <div class="summary-icon icon-blue">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <line x1="8" y1="14" x2="8" y2="14"/>
                    <line x1="12" y1="14" x2="12" y2="14"/>
                    <line x1="16" y1="14" x2="16" y2="14"/>
                </svg>

            </div>

            <div>

                <div class="summary-number">
                    <?= $reservationsToday ?>
                </div>

                <div class="summary-text">
                    Reservasi Hari Ini
                </div>

            </div>

        </div>


        <div class="summary-item">

            <div class="summary-icon icon-orange">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <polyline points="12 7 12 12 15 14"/>
                </svg>

            </div>

            <div>

                <div class="summary-number">
                    <?= $waitingConfirmation ?>
                </div>

                <div class="summary-text">
                    Menunggu Konfirmasi
                </div>

            </div>

        </div>


        <div class="summary-item">

            <div class="summary-icon icon-green">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <polyline points="8 15 11 18 16 13"/>
                </svg>

            </div>

            <div>

                <div class="summary-number">
                    <?= $upcomingReservations ?>
                </div>

                <div class="summary-text">
                    Reservasi Mendatang
                </div>

            </div>

        </div>


        <div class="summary-item">

            <div class="summary-icon icon-red">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.3 3.2L2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.2a2 2 0 0 0-3.4 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12" y2="17"/>
                </svg>

            </div>

            <div>

                <div class="summary-number">
                    <?= $needAttention ?>
                </div>

                <div class="summary-text">
                    Perlu Perhatian
                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================
     MENU ADMIN
====================== -->

<section class="dashboard-card menu-card">

    <div class="menu-heading">

        <h2>
            Menu Admin
        </h2>

        <p>
            Kelola sistem Ramuna Futsal dengan mudah.
        </p>

    </div>


    <div class="menu-grid">


        <a
            href="lapangan-harga/index.php"
            class="menu-button menu-field"
        >

            <span class="menu-icon">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="1"/>
                    <circle cx="12" cy="12" r="2.5"/>
                    <path d="M3 8h18M3 16h18"/>
                    <path d="M8 4v16M16 4v16"/>
                </svg>

            </span>

            <span class="menu-text">
                Data Lapangan<br>
                &amp; Harga
            </span>

        </a>


        <a
            href="customer/index.php"
            class="menu-button menu-customer"
        >

            <span class="menu-icon">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3.5 19c.7-3.2 2.5-5 5.5-5s4.8 1.8 5.5 5"/>
                    <path d="M16 5.5a3 3 0 0 1 0 5.8"/>
                    <path d="M17 14c2 .5 3.2 2 3.5 5"/>
                </svg>

            </span>

            <span class="menu-text">
                Data Customer
            </span>

        </a>


        <a
            href="jadwal/index.php"
            class="menu-button menu-schedule"
        >

            <span class="menu-icon">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="M8 14h3M8 17h3M14 14h2"/>
                </svg>

            </span>

            <span class="menu-text">
                Jadwal Lapangan
            </span>

        </a>


        <a
            href="pengaturan-reservasi/index.php"
            class="menu-button menu-settings"
        >

            <span class="menu-icon">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.8 1.8 0 0 0 .4 2l.1.1-1.8 1.8-.1-.1a1.8 1.8 0 0 0-2-.4 1.8 1.8 0 0 0-1.1 1.7V20h-2.6v-.1a1.8 1.8 0 0 0-1.1-1.7 1.8 1.8 0 0 0-2 .4l-.1.1-1.8-1.8.1-.1a1.8 1.8 0 0 0 .4-2 1.8 1.8 0 0 0-1.7-1.1H4v-2.6h.1a1.8 1.8 0 0 0 1.7-1.1 1.8 1.8 0 0 0-.4-2l-.1-.1 1.8-1.8.1.1a1.8 1.8 0 0 0 2 .4 1.8 1.8 0 0 0 1.1-1.7V4h2.6v.1a1.8 1.8 0 0 0 1.1 1.7 1.8 1.8 0 0 0 2-.4l.1-.1 1.8 1.8-.1.1a1.8 1.8 0 0 0-.4 2 1.8 1.8 0 0 0 1.7 1.1H20v2.6h-.1a1.8 1.8 0 0 0-1.7 1.1z"/>
                </svg>

            </span>

            <span class="menu-text">
                Pengaturan<br>
                Reservasi
            </span>

        </a>


        <a
            href="laporan/index.php"
            class="menu-button menu-report"
        >

            <span class="menu-icon">

                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v14"/>
                    <path d="M4 19h16"/>
                    <path d="M8 15v-4M12 15V8M16 15v-6"/>
                </svg>

            </span>

            <span class="menu-text">
                Laporan
            </span>

        </a>

    </div>

</section>


<!-- =====================
     QUOTE
====================== -->

<section class="dashboard-card quote-card">

    <div class="quote-mark">
        “
    </div>

    <div>
        Fasilitas yang baik,
        melahirkan cerita yang lebih baik.
    </div>

    <div class="quote-author">
        — Ramuna Futsal
    </div>

</section>

</div>


<!-- =========================
     BOTTOM NAVIGATION
========================= -->

<nav class="bottom-nav">

    <a
        href="index.php"
        class="nav-item active"
    >

        <span class="nav-icon">

            <svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10.5L12 3l9 7.5"/>
                <path d="M5 9.5V21h14V9.5"/>
                <path d="M9 21v-6h6v6"/>
            </svg>

        </span>

        <span>
            Beranda
        </span>

    </a>


    <a
        href="reservasi/index.php"
        class="nav-item"
    >

        <span class="nav-icon">

            <svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="3" width="16" height="18" rx="2"/>
                <path d="M8 7h8M8 11h8M8 15h5"/>
            </svg>

        </span>

        <span>
            Kelola Reservasi
        </span>

    </a>


    <a
        href="akun/index.php"
        class="nav-item"
    >

        <span class="nav-icon">

            <svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="3.5"/>
                <path d="M5 21c.8-4 3-6 7-6s6.2 2 7 6"/>
            </svg>

        </span>

        <span>
            Akun
        </span>

    </a>

</nav>

</main>

</div>

</body>
</html>

