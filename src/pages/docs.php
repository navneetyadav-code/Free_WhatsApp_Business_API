<?php
$endpoint = 'http://localhost' . base_url('api/send.php');
$showTestPage = current_user() && app_config('app.enable_test_page') && is_local_request();
?>

<section class="dashboard">
    <div class="page-title">
        <div>
            <p class="eyebrow">Developer reference</p>
            <h1>API Documentation</h1>
            <p class="muted">Send requests are queued first. The worker sends messages in the background.</p>
        </div>
        <?php if ($showTestPage): ?>
            <a class="button small" href="<?= e(base_url('test-page.php')) ?>">Open Test Page</a>
        <?php endif; ?>
    </div>

    <section class="docs-hero">
        <div>
            <span class="panel-kicker">Send API</span>
            <h2>Queue messages with signed API credentials.</h2>
            <p class="muted">Use your dashboard-generated key and secret on every request. Messages are normalized, audited, queued, and sent by the worker.</p>
        </div>
        <div class="endpoint-card">
            <span>Endpoint</span>
            <code>POST <?= e($endpoint) ?></code>
        </div>
    </section>

    <section class="docs-grid">
        <div class="code-sample">
            <span>Headers</span>
            <code>X-API-Key: wa_live_...<br>X-API-Secret: was_...<br>Content-Type: application/json</code>
        </div>
        <div class="code-sample">
            <span>Body</span>
            <code>{ "to": "9507286092", "message": "Hello" }</code>
        </div>
    </section>

    <section class="card code-card">
        <div class="section-head compact">
            <h2>cURL Example</h2>
            <span class="pill queued">Shell</span>
        </div>
        <pre class="doc-code"><code>curl -X POST "<?= e($endpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "X-API-Secret: YOUR_API_SECRET" \
  -d "{\"to\":\"9507286092\",\"message\":\"Hello from API\"}"</code></pre>
    </section>

    <section class="card code-card">
        <div class="section-head compact">
            <h2>PHP Example</h2>
            <span class="pill queued">Server</span>
        </div>
        <pre class="doc-code"><code>$ch = curl_init("<?= e($endpoint) ?>");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-API-Key: YOUR_API_KEY",
        "X-API-Secret: YOUR_API_SECRET",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "to" => "9507286092",
        "message" => "Hello from API",
    ]),
]);
$response = curl_exec($ch);</code></pre>
    </section>

    <section class="card code-card">
        <div class="section-head compact">
            <h2>Response</h2>
            <span class="pill sent">Queued</span>
        </div>
        <pre class="doc-code"><code>{
  "ok": true,
  "status": "queued",
  "message_id": 123,
  "request_id": "28dc986f3a7b9222c4ad26a4"
}</code></pre>
        <p class="muted">Use the dashboard queue table to track queued, processing, sent, failed, and cancelled states.</p>
    </section>
</section>
