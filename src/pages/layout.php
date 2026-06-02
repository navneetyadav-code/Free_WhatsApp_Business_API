<?php
$userForLayout = current_user();
$isAuthed = (bool) $userForLayout;
$currentPage = $_GET['page'] ?? 'dashboard';
$isPublicDocs = !$isAuthed && $currentPage === 'docs';
$pageLabels = [
    'dashboard' => 'Overview',
    'device' => 'Device',
    'api-keys' => 'API Keys',
    'queue' => 'Message Queue',
    'contacts' => 'Contacts',
    'webhooks' => 'Webhooks',
    'campaigns' => 'Campaigns',
    'docs' => 'Docs',
];
$navItems = [
    ['page' => 'dashboard', 'label' => 'Overview', 'icon' => 'overview'],
    ['page' => 'device', 'label' => 'Device', 'icon' => 'device'],
    ['page' => 'api-keys', 'label' => 'API Keys', 'icon' => 'keys'],
    ['page' => 'queue', 'label' => 'Queue', 'icon' => 'queue'],
    ['page' => 'contacts', 'label' => 'Contacts', 'icon' => 'contacts'],
    ['page' => 'webhooks', 'label' => 'Webhooks', 'icon' => 'webhooks'],
    ['page' => 'campaigns', 'label' => 'Campaigns', 'icon' => 'campaigns'],
    ['page' => 'docs', 'label' => 'Docs', 'icon' => 'docs'],
];
$initials = 'AD';
if ($userForLayout) {
    $parts = preg_split('/\s+/', trim((string) $userForLayout['name'])) ?: [];
    $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(app_config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>">
</head>
<body class="<?= $isAuthed ? 'app-body' : ($isPublicDocs ? 'docs-body' : 'auth-body') ?>">
<?php if ($isAuthed): ?>
    <div class="app-frame">
        <aside class="sidebar">
            <a class="brand full-brand" href="<?= e(base_url('index.php?page=dashboard')) ?>">
                <span class="brand-mark">WA</span>
                <span><strong>WhatsApp</strong><b>API Hub</b></span>
            </a>
            <nav class="side-nav">
                <?php foreach ($navItems as $item): ?>
                    <a class="<?= $currentPage === $item['page'] ? 'active' : '' ?>" href="<?= e(base_url('index.php?page=' . $item['page'])) ?>">
                        <span class="nav-icon <?= e($item['icon']) ?>" aria-hidden="true"></span>
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-foot">
                <span class="foot-dot"></span>
                <span>Secure admin console</span>
            </div>
        </aside>

        <section class="workspace">
            <header class="app-header">
                <div class="header-title">
                    <span>Admin Console</span>
                    <strong><?= e($pageLabels[$currentPage] ?? ucwords(str_replace('-', ' ', $currentPage))) ?></strong>
                </div>
                <div class="header-right">
                    <a class="header-link" href="<?= e(base_url('index.php?page=docs')) ?>">Docs</a>
                    <span class="system-pill"><i></i>Operational</span>
                    <div class="user-chip">
                        <span class="avatar"><?= e($initials) ?></span>
                        <span><strong><?= e($userForLayout['name']) ?></strong><small>Administrator</small></span>
                    </div>
                    <form method="post" action="<?= e(base_url('index.php?page=logout')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button class="ghost small" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <main class="app-content">
                <?php foreach (flashes() as $flash): ?>
                    <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endforeach; ?>
                <?= $content ?>
            </main>

            <footer class="app-footer">
                <span>&copy; 2025 WhatsApp API Hub. All rights reserved.</span>
                <span><a href="<?= e(base_url('index.php?page=docs')) ?>">Documentation</a> <a href="#">Support</a></span>
            </footer>
        </section>
    </div>
<?php elseif ($isPublicDocs): ?>
    <main class="public-doc-shell">
        <div class="public-doc-topbar">
            <a class="auth-brand" href="<?= e(base_url('index.php?page=login')) ?>">
                <span class="auth-logo">WA</span>
                <span>WhatsApp <b>API Hub</b></span>
            </a>
            <a class="ghost small" href="<?= e(base_url('index.php?page=login')) ?>">Log in</a>
        </div>
        <?php foreach (flashes() as $flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
<?php else: ?>
    <main class="auth-shell">
        <?php foreach (flashes() as $flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
<?php endif; ?>
</body>
</html>
