<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - Ramuna Futsal</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #071b3a 0%,
                    #0b3d91 55%,
                    #087cf5 100%
                );

            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
        }

        .brand {
            text-align: center;
            color: white;
            margin-bottom: 25px;
        }

        .brand h1 {
            font-size: 28px;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .brand p {
            font-size: 14px;
            opacity: .85;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .login-card h2 {
            color: #102a72;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .login-card .subtitle {
            color: #66739a;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            color: #243875;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            height: 48px;
            border: 1px solid #d9e2f2;
            border-radius: 12px;
            padding: 0 15px;
            font-size: 15px;
            outline: none;
            background: #f7faff;
        }

        input:focus {
            border-color: #1769ff;
            background: white;
        }

        .error {
            background: #ffe5ea;
            color: #c72545;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(
                90deg,
                #0757d9,
                #147df5
            );
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            opacity: .92;
        }

        .footer {
            text-align: center;
            color: rgba(255,255,255,.7);
            font-size: 12px;
            margin-top: 20px;
        }

        @media (max-width: 480px) {

            body {
                padding: 16px;
            }

            .login-card {
                padding: 25px 20px;
                border-radius: 20px;
            }

            .brand h1 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="brand">
            <h1>RAMUNA FUTSAL</h1>
            <p>Panel Administrasi</p>
        </div>

        <div class="login-card">

            <h2>Selamat Datang</h2>

            <p class="subtitle">
                Silakan masuk ke akun admin Anda.
            </p>

            <?php if ($error): ?>

                <div class="error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                action="../actions/admin/auth.php"
            >

                <div class="form-group">

                    <label for="phone">
                        Nomor WhatsApp
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="Masukkan nomor WhatsApp"
                        autocomplete="username"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn-login"
                >
                    Masuk
                </button>

            </form>

        </div>

        <div class="footer">
            Ramuna Futsal &copy; 2026
        </div>

    </div>

</body>
</html>
