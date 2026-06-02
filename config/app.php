<?php

declare(strict_types=1);

if (!function_exists('config_load_env')) {
    function config_load_env(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}

if (!function_exists('config_env')) {
    function config_env(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('config_bool')) {
    function config_bool(string $key, bool $default = false): bool
    {
        $value = config_env($key);
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
}

config_load_env(dirname(__DIR__) . '/.env');

$appEnv = config_env('APP_ENV', 'local');
$workerToken = config_env('WORKER_TOKEN', 'change-this-worker-token-before-production');

if ($appEnv === 'production' && $workerToken === 'change-this-worker-token-before-production') {
    throw new RuntimeException('Set a strong WORKER_TOKEN before running in production.');
}

return [
    'db' => [
        'dsn' => config_env('DB_DSN', 'mysql:host=127.0.0.1;dbname=whatsapp_api;charset=utf8mb4'),
        'user' => config_env('DB_USER', 'root'),
        'password' => config_env('DB_PASSWORD', ''),
    ],
    'app' => [
        'env' => $appEnv,
        'name' => config_env('APP_NAME', 'WhatsApp API Hub'),
        'base_path' => config_env('APP_BASE_PATH', '/whatsapp-api/public'),
        'session_name' => config_env('APP_SESSION_NAME', 'wa_api_hub'),
        'secure_cookies' => config_bool('APP_SECURE_COOKIES', $appEnv === 'production'),
        'enable_test_page' => config_bool('ENABLE_TEST_PAGE', $appEnv !== 'production'),
    ],
    'security' => [
        'worker_token' => $workerToken,
        'login_max_attempts' => (int) config_env('LOGIN_MAX_ATTEMPTS', '5'),
        'login_lockout_minutes' => (int) config_env('LOGIN_LOCKOUT_MINUTES', '15'),
    ],
    'worker' => [
        'url' => config_env('WORKER_URL', 'http://127.0.0.1:3107'),
    ],
];
