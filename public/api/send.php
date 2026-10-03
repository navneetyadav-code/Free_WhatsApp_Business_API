<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Use POST.'], 405);
}

$user = authenticate_api_request();
if (!$user) {
    json_response(['ok' => false, 'error' => 'Invalid API key or secret.'], 401);
}

$rate = api_key_rate_status($user);
if (!$rate['allowed']) {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 429, 'Rate limit exceeded.');
    json_response([
        'ok' => false,
        'error' => 'Rate limit exceeded.',
        'request_id' => $requestId,
        'rate' => $rate,
    ], 429);
}

$data = request_json();
$to = trim((string) ($data['to'] ?? $data['recipient'] ?? ''));
$message = trim((string) ($data['message'] ?? ''));

if ($to === '' || $message === '') {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 422, 'Missing fields.');
    json_response(['ok' => false, 'error' => 'Fields "to" and "message" are required.', 'request_id' => $requestId], 422);
}

$payload = [
    'source' => 'api',
    'type' => 'text',
];
if (!empty($data['humanize']) || !empty($user['allow_humanize'])) {
    $payload['humanize'] = true;
}
$messageId = enqueue_message((int) $user['user_id'], (int) $user['id'], $to, $message, $payload);
$requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 202);

json_response([
    'ok' => true,
    'status' => 'queued',
    'message_id' => $messageId,
    'request_id' => $requestId,
], 202);
