<?php

if (current_user()) {
    redirect_to('index.php?page=dashboard');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $lockedFor = login_lockout_seconds($email);
    if ($lockedFor > 0) {
        $error = 'Too many login attempts. Try again in about ' . (int) ceil($lockedFor / 60) . ' minutes.';
    } elseif (attempt_login($email, $password)) {
        redirect_to('index.php?page=dashboard');
    } else {
        $error = 'Email or password is incorrect.';
    }
}
?>

<section class="auth-card">
    <aside class="auth-visual">
        <a class="auth-brand" href="<?= e(base_url('index.php?page=login')) ?>">
            <span class="auth-logo">WA</span>
            <span>WhatsApp <b>API Hub</b></span>
        </a>
        <div class="auth-preview">
            <span class="preview-status">Live queue</span>
            <strong>12.4k</strong>
            <small>messages routed this month</small>
            <div class="preview-bars"><span></span><span></span><span></span></div>
        </div>
        <div class="auth-proof">
            <span>API keys</span>
            <span>Worker queue</span>
            <span>Webhook logs</span>
        </div>
    </aside>

    <div class="auth-panel">
        <div class="auth-heading">
            <span class="eyebrow">Secure access</span>
            <h1>Welcome back</h1>
            <p>Sign in to manage devices, keys, queues, and callbacks.</p>
        </div>

        <form class="auth-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>

            <label>Email address
                <span class="input-wrap"><span class="field-icon">@</span><input name="email" type="email" autocomplete="email" placeholder="you@example.com" required></span>
            </label>
            <label>Password
                <span class="input-wrap"><span class="field-icon">#</span><input name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required></span>
            </label>

            <div class="auth-row">
                <label class="check"><input type="checkbox" checked> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit">Log in</button>
            <p class="muted center">No account yet? <a href="<?= e(base_url('index.php?page=register')) ?>">Create one</a></p>
        </form>

        <p class="secure-note"><span>OK</span>Your linked-device session stays private.</p>
    </div>
</section>
