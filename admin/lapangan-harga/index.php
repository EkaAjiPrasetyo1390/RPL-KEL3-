<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Lapangan & Harga - Ramuna Futsal</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial,Helvetica,sans-serif;
    background:#e9f2fb;
    color:#142b63;
}

a{
    text-decoration:none;
    color:inherit;
}

.admin-content{
    width:100%;
    max-width:944px;
    min-height:100vh;
    margin:auto;
    background:#f7f5ef;
    padding-bottom:105px;
}

/* HEADER */
.page-header{
    min-height:145px; padding:22px 28px 24px;
    color:white;
    background:
        linear-gradient(135deg,rgba(5,36,82,.93),rgba(8,79,164,.72)),
        url('../../assets/images/admin/admin-bg.jpg') center/cover;
    border-radius:0 0 28px 28px;
}

.header-title h1{
    font-size:25px;
    font-weight:800;
    letter-spacing:-.4px;
}

.header-title p{
    margin-top:7px;
    font-size:13px;
    font-weight:500;
}

/* CONTENT */
.page-body{
    padding:24px 24px 0;
}

/* CARD */
.manage-card{
    position:relative;
    min-height:155px; margin-bottom:14px;
    border-radius:16px;
    overflow:hidden;
    display:flex;
    align-items:center;
    padding:17px 20px; }

.manage-card.field{
    background:#e5f0ff;
}

.manage-card.price{
    background:#fff0d9;
}

