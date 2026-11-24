<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: lapangan.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, name, image, status
    FROM fields
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);

$field = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$field) {
    header('Location: lapangan.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($name === '') {
        $error = 'Nama lapangan wajib diisi.';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = 'Status lapangan tidak valid.';
    } else {

        $imagePath = $field['image'];

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $error = 'Gambar gagal diupload.';

            } else {

                $allowedExtensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];

                $extension = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                if (!in_array($extension, $allowedExtensions, true)) {

                    $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';

                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {

                    $error = 'Ukuran gambar maksimal 5 MB.';

                } else {

                    $uploadDirectory =
                        __DIR__ . '/../../assets/images/fields';

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    $fileName =
                        'field_' .
                        time() .
                        '_' .
                        bin2hex(random_bytes(4)) .
                        '.' .
                        $extension;

                    $destination =
                        $uploadDirectory . '/' . $fileName;

                    if (
                        move_uploaded_file(
                            $_FILES['image']['tmp_name'],
                            $destination
                        )
                    ) {

                        $newImagePath =
                            'assets/images/fields/' . $fileName;

                        /*
                         * Hapus foto lama setelah foto baru berhasil
                         * disimpan.
                         */
                        if (!empty($field['image'])) {

                            $oldFile =
                                __DIR__ .
                                '/../../' .
                                ltrim($field['image'], '/');

                            if (
                                file_exists($oldFile) &&
                                is_file($oldFile)
                            ) {
                                unlink($oldFile);
                            }
                        }

                        $imagePath = $newImagePath;

                    } else {

                        $error = 'Gambar tidak dapat disimpan.';
                    }
                }
            }
        }

        if ($error === '') {

            try {

                $stmt = $pdo->prepare("
                    UPDATE fields
                    SET
                        name = ?,
                        image = ?,
                        status = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $name,
                    $imagePath,
                    $status,
                    $id
                ]);

                header('Location: lapangan.php?success=edit');
                exit;

            } catch (PDOException $e) {

                $error = 'Data lapangan gagal diperbarui.';
            }
        }
    }
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

<title>Edit Lapangan - Ramuna Futsal</title>

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
    padding-bottom:40px;
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

.header-text h1{
    font-size:22px;
    font-weight:800;
}

.header-text p{
    margin-top:5px;
    font-size:12px;
}

/* CONTENT */

.page-body{
    padding:18px 16px;
}

.form-card{
    background:white;
    border-radius:17px;
    padding:20px;
    box-shadow:0 4px 15px rgba(25,55,90,.06);
}

.form-title{
    font-size:18px;
    font-weight:800;
}

.form-description{
    margin-top:5px;
    margin-bottom:20px;
    font-size:12px;
    color:#71809e;
}

/* CURRENT IMAGE */

.current-image{
    width:100%;
    height:180px;
    margin-bottom:20px;
    border-radius:13px;
    overflow:hidden;
    background:#e7eef5;
}

.current-image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* ALERT */

.alert{
    margin-bottom:15px;
    padding:13px 14px;
    border-radius:12px;
    background:#fff0ed;
    border:1px solid #f0d2cd;
    color:#99463d;
    font-size:12px;
    font-weight:700;
}

/* FORM */

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:7px;
    font-size:12px;
    font-weight:800;
    color:#243b72;
}

.form-group input[type="text"],
.form-group select{
    width:100%;
    height:46px;
    padding:0 13px;
    border:1px solid #dbe3ec;
    border-radius:11px;
    outline:none;
    background:#fbfcfd;
    color:#1c315e;
    font-size:13px;
}

.form-group input[type="text"]:focus,
.form-group select:focus{
    border-color:#287be0;
    background:white;
}

.file-box{
    border:1.5px dashed #bfd1e4;
    border-radius:13px;
    padding:16px;
    background:#f7fbff;
}

.file-box input{
    width:100%;
    font-size:12px;
}

.file-help{
    margin-top:8px;
    font-size:10px;
    color:#8492a2;
}

/* BUTTON */

.form-actions{
    display:flex;
    gap:10px;
    margin-top:23px;
}

.btn{
    flex:1;
    min-height:43px;
    border-radius:11px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    font-size:12px;
    font-weight:800;
    cursor:pointer;
    border:0;
}

.btn-cancel{
    background:#eef3f8;
    color:#53657b;
}

.btn-save{
    background:#0874ee;
    color:white;
    box-shadow:0 6px 15px rgba(8,116,238,.18);
}

.btn svg{
    width:17px;
    height:17px;
    fill:none;
    stroke:currentColor;
    stroke-width:2.2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

@media(max-width:680px){

    body{
        background:#f7f5ef;
    }

    .page-header{
        min-height:120px;
        padding:17px;
    }

    .page-body{
        padding:16px;
    }

    .form-card{
        padding:17px;
    }

    .current-image{
        height:165px;
    }
}

</style>

</head>

<body>

<div class="admin-content">

<header class="page-header">

    <div class="header-row">

        <a href="lapangan.php" class="back-button">

            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6"></path>
            </svg>

        </a>

        <div class="header-text">

            <h1>Edit Lapangan</h1>

            <p>
                Perbarui data lapangan
            </p>

        </div>

    </div>

</header>


<main class="page-body">

<div class="form-card">

    <h2 class="form-title">
        Data Lapangan
    </h2>

    <p class="form-description">
        Perbarui informasi lapangan Ramuna Futsal.
    </p>


    <?php if ($error): ?>

        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($field['image'])): ?>

        <?php
        $currentImage =
            '../../' . ltrim($field['image'], '/');

        $currentImageFile =
            __DIR__ .
            '/../../' .
            ltrim($field['image'], '/');
        ?>

        <?php if (file_exists($currentImageFile)): ?>

            <div class="current-image">

                <img
                    src="<?= htmlspecialchars($currentImage) ?>"
                    alt="<?= htmlspecialchars($field['name']) ?>"
                >

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-group">

            <label for="name">
                Nama Lapangan
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($field['name']) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="status">
                Status
            </label>

            <select id="status" name="status">

                <option
                    value="active"
                    <?= $field['status'] === 'active' ? 'selected' : '' ?>
                >
                    Aktif
                </option>

                <option
                    value="inactive"
                    <?= $field['status'] === 'inactive' ? 'selected' : '' ?>
                >
                    Nonaktif
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="image">
                Ganti Foto Lapangan
            </label>

            <div class="file-box">

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <p class="file-help">
                    Kosongkan jika foto tidak ingin diganti.
                    Maksimal 5 MB.
                </p>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="lapangan.php"
                class="btn btn-cancel"
            >
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-save"
            >

                <svg viewBox="0 0 24 24">
                    <path d="M5 12l4 4L19 6"></path>
                </svg>

                Simpan Perubahan

            </button>

        </div>

    </form>

</div>

</main>

</div>

</body>
</html>
