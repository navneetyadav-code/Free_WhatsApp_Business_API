<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/app.php';

session_name($config['app']['session_name']);
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (bool) $config['app']['secure_cookies'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Support.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/ApiCredentials.php';
require_once __DIR__ . '/lib/MessageQueue.php';
require_once __DIR__ . '/lib/WorkerClient.php';

Database::configure($config['db']);
