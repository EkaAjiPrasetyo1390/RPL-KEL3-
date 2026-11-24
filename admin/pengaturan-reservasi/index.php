<?php
require_once "../../config/database.php";
require_once "../../config/auth.php";
requireAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Reservasi - Ramuna Futsal</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#e9f2fb;color:#12345a}
.admin-content{width:min(100%,944px);min-height:100vh;margin:0 auto;background:#f7f5ef;padding-bottom:90px}
.top-header{min-height:120px;padding:17px 27px;display:flex;align-items:center;gap:8px;background:linear-gradient(135deg,rgba(3,35,78,.94),rgba(8,83,165,.70)),url("../../assets/images/admin/admin-bg.jpg") center/cover no-repeat;border-radius:0 0 25px 25px;color:#fff}
.back-button{width:34px;height:34px;min-width:34px;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none}
.back-icon{width:23px;height:23px}
.back-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
.header-text{min-width:0}
.page-title{font-size:24px;line-height:1.15;font-weight:800;color:#fff;margin-bottom:4px}
.page-subtitle{font-size:12px;line-height:1.4;color:rgba(255,255,255,.86)}
.content{padding:20px 27px 0}
.menu-list{display:flex;flex-direction:column;gap:12px}
.menu-card{min-height:105px;padding:17px 19px;display:flex;align-items:center;gap:17px;border-radius:17px;text-decoration:none;border:1px solid rgba(180,205,225,.65);box-shadow:0 4px 12px rgba(24,61,98,.07)}
.menu-card.blue{background:#e5f1ff}.menu-card.green{background:#e5f8f0}.menu-card.orange{background:#fff0d9}
.menu-icon{width:58px;height:58px;min-width:58px;display:flex;align-items:center;justify-content:center;border-radius:14px}
.blue .menu-icon{background:#cce5ff;color:#1475c9}.green .menu-icon{background:#c9f0dc;color:#15935c}.orange .menu-icon{background:#ffe0a8;color:#e68700}
.menu-icon svg{width:30px;height:30px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.menu-info{flex:1;min-width:0}
.menu-title{font-size:17px;font-weight:800;color:#123f70;margin-bottom:5px}
.menu-description{font-size:12px;line-height:1.45;color:#60758b}
.menu-arrow{width:32px;height:32px;min-width:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#fff}
.blue .menu-arrow{background:#1678d2}.green .menu-arrow{background:#15935c}.orange .menu-arrow{background:#ef8a00}
.menu-arrow svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.info-box{margin-top:18px;padding:14px 16px;display:flex;gap:10px;background:#edf6ff;border:1px solid #cfe4f7;border-radius:13px;color:#58718a;font-size:11px;line-height:1.5}
.info-icon{width:20px;height:20px;min-width:20px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#1769aa;color:#fff;font-size:12px;font-weight:800}
.info-title{font-weight:800;color:#164d79;margin-bottom:2px}
.bottom-nav{position:fixed;left:50%;bottom:0;transform:translateX(-50%);width:min(100%,944px);height:70px;background:#fff;border-top:1px solid #dce4eb;display:flex;align-items:center;justify-content:space-around;z-index:100;box-shadow:0 -4px 15px rgba(31,65,94,.08)}
.nav-item{width:33.333%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;text-decoration:none;color:#8b96a3;font-size:12px;font-weight:700;text-shadow:0 1px 1px rgba(0,0,0,.08)}
.nav-icon{width:21px;height:21px}.nav-icon svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
@media(max-width:700px){
.admin-content{width:100%}
.top-header{min-height:120px;padding:17px 20px;border-radius:0 0 22px 22px}
.page-title{font-size:22px}.page-subtitle{font-size:11px}
.content{padding:18px 14px 0}
.menu-card{min-height:94px;padding:14px;gap:12px}
.menu-icon{width:52px;height:52px;min-width:52px}
.menu-title{font-size:15px}.menu-description{font-size:10px}
.menu-arrow{width:29px;height:29px;min-width:29px}
.bottom-nav{width:100%;height:68px}
}
/* FINAL MENU CARD STYLE - SESUAI MOCKUP */
.menu-card {
    background: #ffffff !important;
    border: 1px solid #dce7f0 !important;
    box-shadow: 0 3px 10px rgba(18,63,112,.06) !important;
}
.menu-card.blue,
.menu-card.green,
.menu-card.orange {
    background: #ffffff !important;
}
.menu-icon {
    border-radius: 11px !important;
}
/* Icon biru */
.blue .menu-icon {
    background: #e3f0ff !important;
    color: #1678d2 !important;
}
/* Icon hijau */
.green .menu-icon {
    background: #def7eb !important;
    color: #15935c !important;
}
/* Icon orange */
.orange .menu-icon {
    background: #fff0d4 !important;
    color: #ed8a00 !important;
}
/* Judul semua card */
.menu-title {
    color: #123f70 !important;
    font-size: 16px !important;
    font-weight: 800 !important;
}
/* Deskripsi */
.menu-description {
    color: #657b91 !important;
    font-size: 11px !important;
}
/* Panah */
.blue .menu-arrow {
    background: #1678d2 !important;
}
.green .menu-arrow {
    background: #15935c !important;
}
.orange .menu-arrow {
    background: #ed8a00 !important;
}
@media (max-width: 700px) {
    .menu-card {
        min-height: 76px !important;
        padding: 11px 12px !important;
        gap: 11px !important;
        border-radius: 10px !important;
    }
    .menu-icon {
        width: 43px !important;
        height: 43px !important;
        min-width: 43px !important;
        border-radius: 9px !important;
    }
    .menu-icon svg {
        width: 23px !important;
        height: 23px !important;
    }
    .menu-title {
        font-size: 13px !important;
        margin-bottom: 3px !important;
    }
    .menu-description {
        font-size: 9px !important;
        line-height: 1.35 !important;
    }
    .menu-arrow {
        width: 25px !important;
        height: 25px !important;
        min-width: 25px !important;
    }
    .menu-arrow svg {
        width: 15px !important;
        height: 15px !important;
    }
}
</style>
</head>
<body>
<div class="admin-content">
<header class="top-header">
<a href="../index.php" class="back-button" aria-label="Kembali">
<span class="back-icon"><svg viewBox="0 0 24 24"><path d="M19 12H5"></path><path d="M11 18l-6-6 6-6"></path></svg></span>
</a>
<div class="header-text">
<h1 class="page-title">Pengaturan Reservasi</h1>
<p class="page-subtitle">Atur ketentuan reservasi dan pembayaran customer.</p>
</div>
</header>
<main class="content">
<div class="menu-list">
<a href="jam-operasional.php" class="menu-card blue">
<div class="menu-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3 2"></path></svg></div>
<div class="menu-info"><div class="menu-title">Jam Operasional</div><div class="menu-description">Atur jam buka dan tutup lapangan.</div></div>
<div class="menu-arrow"><svg viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></div>
</a>
<a href="rentang-tanggal.php" class="menu-card green">
<div class="menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M7 3v4"></path><path d="M17 3v4"></path><path d="M3 10h18"></path><path d="M8 14h3"></path><path d="M8 17h5"></path></svg></div>
<div class="menu-info"><div class="menu-title">Rentang Tanggal</div><div class="menu-description">Tentukan maksimal hari ke depan untuk pemesanan oleh customer.</div></div>
<div class="menu-arrow"><svg viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></div>
</a>
<a href="pembayaran.php" class="menu-card orange">
<div class="menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h4"></path><path d="M15 15h2"></path></svg></div>
<div class="menu-info"><div class="menu-title">Pembayaran</div><div class="menu-description">Kelola metode pembayaran yang tersedia untuk customer.</div></div>
<div class="menu-arrow"><svg viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg></div>
</a>
</div>
<div class="info-box">
<span class="info-icon">i</span>
<div><div class="info-title">Pengaturan Reservasi</div>Pengaturan di halaman ini akan digunakan dalam proses reservasi customer.</div>
</div>
</main>
<nav class="bottom-nav">
<a href="../index.php" class="nav-item"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"></path><path d="M5.5 9.5V21h13V9.5"></path><path d="M9.5 21v-6h5v6"></path></svg></span><span>Beranda</span></a>
<a href="../reservasi/index.php" class="nav-item"><span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M7 3v4"></path><path d="M17 3v4"></path><path d="M3 10h18"></path></svg></span><span>Kelola Reservasi</span></a>
<a href="../akun/index.php" class="nav-item"><span class="nav-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c.8-4 3.5-6 8-6s7.2 2 8 6"></path></svg></span><span>Akun</span></a>
</nav>
</div>
</body>
</html>

