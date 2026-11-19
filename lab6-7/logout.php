<?php
declare(strict_types=1);

require_once __DIR__ . '/helper/bootstrap.php';

requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    renderHeader('Use the sign-out button');
    echo '<h1>Use the sign-out button</h1><p>Use the button in the navigation to end your session.</p>';
    renderFooter();
    exit;
}
if (!csrfValid()) {
    setFlash('error', 'The form expired. Please try again.');
    redirect(dashboardUrl());
}

logout();

redirect(url('auth/login.php') . '?logged_out=1');
