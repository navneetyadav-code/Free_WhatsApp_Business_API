<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

require_auth();
if (!app_config('app.enable_test_page') || !is_local_request()) {
    http_response_code(403);
    exit('The API test page is available only for authenticated local development.');
}

$response = null;
$httpCode = null;
$error = null;

$apiKey = (string) ($_POST['api_key'] ?? '');
$apiSecret = (string) ($_POST['api_secret'] ?? '');
$to = (string) ($_POST['to'] ?? '');
$message = (string) ($_POST['message'] ?? 'Hello from WhatsApp API Hub test page.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endpoint = 'http://localhost' . base_url('api/send.php');
    $payload = json_encode([
        'to' => trim($to),
        'message' => trim($message),
    ], JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . trim($apiKey),
            'X-API-Secret: ' . trim($apiSecret),
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $raw = curl_exec($ch);
    $error = curl_error($ch) ?: null;
    $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $decoded = json_decode((string) $raw, true);
    $response = is_array($decoded) ? $decoded : ['raw' => $raw];
}

$recent = [];
try {
    $recent = Database::pdo()
        ->query('SELECT id, recipient, normalized_recipient, status, attempts, error_message, created_at, sent_at FROM message_queue ORDER BY id DESC LIMIT 8')
        ->fetchAll();
} catch (Throwable $exception) {
    $recent = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp API Test Page</title>
    <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>">
    <style>
        .test-shell { max-width: 980px; margin: 0 auto; padding: 32px 20px; }
        .test-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mono-box { background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 16px; overflow: auto; }
        .mono-box code { color: inherit; white-space: pre-wrap; }
        @media (max-width: 840px) { .test-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="test-shell">
        <div class="page-title">
            <div>
                <p class="eyebrow">Live endpoint test</p>
                <h1>WhatsApp API Test Page</h1>
                <p class="muted">This page sends through the real <code>/api/send.php</code> endpoint.</p>
            </div>
            <a class="button small" href="<?= e(base_url('index.php?page=dashboard')) ?>">Dashboard</a>
        </div>

        <div class="test-grid">
            <section class="card">
                <h2>Send Message</h2>
                <form class="form compact-form" method="post">
                    <label>API Key
                        <input name="api_key" value="<?= e($apiKey) ?>" placeholder="wa_live_..." required>
                    </label>
                    <label>API Secret
                        <input name="api_secret" value="<?= e($apiSecret) ?>" placeholder="was_..." required>
                    </label>
                    <label>Recipient Number
                        <input name="to" value="<?= e($to) ?>" placeholder="9507286092 or 919507286092" required>
                    </label>
                    <label>Message
                        <input name="message" value="<?= e($message) ?>" required>
                    </label>
                    <button type="submit">Send Through API</button>
                </form>
            </section>

            <section class="card">
                <h2>API Response</h2>
                <?php if ($httpCode !== null): ?>
                    <p><strong>HTTP:</strong> <?= (int) $httpCode ?></p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert danger"><?= e($error) ?></div>
                <?php endif; ?>
                <div class="mono-box"><code><?= e(json_encode($response ?? ['info' => 'Submit the form to test the API.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></code></div>
            </section>
        </div>

        <section class="card" style="margin-top: 20px;">
            <div class="section-head">
                <div>
                    <h2>Recent Queue Items</h2>
                    <p class="muted">Refresh after a few seconds to see the worker update queued messages.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>ID</th><th>To</th><th>Status</th><th>Attempts</th><th>Error</th><th>Created</th><th>Sent</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $item): ?>
                            <tr>
                                <td>#<?= (int) $item['id'] ?></td>
                                <td><?= e($item['normalized_recipient'] ?: $item['recipient']) ?></td>
                                <td><span class="pill <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                                <td><?= (int) $item['attempts'] ?></td>
                                <td><?= e($item['error_message'] ?? '') ?></td>
                                <td><?= e($item['created_at']) ?></td>
                                <td><?= e($item['sent_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recent): ?>
                            <tr><td colspan="7" class="muted center">No queue items yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
