<?php
$endpoint = 'http://localhost' . base_url('api/send.php');
$showTestPage = current_user() && app_config('app.enable_test_page') && is_local_request();
?>

<section class="dashboard">
    <div class="page-title">
        <div>
            <p class="eyebrow">Free open-source WhatsApp API</p>
            <h1>Free WhatsApp Web Automation API for PHP, Node.js, and Baileys</h1>
            <p class="muted">Build a local WhatsApp API dashboard with linked-device QR login, API keys, queued message sending, rate limits, contacts, and webhook callbacks.</p>
        </div>
        <?php if ($showTestPage): ?>
            <a class="button small" href="<?= e(base_url('test-page.php')) ?>">Open Test Page</a>
        <?php endif; ?>
    </div>

    <section class="docs-hero">
        <div>
            <span class="panel-kicker">WhatsApp message API</span>
            <h2>Send WhatsApp messages from a free API endpoint.</h2>
            <p class="muted">WhatsApp API Hub gives developers a free WhatsApp Web automation API for testing, internal tools, demos, and local workflows. Requests are signed, audited, normalized, queued, and sent by the Node.js worker.</p>
        </div>
        <div class="endpoint-card">
            <span>Endpoint</span>
            <code>POST <?= e($endpoint) ?></code>
        </div>
    </section>

    <section class="docs-grid">
        <div class="code-sample">
            <span>Best for</span>
            <code>Free WhatsApp API testing<br>WhatsApp Web automation<br>Baileys API dashboards<br>PHP WhatsApp API projects</code>
        </div>
        <div class="code-sample">
            <span>Included</span>
            <code>API keys<br>Message queue<br>Rate limits<br>Contacts<br>Webhooks<br>Delivery logs</code>
        </div>
    </section>

    <section class="card code-card">
        <div class="section-head compact">
            <h2>Why Developers Use This WhatsApp Web Automation API</h2>
            <span class="pill queued">Open Source</span>
        </div>
        <p class="muted">Use this project when you need a free WhatsApp API starter kit with a PHP dashboard, MySQL storage, a Node.js Baileys worker, QR-based linked-device login, and background message processing.</p>
        <div class="docs-grid">
            <div class="code-sample"><span>Search terms covered</span><code>free WhatsApp API<br>WhatsApp Web API<br>WhatsApp automation API<br>open source WhatsApp API</code></div>
            <div class="code-sample"><span>Developer stack</span><code>PHP + MySQL dashboard<br>Node.js worker<br>Baileys linked device<br>Webhook callbacks</code></div>
        </div>
    </section>

    <section class="card code-card">
        <div class="section-head compact">
            <h2>Important WhatsApp API Note</h2>
            <span class="pill queued">Developer Safety</span>
        </div>
        <p class="muted">This project uses WhatsApp Web linked-device automation through Baileys. It is useful for free WhatsApp API testing, demos, and internal workflows. For large commercial production systems, compare it with Meta's official WhatsApp Business Cloud API.</p>
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
            <h2>Free WhatsApp API cURL Example</h2>
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
            <h2>PHP WhatsApp API Example</h2>
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
            <h2>Queued Message API Response</h2>
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
