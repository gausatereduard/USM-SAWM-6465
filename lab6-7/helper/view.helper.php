<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.helper.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function renderHeader(string $title): void
{
    $role = currentRole();
    $flash = pullFlash();
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> — SAWM Workspace</title>
    <link rel="stylesheet" href="<?= e(url('assets/graphite.css')) ?>">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= e(url('index.php')) ?>">SAWM <span>WORKSPACE</span></a>
    <nav aria-label="Workspace">
        <?php if (isAuthenticated()): ?>
            <a href="<?= e(dashboardUrl()) ?>">Dashboard</a>
            <?php if ($role === ROLE_ADMINISTRATOR): ?>
                <a href="<?= e(url('admin/users/index.php')) ?>">Accounts</a>
                <a href="<?= e(url('admin/dashboard.php') . '#journals') ?>">Journals</a>
            <?php elseif ($role === ROLE_MANAGER): ?>
                <a href="<?= e(url('manager/reports.php')) ?>">Reports</a>
                <a href="<?= e(url('manager/profile.php')) ?>">Profile</a>
            <?php endif; ?>
            <span class="badge"><?= e(roleLabel((string) $role)) ?></span>
            <form action="<?= e(url('logout.php')) ?>" method="post">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-secondary btn-small">Log out</button>
            </form>
        <?php else: ?>
            <a href="<?= e(url('auth/login.php')) ?>">Sign in</a>
            <a class="btn btn-small" href="<?= e(url('auth/signup.php')) ?>">Sign up</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page">
<?php if ($flash !== null): ?>
    <div class="alert alert-<?= e((string) ($flash['type'] ?? 'info')) ?>"><?= e((string) ($flash['message'] ?? '')) ?></div>
<?php endif; ?>
<?php
}

function renderFooter(): void
{
    ?>
</main>
<footer>SAWM · Access control, activity and error logging</footer>
</body>
</html>
<?php
}
