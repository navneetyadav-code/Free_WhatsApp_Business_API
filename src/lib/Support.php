<?php

declare(strict_types=1);

function app_config(?string $key = null): mixed
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    return rtrim((string) app_config('app.base_path'), '/') . '/' . ltrim($path, '/');
}

function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function normalize_phone(string $phone): string {
    $phone = trim($phone);
    // If the user submits just 10 digits (without JS), assume default country
    $defaultCountryPrefix = config_env('DEFAULT_COUNTRY_CODE', 'in') === 'in' ? '+91' : '';
    if ($defaultCountryPrefix && preg_match('/^[0-9]{10}$/', $phone)) {
        return $defaultCountryPrefix . $phone;
    }
    return $phone;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $body = file_get_contents('php://input') ?: '';
    $data = json_decode($body, true);
    return is_array($data) ? $data : $_POST;
}

function is_local_request(): bool
{
    $ip = client_ip();
    return in_array($ip, ['127.0.0.1', '::1'], true) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.');
}

function is_private_ip(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true;
    }

    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

function validate_webhook_url(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Enter a valid webhook URL.');
    }

    $parts = parse_url($url);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));

    if ($scheme !== 'https') {
        throw new InvalidArgumentException('Webhook URL must use HTTPS.');
    }

    if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.local')) {
        throw new InvalidArgumentException('Webhook URL cannot point to localhost or private hosts.');
    }

    $addresses = [];
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $addresses[] = $host;
    } else {
        foreach (dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($address) {
                $addresses[] = $address;
            }
        }
    }

    if (!$addresses) {
        throw new InvalidArgumentException('Webhook URL host could not be resolved safely.');
    }

    foreach ($addresses as $address) {
        if (is_private_ip($address)) {
            throw new InvalidArgumentException('Webhook URL cannot resolve to a private or reserved IP address.');
        }
    }

    return $url;
}

function seo_meta(?string $page): array {
    $baseUrl = rtrim(app_config('app.public_url'), '/');
    $defaults = [
        'title' => 'WhatsApp API Hub',
        'description' => 'A robust WhatsApp API Hub for managing messages, campaigns, and API integrations.',
        'keywords' => 'whatsapp, api, hub, messaging, campaigns',
        'canonical' => $baseUrl . '/index.php?page=' . $page,
        'type' => 'website',
        'robots' => 'index, follow'
    ];

    switch ($page) {
        case 'dashboard':
            $defaults['title'] = 'Dashboard | WhatsApp API Hub';
            break;
        case 'login':
            $defaults['title'] = 'Login | WhatsApp API Hub';
            break;
        case 'register':
            $defaults['title'] = 'Register | WhatsApp API Hub';
            break;
        case 'docs':
            $defaults['title'] = 'Documentation | WhatsApp API Hub';
            break;
    }

    return $defaults;
}

function send_smtp_email(string $to, string $subject, string $body): void {
    $host = app_config('smtp.host');
    $port = app_config('smtp.port') ?: 587;
    $user = app_config('smtp.user');
    $pass = app_config('smtp.pass');
    $from = app_config('smtp.from');
    $fromName = app_config('smtp.from_name');

    if (!$host) return;

    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) return;
    
    stream_set_timeout($socket, 10);
    fread($socket, 1024); // read greeting

    fwrite($socket, "EHLO localhost\r\n");
    fread($socket, 1024);

    if ($port == 587) {
        fwrite($socket, "STARTTLS\r\n");
        fread($socket, 1024);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fwrite($socket, "EHLO localhost\r\n");
        fread($socket, 1024);
    }

    if ($user && $pass) {
        fwrite($socket, "AUTH LOGIN\r\n");
        fread($socket, 1024);
        fwrite($socket, base64_encode($user) . "\r\n");
        fread($socket, 1024);
        fwrite($socket, base64_encode($pass) . "\r\n");
        fread($socket, 1024);
    }

    fwrite($socket, "MAIL FROM: <$from>\r\n");
    fread($socket, 1024);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    fread($socket, 1024);
    fwrite($socket, "DATA\r\n");
    fread($socket, 1024);

    $headers = "From: $fromName <$from>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";

    fwrite($socket, $headers . $body . "\r\n.\r\n");
    fread($socket, 1024);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
}
