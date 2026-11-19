<?php
declare(strict_types=1);

require_once __DIR__ . '/../helper/bootstrap.php';

// Role-based access control: administrators only.
requireRole(ROLE_ADMINISTRATOR);

$counts = countUsersByRole();
$totalUsers = countUsers();
$administrators = $counts[ROLE_ADMINISTRATOR] ?? 0;
$managers = $counts[ROLE_MANAGER] ?? 0;
$activeSessions = countActiveSessions();
$journal = ($_GET['journal'] ?? '') === 'errors' ? 'errors' : 'activity';
$entries = recentLog($journal);
header('Cache-Control: no-store');

renderHeader('Administrator dashboard');
?>
<h1>Administrator dashboard</h1>
<p class="lead">Signed in as <strong><?= e((string) ($_SESSION['username'] ?? '')) ?></strong> — this workspace requires the
    <span class="badge role-administrator">administrator</span> role.</p>

<div class="grid">
    <div class="card">
        <p class="small muted">Accounts</p>
        <p class="stat"><?= $totalUsers ?></p>
        <p class="small muted"><?= $administrators ?> administrator(s) · <?= $managers ?> manager(s)</p>
    </div>
    <div class="card">
        <p class="small muted">Active sessions</p>
        <p class="stat"><?= $activeSessions ?></p>
        <p class="small muted">Stored server-side in the <code>sessions</code> table</p>
    </div>
    <div class="card">
        <p class="small muted">My permissions</p>
        <p class="small">
            <?php foreach (permissions() as $permission): ?>
                <span class="badge"><?= e($permission) ?></span>
            <?php endforeach; ?>
        </p>
    </div>
</div>

<div class="card">
    <h2>User management</h2>
    <p class="muted small">Administrators can view, edit and delete accounts. These pages are also protected by
        permission checks (<code>users.view</code>, <code>users.update</code>, <code>users.delete</code>).</p>
    <p>
        <a class="btn" href="<?= e(url('admin/users/index.php')) ?>">Manage users</a>
    </p>
</div>
<section class="card" id="journals">
    <h2>Journals</h2>
    <p class="muted">Up to 100 recent entries from the current file, newest first. Times are in UTC.</p>
    <p class="actions">
        <a class="btn <?= $journal === 'activity' ? '' : 'btn-secondary' ?>" href="?journal=activity#journals">Activity</a>
        <a class="btn <?= $journal === 'errors' ? '' : 'btn-secondary' ?>" href="?journal=errors#journals">Errors</a>
    </p>
    <?php if ($entries === null): ?>
        <p class="muted">The journal is not available. Check again later.</p>
    <?php elseif ($entries === []): ?>
        <p class="muted">No entries yet.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Time</th><th><?= $journal === 'activity' ? 'Action' : 'Type' ?></th><th>Details</th></tr></thead>
            <tbody>
            <?php foreach ($entries as $line): ?>
                <?php $entry = json_decode($line, true); if (!is_array($entry)) { continue; } ?>
                <tr>
                    <td><?= e((string) ($entry['time'] ?? '')) ?></td>
                    <?php if ($journal === 'activity'): ?>
                        <td><code><?= e((string) ($entry['event'] ?? '')) ?></code></td>
                        <td><?= e((string) ($entry['result'] ?? '')) ?> · user #<?= e((string) ($entry['actor'] ?? '—')) ?>
                            <?php if (isset($entry['target'])): ?> · account #<?= e((string) $entry['target']) ?><?php endif; ?></td>
                    <?php else: ?>
                        <td><?= e((string) ($entry['severity'] ?? '')) ?> · <code><?= e((string) ($entry['type'] ?? '')) ?></code></td>
                        <td><code><?= e((string) ($entry['source'] ?? '')) ?>:<?= e((string) ($entry['line'] ?? '')) ?></code>
                            <?php if (isset($entry['code'])): ?> · code <?= e((string) $entry['code']) ?><?php endif; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php renderFooter();
