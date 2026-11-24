<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/login.php');
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if ($phone === '' || $password === '') {
    $_SESSION['login_error'] = 'Nomor WhatsApp dan password wajib diisi.';
    header('Location: ../../admin/login.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, name, phone, password_hash
     FROM admins
     WHERE phone = ?
     LIMIT 1"
);

$stmt->execute([$phone]);

$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    $_SESSION['login_error'] = 'Nomor WhatsApp atau password salah.';
    header('Location: ../../admin/login.php');
    exit;
}

adminLogin($admin);

header('Location: ../../admin/index.php');
exit;
