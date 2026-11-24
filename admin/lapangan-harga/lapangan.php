<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdmin();

if (isset($_POST['hapus_id']) && is_numeric($_POST['hapus_id'])) {

    $hapusId = (int) $_POST['hapus_id'];

    try {
        $stmtHapus = $pdo->prepare("DELETE FROM fields WHERE id = ?");
        $stmtHapus->execute([$hapusId]);

        header("Location: lapangan.php?success=hapus");
        exit;

    } catch (PDOException $e) {

        header("Location: lapangan.php?error=hapus");
        exit;
    }
}

$stmt = $pdo->query("
    SELECT id, name, image, status
    FROM fields
    ORDER BY id ASC
");

$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Kelola Lapangan - Ramuna Futsal</title>

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
    min-height:120px;
    padding:17px 27px;
    color:white;

    background:
        linear-gradient(
            135deg,
            rgba(5,36,82,.93),
            rgba(8,79,164,.72)
        ),
        url('../../assets/images/admin/admin-bg.jpg') center/cover;

    border-radius:0 0 24px 24px;
}

.header-row{
    display:flex;
    align-items:center;
    gap:10px;
}

.back-button{
    width:38px;
    height:40px;

    display:flex;
    align-items:center;
    justify-content:center;
}

.back-button svg{
    width:27px;
    height:27px;
    fill:none;
    stroke:white;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.header-text{
    flex:1;
}

.header-text h1{
    font-size:22px;
    font-weight:800;
}

.header-text p{
    margin-top:5px;
    font-size:12px;
}

.add-button{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;

    padding:10px 13px;

    border-radius:9px;

    background:#0874ee;
    color:white;

    font-size:11px;
    font-weight:800;

    white-space:nowrap;
}

.add-button svg{
    width:17px;
    height:17px;
    fill:none;
    stroke:white;
    stroke-width:2.2;
    stroke-linecap:round;
}

/* CONTENT */

.page-body{
    padding:14px 16px 0;
}

/* ALERT */

.alert-success,
.alert-error{
    margin-bottom:12px;
    padding:11px 13px;
    border-radius:11px;
    font-size:11px;
    font-weight:700;
}

.alert-success{
    color:#17663e;
    background:#eaf8f0;
    border:1px solid #ccebd9;
}

.alert-error{
    color:#99463d;
    background:#fff0ed;
    border:1px solid #f0d2cd;
}

/* SEARCH */

.search-box{
    height:46px;

    margin-bottom:12px;
    padding:0 14px;

    display:flex;
    align-items:center;
    gap:10px;

    background:#eaf2fc;
    border-radius:12px;
}

.search-box svg{
    width:22px;
    height:22px;

    fill:none;
    stroke:#4868ad;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.search-box input{
    width:100%;

    border:0;
    outline:0;

    background:transparent;

    color:#142b63;
    font-size:13px;
}

.search-box input::placeholder{
    color:#5974b2;
}

/* FIELD LIST */

.field-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.field-card{
    min-height:112px;

    display:flex;
    align-items:center;

    gap:13px;

    padding:9px;

    background:white;

    border-radius:14px;

    box-shadow:
        0 4px 13px rgba(25,55,90,.06);
}

.field-photo{
    width:105px;
    height:94px;

    flex:0 0 105px;

    border-radius:10px;

    overflow:hidden;

    background:#dfe8ef;
}

.field-photo img{
    width:100%;
    height:100%;

    display:block;

    object-fit:cover;
}

.field-placeholder{
    width:100%;
    height:100%;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#e6eef5;

    color:#7790a3;
}

.field-placeholder svg{
    width:34px;
    height:34px;

    fill:none;
    stroke:currentColor;
    stroke-width:1.5;

    stroke-linecap:round;
    stroke-linejoin:round;
}

.field-content{
    flex:1;
    min-width:0;
}

.field-top{
    display:flex;
    align-items:center;
    gap:8px;

    flex-wrap:wrap;
}

.field-name{
    font-size:15px;
    font-weight:800;

    color:#10225e;
}

.status{
    padding:5px 10px;

    border-radius:999px;

    font-size:9px;
    font-weight:800;
}

.status.active{
    color:#14915c;
    background:#dff7eb;
}

.status.inactive{
    color:#f04457;
    background:#ffe5ea;
}

.field-location{
    display:flex;
    align-items:center;

    gap:6px;

    margin-top:9px;

    font-size:11px;

    color:#6075b1;
}

.field-location svg{
    width:17px;
    height:17px;

    flex:0 0 17px;

    fill:none;
    stroke:#506bb0;
    stroke-width:2;

    stroke-linecap:round;
    stroke-linejoin:round;
}

/* ACTION BUTTONS */

.action-buttons{
    width:70px;

    flex:0 0 70px;

    display:flex;
    flex-direction:column;

    gap:6px;
}

.action-btn{
    width:100%;
    height:32px;

    display:flex;
    align-items:center;
    justify-content:center;

    gap:5px;

    border-radius:8px;

    font-size:9px;
    font-weight:800;

    cursor:pointer;

    border:1px solid transparent;
}

.action-btn svg{
    width:13px;
    height:13px;

    fill:none;
    stroke:currentColor;
    stroke-width:2;

    stroke-linecap:round;
    stroke-linejoin:round;
}

.edit-btn{
    background:#e8f2ff;
    border-color:#cfe2fa;
    color:#1268a8;
}

.delete-btn{
    background:#fff0ed;
    border-color:#f1d3ce;
    color:#b34b40;
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

    box-shadow:
        0 -8px 25px rgba(25,55,80,.08);
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

/* MOBILE */

@media(max-width:680px){

    body{
        background:#f7f5ef;
    }

    .page-header{
        min-height:120px;
        padding:17px;
    }

    .header-text h1{
        font-size:21px;
    }

    .header-text p{
        font-size:11px;
    }

    .add-button{
        padding:9px 10px;
    }

    .page-body{
        padding:14px 16px 0;
    }

    .field-card{
        min-height:110px;
        gap:10px;
    }

    .field-photo{
        width:96px;
        height:92px;
        flex-basis:96px;
    }

    .field-name{
        font-size:14px;
    }

    .field-location{
        font-size:10px;
    }

    .action-buttons{
        width:65px;
        flex-basis:65px;
    }

    .action-btn{
        height:31px;
    }

    .bottom-nav{
        height:88px;
        border-radius:20px 20px 0 0;
    }
}

@media(max-width:390px){

    .field-photo{
        width:90px;
        height:88px;
        flex-basis:90px;
    }

    .action-buttons{
        width:60px;
        flex-basis:60px;
    }

    .action-btn{
        font-size:8px;
    }

}

</style>

</head>

<body>

<div class="admin-content">

<header class="page-header">

    <div class="header-row">

        <a href="index.php" class="back-button">

            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6"></path>
            </svg>

        </a>

        <div class="header-text">

            <h1>Kelola Lapangan</h1>

            <p>
                Kelola master data lapangan futsal.
            </p>

        </div>

        <a
            href="tambah-lapangan.php"
            class="add-button"
        >

            <svg viewBox="0 0 24 24">
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
            </svg>

            Tambah

        </a>

    </div>

</header>


<main class="page-body">

<?php if (
    isset($_GET['success']) &&
    $_GET['success'] === 'hapus'
): ?>

<div class="alert-success">
    Lapangan berhasil dihapus.
</div>

<?php endif; ?>


<?php if (
    isset($_GET['error']) &&
    $_GET['error'] === 'hapus'
): ?>

<div class="alert-error">
    Lapangan tidak dapat dihapus karena masih digunakan.
</div>

<?php endif; ?>


<div class="search-box">

    <svg viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="7"></circle>
        <path d="m20 20-4-4"></path>
    </svg>

    <input
        type="text"
        id="searchField"
        placeholder="Cari nama lapangan..."
        autocomplete="off"
    >

</div>


<div class="field-list" id="fieldList">

<?php foreach($fields as $field): ?>

<article
    class="field-card"
    data-name="<?= htmlspecialchars(
        strtolower($field['name'])
    ) ?>"
>

    <div class="field-photo">

    <?php

    $image = $field['image'] ?? '';

    $imagePath = '';

    if($image){

        $imagePath =
            '../../' .
            ltrim($image,'/');
    }

    $imageFile =
        __DIR__ .
        '/../../' .
        ltrim($image,'/');

    ?>

    <?php if(
        $image &&
        file_exists($imageFile)
    ): ?>

        <img
            src="<?= htmlspecialchars($imagePath) ?>"
            alt="<?= htmlspecialchars($field['name']) ?>"
        >

    <?php else: ?>

        <div class="field-placeholder">

            <svg viewBox="0 0 24 24">

                <rect
                    x="3"
                    y="4"
                    width="18"
                    height="16"
                    rx="2"
                ></rect>

                <path d="M8 4v16"></path>
                <path d="M16 4v16"></path>
                <path d="M3 12h18"></path>

                <circle
                    cx="12"
                    cy="12"
                    r="2"
                ></circle>

            </svg>

        </div>

    <?php endif; ?>

    </div>


    <div class="field-content">

        <div class="field-top">

            <h2 class="field-name">
                <?= htmlspecialchars($field['name']) ?>
            </h2>

            <?php if(
                $field['status'] === 'active'
            ): ?>

                <span class="status active">
                    Aktif
                </span>

            <?php else: ?>

                <span class="status inactive">
                    Nonaktif
                </span>

            <?php endif; ?>

        </div>


        <div class="field-location">

            <svg viewBox="0 0 24 24">

                <path
                    d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
                ></path>

                <circle
                    cx="12"
                    cy="10"
                    r="2.5"
                ></circle>

            </svg>

            <span>Indoor</span>

        </div>

    </div>


    <div class="action-buttons">

        <a
            href="edit-lapangan.php?id=<?= (int)$field['id'] ?>"
            class="action-btn edit-btn"
        >

            <svg viewBox="0 0 24 24">

                <path d="M12 20h9"></path>

                <path
                    d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"
                ></path>

            </svg>

            Edit

        </a>


        <form
            method="POST"
            style="margin:0;"
            onsubmit="return confirm('Hapus lapangan ini?');"
        >

            <input
                type="hidden"
                name="hapus_id"
                value="<?= (int)$field['id'] ?>"
            >

            <button
                type="submit"
                class="action-btn delete-btn"
            >

                <svg viewBox="0 0 24 24">

                    <path d="M3 6h18"></path>

                    <path d="M8 6V4h8v2"></path>

                    <path
                        d="M19 6l-1 15H6L5 6"
                    ></path>

                    <path d="M10 11v6"></path>

                    <path d="M14 11v6"></path>

                </svg>

                Hapus

            </button>

        </form>

    </div>

</article>

<?php endforeach; ?>

</div>

</main>


<nav class="bottom-nav">

<a
    href="../index.php"
    class="nav-item active"
>

    <svg viewBox="0 0 24 24">

        <path d="M3 10.5 12 3l9 7.5"></path>

        <path d="M5 9.5V21h14V9.5"></path>

        <path d="M9 21v-7h6v7"></path>

    </svg>

    <span>Beranda</span>

</a>


<a
    href="../reservasi/"
    class="nav-item"
>

    <svg viewBox="0 0 24 24">

        <rect
            x="3"
            y="5"
            width="18"
            height="16"
            rx="2"
        ></rect>

        <path d="M8 3v4"></path>
        <path d="M16 3v4"></path>
        <path d="M3 10h18"></path>

    </svg>

    <span>Kelola Reservasi</span>

</a>


<a
    href="../akun.php"
    class="nav-item"
>

    <svg viewBox="0 0 24 24">

        <circle
            cx="12"
            cy="8"
            r="4"
        ></circle>

        <path
            d="M4 21a8 8 0 0 1 16 0"
        ></path>

    </svg>

    <span>Akun</span>

</a>

</nav>

</div>


<script>

const searchField =
    document.getElementById('searchField');

const cards =
    document.querySelectorAll('.field-card');

searchField.addEventListener(
    'input',
    function(){

        const keyword =
            this.value.toLowerCase().trim();

        cards.forEach(function(card){

            const name =
                card.dataset.name;

            card.style.display =
                name.includes(keyword)
                ? 'flex'
                : 'none';

        });

    }
);

</script>

</body>
</html>
