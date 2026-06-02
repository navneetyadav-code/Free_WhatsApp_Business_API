<?php

if (current_user()) {
    redirect_to('index.php?page=dashboard');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a valid name, email, and password with at least 8 characters.';
    } else {
        try {
            register_user($name, $email, $password);
            flash('success', 'Account created. Copy your API credentials before leaving this page.');
            redirect_to('index.php?page=dashboard');
        } catch (Throwable $exception) {
            $error = 'That email is already registered.';
        }
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
            <span class="preview-status">Setup flow</span>
            <strong>3 min</strong>
            <small>from account to first queued message</small>
            <div class="preview-steps"><span></span><span></span><span></span></div>
        </div>
        <div class="auth-proof">
            <span>Create keys</span>
            <span>Scan device</span>
            <span>Send API</span>
        </div>
    </aside>

    <div class="auth-panel">
        <div class="auth-heading">
            <span class="eyebrow">New workspace</span>
            <h1>Create account</h1>
            <p>Start with an admin login, generated credentials, and a private linked-device session.</p>
        </div>

        <form class="auth-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>

            <label>Full name
                <span class="input-wrap"><span class="field-icon">ID</span><input name="name" type="text" autocomplete="name" placeholder="Admin User" required></span>
            </label>
            <label>Email address
                <span class="input-wrap"><span class="field-icon">@</span><input name="email" type="email" autocomplete="email" placeholder="you@example.com" required></span>
            </label>
            <label>Password
                <span class="input-wrap"><span class="field-icon">#</span><input name="password" type="password" autocomplete="new-password" minlength="8" placeholder="Create a password" required></span>
            </label>

            <button type="submit">Create account</button>
            <p class="muted center">Already registered? <a href="<?= e(base_url('index.php?page=login')) ?>">Log in</a></p>
        </form>

        <p class="secure-note"><span>OK</span>API credentials are generated securely.</p>
    </div>
</section>