.manage-image{
    width:135px; height:125px;
    flex:0 0 155px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.manage-image img{
    width:100%;
    height:100%;
    object-fit:contain;
}

.manage-info{
    padding-left:14px; padding-right:55px;
}

.manage-info h2{
    font-size:20px;
    font-weight:800;
    color:#101f61;
    margin-bottom:9px;
}

.manage-info p{
    max-width:245px;
    font-size:13px;
    line-height:1.55;
    color:#5470b2;
    font-weight:500;
}

.price .manage-info p{
    color:#75644d;
}

.manage-link{
    position:absolute;
    left:22px;
    bottom:17px;
    font-size:13px;
    font-weight:800;
}

.field .manage-link{
    color:#0868e8;
}

.price .manage-link{
    color:#f17c00;
}

.manage-arrow{
    position:absolute;
    right:17px;
    bottom:17px;
    width:51px;
    height:51px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.field .manage-arrow{
    background:#0874ee;
}

.price .manage-arrow{
    background:#f57c00;
}

.manage-arrow svg{
    width:25px;
    height:25px;
    fill:none;
    stroke:white;
    stroke-width:2.6;
    stroke-linecap:round;
    stroke-linejoin:round;
}

/* INFO */
.info-box{
    display:flex;
    align-items:flex-start;
    gap:12px;
    margin-top:14px;
    padding:14px 16px;
    border-radius:15px;
    background:#e9f3ff;
}

.info-icon{
    width:32px;
    height:32px;
    flex:0 0 32px;
    border-radius:50%;
    background:#0874ee;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
}

.info-icon svg{
    width:19px;
    height:19px;
    fill:none;
    stroke:currentColor;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.info-box p{
    font-size:12px;
    line-height:1.55;
    color:#5572b0;
    padding-top:2px;
}

/* QUOTE */
.quote-area{
    min-height:245px;
    position:relative;
    margin-top:18px;
    overflow:hidden;
    display:flex;
    align-items:flex-end;
    justify-content:flex-end;
    padding:30px 25px 55px 190px;
}

.quote-ball{
    position:absolute;
    left:-38px;
    bottom:-62px;
    width:225px;
    height:225px;
    opacity:.10;
}

.quote-ball svg{
    width:100%;
    height:100%;
    fill:none;
    stroke:#2375dd;
    stroke-width:4;
}

.quote-text{
    position:relative;
    max-width:270px;
    text-align:left;
    color:#4867b3;
    font-size:14px;
    font-style:italic;
    line-height:1.45;
}

.quote-author{
    display:block;
    margin-top:7px;
    text-align:right;
    font-style:normal;
}

/* BOTTOM NAV */
.bottom-nav{
    position:fixed;
    z-index:50;
    left:50%;
    bottom:0;
    transform:translateX(-50%);
    width:min(944px,100%);
    height:94px;
    padding:10px 12px 9px;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    background:white;
    border-radius:22px 22px 0 0;
    box-shadow:0 -8px 25px rgba(25,55,80,.08);
}

.nav-item{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:5px;
    color:#4c65a5;
    font-size:12px;
    font-weight:700;
}

.nav-item svg{
    width:27px;
    height:27px;
    fill:none;
    stroke:currentColor;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.nav-item.active{
    color:#234da2;
}

/* RESPONSIVE */
@media(max-width:680px){

    body{
        background:#f7f5ef;
    }

    .page-header{
        min-height:120px;
        padding:17px 20px 19px;
        border-radius:0 0 22px 22px;
    }

    .header-title h1{
        font-size:22px;
    }

    .header-title p{
        font-size:12px;
    }

    .page-body{
        padding:24px 16px 0;
    }

    .manage-card{
        min-height:155px;
        padding:18px;
    }

    .manage-image{
        width:120px; height:115px;
        flex-basis:135px;
    }

    .manage-info{
        padding-left:12px;
        padding-right:55px;
    }

    .manage-info h2{
        font-size:18px;
    }

    .manage-info p{
        font-size:12px;
    }

    .manage-link{
        left:19px;
        bottom:17px;
    }

    .manage-arrow{
        width:47px;
        height:47px;
        right:15px;
        bottom:15px;
    }

    .quote-area{
        min-height:205px;
        padding-left:165px;
        padding-right:12px;
        padding-bottom:38px;
    }

    .quote-ball{
        width:195px;
        height:195px;
        left:-38px;
    }

    .quote-text{
        font-size:13px;
    }

    .bottom-nav{
        height:88px;
        border-radius:20px 20px 0 0;
    }
}

@media(max-width:390px){

    .manage-image{
        width:120px;
        height:120px;
        flex-basis:120px;
    }

    .manage-info{
        padding-left:9px;
        padding-right:48px;
    }

    .manage-info h2{
        font-size:17px;
    }

    .manage-info p{
        font-size:11px;
    }
}
</style>
</head>

<body>

<div class="admin-content">

<header class="page-header">
    <div class="header-title">
        <h1>Data Lapangan & Harga</h1>
        <p>Kelola data lapangan, harga sewa, dan harga booking.</p>
    </div>
</header>

<main class="page-body">

<a href="lapangan.php" class="manage-card field">

    <div class="manage-image">
        <img
            src="../../assets/images/admin/kelola-lapangan.png"
            alt="Kelola Lapangan"
        >
    </div>

    <div class="manage-info">
        <h2>Kelola Lapangan</h2>
        <p>
            Tambah, edit, dan kelola data lapangan futsal.
        </p>
    </div>

    <span class="manage-link">
        Kelola Lapangan&nbsp; →
    </span>

    <span class="manage-arrow">
        <svg viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7"></path>
        </svg>
    </span>

</a>


<a href="harga.php" class="manage-card price">

    <div class="manage-image">
        <img
            src="../../assets/images/admin/kelola-harga.png"
            alt="Kelola Harga"
        >
    </div>

    <div class="manage-info">
        <h2>Kelola Harga</h2>
        <p>
            Atur harga sewa lapangan dan harga booking berdasarkan rentang jam.
        </p>
    </div>

    <span class="manage-link">
        Kelola Harga&nbsp; →
    </span>

    <span class="manage-arrow">
        <svg viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7"></path>
        </svg>
    </span>

</a>


<div class="info-box">

    <div class="info-icon">
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 10v6"></path>
            <path d="M12 7h.01"></path>
        </svg>
    </div>

    <p>
        Data harga dan harga booking diambil dari data lapangan
        yang sudah ditambahkan.
    </p>

</div>


<div class="quote-area">

    <div class="quote-ball">
        <svg viewBox="0 0 220 220">
            <circle cx="110" cy="110" r="96"></circle>
            <path d="M110 14l31 42-31 36-49-17"></path>
            <path d="M110 92l31 36-20 52"></path>
            <path d="M110 14l-61 61 12 65"></path>
            <path d="M49 75l-35 35 45 30"></path>
            <path d="M141 56l45 20-16 53"></path>
        </svg>
    </div>

    <div class="quote-text">
        “Fasilitas yang baik,<br>
        melahirkan cerita yang lebih baik.”
        <span class="quote-author">— Ramuna Futsal</span>
    </div>

</div>

</main>


<nav class="bottom-nav">

<a href="../index.php" class="nav-item active">
    <svg viewBox="0 0 24 24">
        <path d="M3 10.5 12 3l9 7.5"></path>
        <path d="M5 9.5V21h14V9.5"></path>
        <path d="M9 21v-7h6v7"></path>
    </svg>
    <span>Beranda</span>
</a>

<a href="../reservasi/" class="nav-item">
    <svg viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
        <path d="M8 3v4"></path>
        <path d="M16 3v4"></path>
        <path d="M3 10h18"></path>
    </svg>
    <span>Kelola Reservasi</span>
</a>

<a href="../akun.php" class="nav-item">
    <svg viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="4"></circle>
        <path d="M4 21a8 8 0 0 1 16 0"></path>
    </svg>
    <span>Akun</span>
</a>

</nav>

</div>

</body>
</html>

