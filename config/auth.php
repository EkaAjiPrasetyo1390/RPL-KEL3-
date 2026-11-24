<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        header('Location: /ramuna-futsal/admin/login.php');
        exit;
    }
}

function adminLogin($admin)
{
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_phone'] = $admin['phone'];
}

function adminLogout()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
