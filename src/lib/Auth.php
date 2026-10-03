<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('index.php?page=login');
    }

    return $user;
}

function attempt_login(string $email, string $password): bool
{
    $email = mb_strtolower(trim($email));
    if (login_lockout_seconds($email) > 0) {
        return false;
    }

    $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_failed_login($email);
        return false;
    }

    clear_failed_logins($email);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}

function login_lockout_seconds(string $email): int
{
    $maxAttempts = max(1, (int) app_config('security.login_max_attempts'));
    $lockoutMinutes = max(1, (int) app_config('security.login_lockout_minutes'));

    try {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = ? AND ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([mb_strtolower(trim($email)), client_ip(), $lockoutMinutes]);
        $attempts = (int) $stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }

    return $attempts >= $maxAttempts ? $lockoutMinutes * 60 : 0;
}

function record_failed_login(string $email): void
{
    try {
        $stmt = Database::pdo()->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
        $stmt->execute([mb_strtolower(trim($email)), client_ip()]);
    } catch (Throwable) {
        // Login throttling is defense-in-depth; do not break authentication if migration is pending.
    }
}

function clear_failed_logins(string $email): void
{
    try {
        $stmt = Database::pdo()->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
        $stmt->execute([mb_strtolower(trim($email)), client_ip()]);
    } catch (Throwable) {
        // See record_failed_login().
    }
}

function register_user(string $name, string $email, string $phone, string $password): array
{
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            trim($name),
            mb_strtolower(trim($email)),
            trim($phone),
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        $userId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO whatsapp_sessions (user_id) VALUES (?)')->execute([$userId]);
        $pdo->prepare('INSERT INTO webhooks (user_id) VALUES (?)')->execute([$userId]);
        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;

        return [];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
