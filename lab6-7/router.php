<?php
declare(strict_types=1);

// Local web entry point. Never let the development server serve arbitrary project files.
require_once __DIR__ . '/helper/error.helper.php';
installErrorHandling();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$routes = [
    '/' => 'index.php', '/index.php' => 'index.php', '/logout.php' => 'logout.php',
    '/auth/login.php' => 'auth/login.php', '/auth/signup.php' => 'auth/signup.php',
    '/admin/dashboard.php' => 'admin/dashboard.php',
    '/admin/users/index.php' => 'admin/users/index.php', '/admin/users/edit.php' => 'admin/users/edit.php',
    '/admin/users/delete.php' => 'admin/users/delete.php',
    '/manager/dashboard.php' => 'manager/dashboard.php', '/manager/profile.php' => 'manager/profile.php',
    '/manager/reports.php' => 'manager/reports.php',
];
if ($path === '/assets/graphite.css') {
    header('Content-Type: text/css; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    readfile(__DIR__ . '/assets/graphite.css');
    return;
}

if (!is_string($path) || !isset($routes[$path])) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Page not found — SAWM</title>'
        . '<link rel="stylesheet" href="/assets/graphite.css"><main class="page"><section class="card">'
        . '<h1>Page not found</h1><p>Check the address or return to the home page.</p>'
        . '<a class="btn" href="/">Return home</a></section></main></html>';
    return;
}

require __DIR__ . '/' . $routes[$path];
