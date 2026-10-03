<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_auth();
$action = $_GET['action'] ?? 'status';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$userId = (int) $user['id'];

if ($action === 'start') {
    $response = worker_request('POST', "sessions/{$userId}/start");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? []);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'logout') {
    $response = worker_request('POST', "sessions/{$userId}/logout");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? ['state' => 'idle']);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'sleep') {
    $response = worker_request('POST', "sessions/{$userId}/sleep");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? ['state' => 'idle']);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'wake') {
    $response = worker_request('POST', "sessions/{$userId}/wake");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? []);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'send-test') {
    $data = request_json();
    $to = trim((string) ($data['to'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));

    if ($to === '' || $message === '') {
        json_response(['ok' => false, 'error' => 'Recipient and message are required.'], 422);
    }

    $messageId = enqueue_message($userId, null, $to, $message, [
        'source' => 'dashboard',
        'type' => 'text',
    ]);

    json_response(['ok' => true, 'status' => 'queued', 'message_id' => $messageId], 202);
}

$response = worker_request('GET', "sessions/{$userId}/status");
if (!empty($response['ok'])) {
    sync_session_status($userId, $response['session'] ?? []);
}
json_response($response, !empty($response['ok']) ? 200 : 502);
