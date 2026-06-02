<?php

declare(strict_types=1);

function create_api_key(int $userId, string $name = 'API key'): array
{
    $apiKey = 'wa_live_' . bin2hex(random_bytes(24));
    $apiSecret = 'was_' . bin2hex(random_bytes(32));

    $stmt = Database::pdo()->prepare(
        'INSERT INTO api_keys (user_id, name, key_hash, key_prefix, secret_hash) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        trim($name) ?: 'API key',
        hash('sha256', $apiKey),
        substr($apiKey, 0, 22),
        password_hash($apiSecret, PASSWORD_DEFAULT),
    ]);

    return [
        'id' => (int) Database::pdo()->lastInsertId(),
        'api_key' => $apiKey,
        'api_secret' => $apiSecret,
    ];
}

function regenerate_api_credentials(int $userId): array
{
    return create_api_key($userId, 'Generated key');
}

function list_api_keys(int $userId): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT id, name, key_prefix, status, ip_allowlist, rate_per_minute, rate_per_hour, rate_per_day, last_used_at, created_at
         FROM api_keys WHERE user_id = ? ORDER BY id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function update_api_key_status(int $userId, int $keyId, string $status): void
{
    if (!in_array($status, ['active', 'disabled'], true)) {
        return;
    }

    $stmt = Database::pdo()->prepare('UPDATE api_keys SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $keyId, $userId]);
}

function delete_api_key(int $userId, int $keyId): void
{
    $stmt = Database::pdo()->prepare('DELETE FROM api_keys WHERE id = ? AND user_id = ?');
    $stmt->execute([$keyId, $userId]);
}

function update_api_key_limits(int $userId, int $keyId, array $data): void
{
    $minute = max(1, min(300, (int) ($data['rate_per_minute'] ?? 20)));
    $hour = max($minute, min(5000, (int) ($data['rate_per_hour'] ?? 300)));
    $day = max($hour, min(50000, (int) ($data['rate_per_day'] ?? 1000)));
    $allowlist = trim((string) ($data['ip_allowlist'] ?? ''));

    $stmt = Database::pdo()->prepare(
        'UPDATE api_keys SET rate_per_minute = ?, rate_per_hour = ?, rate_per_day = ?, ip_allowlist = ? WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$minute, $hour, $day, $allowlist ?: null, $keyId, $userId]);
}

function authenticate_api_request(): ?array
{
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $secret = $_SERVER['HTTP_X_API_SECRET'] ?? '';
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!$key && str_starts_with($authorization, 'Bearer ')) {
        $token = substr($authorization, 7);
        [$key, $secret] = array_pad(explode(':', $token, 2), 2, '');
    }

    if (!is_string($key) || !is_string($secret) || $key === '' || $secret === '') {
        return null;
    }

    $stmt = Database::pdo()->prepare(
        'SELECT api_keys.*, users.email, users.name AS user_name
         FROM api_keys
         JOIN users ON users.id = api_keys.user_id
         WHERE api_keys.key_hash = ? LIMIT 1'
    );
    $stmt->execute([hash('sha256', $key)]);
    $apiKey = $stmt->fetch();

    if (!$apiKey || $apiKey['status'] !== 'active' || !password_verify($secret, $apiKey['secret_hash'] ?? '')) {
        return null;
    }

    if (!api_key_ip_allowed($apiKey, client_ip())) {
        return null;
    }

    Database::pdo()->prepare('UPDATE api_keys SET last_used_at = NOW() WHERE id = ?')->execute([$apiKey['id']]);

    return $apiKey;
}

function api_key_ip_allowed(array $apiKey, string $ip): bool
{
    $allowlist = trim((string) ($apiKey['ip_allowlist'] ?? ''));
    if ($allowlist === '') {
        return true;
    }

    $allowed = array_filter(array_map('trim', preg_split('/[\s,]+/', $allowlist) ?: []));
    return in_array($ip, $allowed, true);
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}
