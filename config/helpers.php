<?php

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function redirect($url)
{
    header("Location: $url");
    exit;
}
