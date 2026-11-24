<?php

require_once __DIR__ . '/../config/auth.php';

adminLogout();

header('Location: login.php');
exit;
