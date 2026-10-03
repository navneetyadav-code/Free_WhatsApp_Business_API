<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    $apiKey = $_POST['api_key'] ?? '';

    // URL to your ngrok tunnel pointing to XAMPP
    $apiUrl = 'https://slightly-ascend-unreal.ngrok-free.dev/whatsapp-api/public/api/send.php';

    $payload = json_encode([
        'to' => $to,
        'message' => $message
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'ngrok-skip-browser-warning: true'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = ['code' => $httpCode, 'response' => json_decode($response, true) ?: $response];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Tester</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #334155; padding: 2rem; max-width: 600px; margin: 0 auto; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
        h2 { margin-top: 0; color: #0f172a; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 0.5rem; color: #475569; }
        input[type="text"], textarea { w-full; width: 100%; box-sizing: border-box; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 14px; }
        button { background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; width: 100%; transition: background 0.2s; }
        button:hover { background: #047857; }
        .result { margin-top: 1.5rem; padding: 1rem; background: #f1f5f9; border-radius: 6px; border: 1px solid #e2e8f0; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; }
        .success { border-color: #10b981; background: #ecfdf5; color: #065f46; }
        .error { border-color: #ef4444; background: #fef2f2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="card">
        <h2>WhatsApp API Tester</h2>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 1.5rem;">Test your free WhatsApp API hosted on Ngrok.</p>
        
        <form method="POST">
            <div class="form-group">
                <label>API Key (Bearer Token)</label>
                <input type="text" name="api_key" placeholder="wa_live_..." required value="<?= htmlspecialchars($_POST['api_key'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Recipient Phone Number (with Country Code)</label>
                <input type="text" name="phone" placeholder="919876543210" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" rows="4" required placeholder="Type your test message..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit">Send Message</button>
        </form>

        <?php if (isset($result)): ?>
            <?php $isSuccess = $result['code'] >= 200 && $result['code'] < 300; ?>
            <div class="result <?= $isSuccess ? 'success' : 'error' ?>">
<strong>HTTP <?= $result['code'] ?></strong>
<?= htmlspecialchars(print_r($result['response'], true)) ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

