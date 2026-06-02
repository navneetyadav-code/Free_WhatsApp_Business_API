<?php

declare(strict_types=1);

function worker_request(string $method, string $path, array $payload = []): array
{
    $url = rtrim((string) app_config('worker.url'), '/') . '/' . ltrim($path, '/');
    $body = $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES) : '';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Worker-Token: ' . app_config('security.worker_token'),
        ],
    ]);

    if ($body !== '') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'status' => 0, 'error' => $error ?: 'Worker is not reachable.'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'status' => $status, 'error' => 'Worker returned an invalid response.'];
    }

    $data['status_code'] = $status;
    return $data;
}

function sync_session_status(int $userId, array $workerStatus): void
{
    $state = $workerStatus['state'] ?? 'idle';
    $allowed = ['idle', 'connecting', 'qr', 'connected', 'disconnected', 'error'];
    if (!in_array($state, $allowed, true)) {
        $state = 'error';
    }

    $stmt = Database::pdo()->prepare(
        'UPDATE whatsapp_sessions
         SET status = ?, phone = ?, push_name = ?, qr_updated_at = ?, connected_at = ?, disconnected_at = ?, last_error = ?
         WHERE user_id = ?'
    );
    $stmt->execute([
        $state,
        $workerStatus['phone'] ?? null,
        $workerStatus['pushName'] ?? null,
        !empty($workerStatus['hasQr']) ? date('Y-m-d H:i:s') : null,
        $state === 'connected' ? date('Y-m-d H:i:s') : null,
        in_array($state, ['disconnected', 'error'], true) ? date('Y-m-d H:i:s') : null,
        $workerStatus['error'] ?? null,
        $userId,
    ]);
}
