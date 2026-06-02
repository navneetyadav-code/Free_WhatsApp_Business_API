<?php

declare(strict_types=1);

function enqueue_message(int $userId, ?int $apiKeyId, string $to, string $message, array $payload = []): int
{
    $stmt = Database::pdo()->prepare(
        'INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $apiKeyId,
        trim($to),
        $payload['type'] ?? 'text',
        $message,
        json_encode($payload, JSON_UNESCAPED_SLASHES),
    ]);

    return (int) Database::pdo()->lastInsertId();
}

function api_key_rate_status(array $apiKey): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        "SELECT
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)) AS per_minute,
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) AS per_hour,
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS per_day
         FROM message_queue
         WHERE api_key_id = ? AND status <> 'cancelled'"
    );
    $stmt->execute([(int) $apiKey['id']]);
    $row = $stmt->fetch() ?: [];

    $counts = [
        'per_minute' => (int) ($row['per_minute'] ?? 0),
        'per_hour' => (int) ($row['per_hour'] ?? 0),
        'per_day' => (int) ($row['per_day'] ?? 0),
    ];

    $limits = [
        'per_minute' => (int) $apiKey['rate_per_minute'],
        'per_hour' => (int) $apiKey['rate_per_hour'],
        'per_day' => (int) $apiKey['rate_per_day'],
    ];

    return [
        'allowed' => $counts['per_minute'] < $limits['per_minute']
            && $counts['per_hour'] < $limits['per_hour']
            && $counts['per_day'] < $limits['per_day'],
        'counts' => $counts,
        'limits' => $limits,
    ];
}

function log_api_request(int $userId, ?int $apiKeyId, string $endpoint, int $statusCode, ?string $error = null): string
{
    $requestId = bin2hex(random_bytes(12));
    $stmt = Database::pdo()->prepare(
        'INSERT INTO api_request_logs (user_id, api_key_id, endpoint, ip_address, status_code, request_id, error_message)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $apiKeyId, $endpoint, client_ip(), $statusCode, $requestId, $error]);
    return $requestId;
}

function message_stats(int $userId): array
{
    $stmt = Database::pdo()->prepare(
        "SELECT status, COUNT(*) AS total FROM message_queue WHERE user_id = ? GROUP BY status"
    );
    $stmt->execute([$userId]);
    $stats = ['queued' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0, 'cancelled' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $stats[$row['status']] = (int) $row['total'];
    }

    return $stats;
}

function recent_messages(int $userId, int $limit = 15): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT message_queue.*, api_keys.key_prefix
         FROM message_queue
         LEFT JOIN api_keys ON api_keys.id = message_queue.api_key_id
         WHERE message_queue.user_id = ?
         ORDER BY message_queue.id DESC
         LIMIT ' . max(1, min(100, $limit))
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function retry_message(int $userId, int $messageId): void
{
    $stmt = Database::pdo()->prepare(
        "UPDATE message_queue
         SET status = 'queued', attempts = 0, error_message = NULL, locked_at = NULL, locked_by = NULL, available_at = NOW()
         WHERE id = ? AND user_id = ? AND status IN ('failed','cancelled')"
    );
    $stmt->execute([$messageId, $userId]);
}

function cancel_message(int $userId, int $messageId): void
{
    $stmt = Database::pdo()->prepare(
        "UPDATE message_queue SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'queued'"
    );
    $stmt->execute([$messageId, $userId]);
}

function create_campaign(int $userId, string $name, string $template): int
{
    $pdo = Database::pdo();
    $contacts = opted_in_contacts($userId);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO campaigns (user_id, name, message_template, target_count) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, trim($name) ?: 'Untitled campaign', $template, count($contacts)]);
        $campaignId = (int) $pdo->lastInsertId();

        $queued = 0;
        foreach ($contacts as $contact) {
            $body = apply_contact_template($template, $contact);
            $queue = $pdo->prepare(
                'INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload)
                 VALUES (?, NULL, ?, "text", ?, ?)'
            );
            $queue->execute([
                $userId,
                $contact['phone'],
                $body,
                json_encode([
                    'source' => 'campaign',
                    'campaign_id' => $campaignId,
                    'contact_id' => (int) $contact['id'],
                    'type' => 'text',
                ], JSON_UNESCAPED_SLASHES),
            ]);
            $queued++;
        }

        $pdo->prepare('UPDATE campaigns SET queued_count = ?, status = ? WHERE id = ?')
            ->execute([$queued, $queued > 0 ? 'queued' : 'completed', $campaignId]);
        $pdo->commit();

        return $campaignId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function opted_in_contacts(int $userId): array
{
    $stmt = Database::pdo()->prepare('SELECT * FROM contacts WHERE user_id = ? AND opt_in = 1 ORDER BY id ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function apply_contact_template(string $template, array $contact): string
{
    return strtr($template, [
        '{name}' => (string) ($contact['name'] ?? ''),
        '{phone}' => (string) ($contact['phone'] ?? ''),
    ]);
}

function recent_campaigns(int $userId, int $limit = 8): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, min(50, $limit))
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
