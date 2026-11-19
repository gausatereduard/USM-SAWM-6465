<?php
declare(strict_types=1);

require_once __DIR__ . '/helper/bootstrap.php';

$loggedIn = isAuthenticated();

renderHeader('Home');
?>
<section class="hero">
    <p class="eyebrow">ACCOUNT WORKSPACE</p>
    <h1>Manage access.<br>Review activity.</h1>
    <p class="lead">A single workspace for account management, reports and security journals.</p>
    <?php if ($loggedIn): ?>
        <p>
            <a class="btn" href="<?= e(dashboardUrl()) ?>">Open my dashboard</a>
        </p>
        <p class="small muted">Signed in as <strong><?= e((string) ($_SESSION['username'] ?? '')) ?></strong>
            with role <strong><?= e(roleLabel((string) (currentRole() ?? ''))) ?></strong>.</p>
    <?php else: ?>
        <p>
            <a class="btn" href="<?= e(url('auth/login.php')) ?>">Sign in</a>
            <a class="btn btn-secondary" href="<?= e(url('auth/signup.php')) ?>">Sign up</a>
        </p>
    <?php endif; ?>
</section>
<?php renderFooter();
