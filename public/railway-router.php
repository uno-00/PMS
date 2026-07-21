<?php

declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if ($uri !== '/' && $uri !== '' && is_file(__DIR__.$uri)) {
    return false;
}

require_once __DIR__.'/index.php';
