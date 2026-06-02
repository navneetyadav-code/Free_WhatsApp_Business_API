<?php

$user = $user ?? require_auth();
$section = $_GET['page'] ?? 'dashboard';
$pdo = Database::pdo();

$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$user['id']]);
$session = $sessionStmt->fetch() ?: ['status' => 'idle'];

$apiKeys = list_api_keys((int) $user['id']);
$activeApiKeyCount = count(array_filter($apiKeys, static fn (array $key): bool => ($key['status'] ?? '') === 'active'));
$stats = message_stats((int) $user['id']);
$messages = recent_messages((int) $user['id']);
$apiRequestCountStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ?');
$apiRequestCountStmt->execute([(int) $user['id']]);
$apiRequestCount = (int) $apiRequestCountStmt->fetchColumn();
$connectedCount = (($session['status'] ?? 'idle') === 'connected') ? 1 : 0;
$sentCount = (int) ($stats['sent'] ?? 0);
$failedCount = (int) ($stats['failed'] ?? 0);
$queuedCount = (int) ($stats['queued'] ?? 0);
$processingCount = (int) ($stats['processing'] ?? 0);
$deliveryTotal = max(1, $sentCount + $failedCount + $queuedCount + $processingCount);
$sentRate = (int) round(($sentCount / $deliveryTotal) * 100);
$failureRate = (int) round(($failedCount / $deliveryTotal) * 100);
$queueDepth = $queuedCount + $processingCount;
$deviceConnected = (($session['status'] ?? 'idle') === 'connected');

$contactsStmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY id DESC LIMIT 25');
$contactsStmt->execute([$user['id']]);
$contacts = $contactsStmt->fetchAll();
$optedInCount = count(opted_in_contacts((int) $user['id']));
$campaigns = recent_campaigns((int) $user['id']);

$webhookStmt = $pdo->prepare('SELECT * FROM webhooks WHERE user_id = ? LIMIT 1');
$webhookStmt->execute([$user['id']]);
$webhook = $webhookStmt->fetch() ?: ['enabled' => 0, 'target_url' => '', 'secret' => ''];
$webhookEnabled = !empty($webhook['enabled']);
$readySignals = 1 + ($deviceConnected ? 1 : 0) + ($webhookEnabled ? 1 : 0) + ($queueDepth === 0 ? 1 : 0);
$readinessScore = (int) round(($readySignals / 4) * 100);
$lastActivity = $messages[0]['created_at'] ?? 'No activity yet';
$nextAction = $deviceConnected ? 'Send test message' : 'Pair WhatsApp device';
$nextActionUrl = $deviceConnected ? base_url('index.php?page=queue') : base_url('index.php?page=device');

$newCredentials = $_SESSION['new_api_credentials'] ?? null;
unset($_SESSION['new_api_credentials']);
$newWebhookSecret = $_SESSION['new_webhook_secret'] ?? null;
unset($_SESSION['new_webhook_secret']);

function page_header(string $title, string $subtitle): void
{
    ?>
    <div class="page-title overview-title">
        <div>
            <span class="eyebrow">Workspace</span>
            <h1><?= e($title) ?></h1>
            <p class="muted"><?= e($subtitle) ?></p>
        </div>
    </div>
    <?php
}
?>

<section class="dashboard" data-session-page="1">
<?php if ($section === 'dashboard'): ?>
    <div class="page-title dashboard-title">
        <div>
            <span class="eyebrow">Live command center</span>
            <h1>Operations Dashboard</h1>
            <p class="muted">Monitor device readiness, message throughput, key usage, and worker health from one focused surface.</p>
        </div>
        <div class="toolbar-actions">
            <a class="icon-square" href="<?= e(base_url('index.php?page=dashboard')) ?>" aria-label="Refresh dashboard"><span>RF</span></a>
            <a class="ghost" href="<?= e(base_url('index.php?page=docs')) ?>">API Docs</a>
            <a class="button" href="<?= e($nextActionUrl) ?>"><?= e($nextAction) ?></a>
        </div>
    </div>

    <section class="ops-hero">
        <div class="ops-copy">
            <span class="panel-kicker">Workspace readiness</span>
            <h2><?= $readinessScore >= 75 ? 'Your sending stack is ready.' : 'Finish setup to unlock reliable sending.' ?></h2>
            <p class="muted">Readiness combines WhatsApp pairing, webhook status, queue pressure, and API service health.</p>
            <div class="ops-badges">
                <span><?= $activeApiKeyCount ?> active keys</span>
                <span><?= e($deviceConnected ? 'Device online' : 'Device offline') ?></span>
                <span><?= $queueDepth ?> pending</span>
            </div>
        </div>
        <div class="readiness-card">
            <div class="meter-ring readiness-ring" style="--value: <?= $readinessScore ?>;">
                <strong><?= $readinessScore ?>%</strong>
                <span>ready</span>
            </div>
            <div class="readiness-list">
                <div><span class="dot <?= $deviceConnected ? 'success' : 'warn' ?>"></span><strong>Device</strong><small><?= e($deviceConnected ? 'Connected' : 'Pair required') ?></small></div>
                <div><span class="dot <?= $webhookEnabled ? 'success' : 'warn' ?>"></span><strong>Webhook</strong><small><?= $webhookEnabled ? 'Enabled' : 'Optional' ?></small></div>
                <div><span class="dot <?= $queueDepth === 0 ? 'success' : 'warn' ?>"></span><strong>Queue</strong><small><?= $queueDepth === 0 ? 'Clear' : $queueDepth . ' pending' ?></small></div>
            </div>
        </div>
    </section>

    <div class="signal-grid">
        <div class="signal-card"><span class="signal-icon device"></span><small>Device</small><strong><?= e($deviceConnected ? 'Connected' : 'Disconnected') ?></strong><em><?= e($session['phone'] ?? 'No phone linked') ?></em></div>
        <div class="signal-card"><span class="signal-icon keys"></span><small>Credentials</small><strong><?= $activeApiKeyCount ?> active</strong><em><?= count($apiKeys) ?> total keys</em></div>
        <div class="signal-card"><span class="signal-icon queue"></span><small>Worker queue</small><strong><?= $queueDepth ?></strong><em><?= $processingCount ?> processing</em></div>
        <div class="signal-card"><span class="signal-icon webhooks"></span><small>Webhook</small><strong><?= $webhookEnabled ? 'Active' : 'Inactive' ?></strong><em><?= !empty($webhook['target_url']) ? 'URL configured' : 'No endpoint' ?></em></div>
    </div>

    <div class="metric-grid">
        <div class="metric-card"><span>Sent</span><strong><?= $sentCount ?></strong><div class="metric-bar"><i style="width: <?= $sentRate ?>%"></i></div><em><?= $sentRate ?>% delivery mix</em></div>
        <div class="metric-card"><span>Failed</span><strong><?= $failedCount ?></strong><div class="metric-bar danger"><i style="width: <?= $failureRate ?>%"></i></div><em><?= $failureRate ?>% failure mix</em></div>
        <div class="metric-card"><span>API Requests</span><strong><?= $apiRequestCount ?></strong><div class="metric-bar blue"><i style="width: <?= min(100, $apiRequestCount * 8) ?>%"></i></div><em>Audited requests</em></div>
        <div class="metric-card"><span>Last Activity</span><strong><?= e($lastActivity === 'No activity yet' ? 'None' : substr((string) $lastActivity, 5, 11)) ?></strong><div class="metric-bar muted-bar"><i style="width: <?= $messages ? 100 : 6 ?>%"></i></div><em><?= e($messages ? 'Recent event found' : 'Waiting for traffic') ?></em></div>
    </div>

    <div class="dashboard-workbench">
        <section class="card activity-card">
            <div class="section-head">
                <h2>Recent Activity</h2>
                <a class="ghost small" href="<?= e(base_url('index.php?page=queue')) ?>">View all</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Time</th><th>Event</th><th>Details</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($messages, 0, 5) as $message): ?>
                            <tr>
                                <td><?= e(substr((string) $message['created_at'], 11, 8)) ?></td>
                                <td>Message <?= e(ucfirst((string) $message['status'])) ?></td>
                                <td>To: <?= e($message['normalized_recipient'] ?: $message['recipient']) ?></td>
                                <td><span class="pill <?= e($message['status']) ?>"><?= e($message['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$messages): ?>
                            <tr><td colspan="4" class="empty-state">No messages have been queued yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="card pipeline-card">
            <div class="section-head compact">
                <h2>Sending Pipeline</h2>
                <span class="pill <?= $deviceConnected ? 'sent' : 'queued' ?>"><?= $deviceConnected ? 'Ready' : 'Standby' ?></span>
            </div>
            <div class="pipeline-list">
                <div class="<?= $activeApiKeyCount > 0 ? 'done' : '' ?>"><span></span><strong>Authenticate request</strong><small><?= $activeApiKeyCount > 0 ? 'API key available' : 'Create an API key' ?></small></div>
                <div class="done"><span></span><strong>Queue message</strong><small>Requests are stored before sending</small></div>
                <div class="<?= $deviceConnected ? 'done' : '' ?>"><span></span><strong>Send via device</strong><small><?= $deviceConnected ? 'Linked device ready' : 'Waiting for pairing' ?></small></div>
                <div class="<?= $webhookEnabled ? 'done' : '' ?>"><span></span><strong>Notify webhook</strong><small><?= $webhookEnabled ? 'Callback enabled' : 'Optional callback off' ?></small></div>
            </div>
        </section>
        <section class="card health-card">
            <div class="section-head compact">
                <h2>System Health</h2>
                <span class="pill <?= $readinessScore >= 75 ? 'sent' : 'queued' ?>"><?= $readinessScore >= 75 ? 'Stable' : 'Setup' ?></span>
            </div>
            <div class="health-list compact-health">
                <div><span></span>API Service <strong>Healthy</strong></div>
                <div><span></span>Database <strong>Healthy</strong></div>
                <div><span></span>Queue Status <strong><?= $queueDepth > 0 ? $queueDepth . ' pending' : 'Clear' ?></strong></div>
                <div><span></span>Webhook <strong><?= $webhookEnabled ? 'Active' : 'Inactive' ?></strong></div>
            </div>
        </section>
    </div>
<?php elseif ($section === 'device'): ?>
    <?php page_header('WhatsApp Device', 'Connect and manage your WhatsApp Web linked device.'); ?>
    <div class="summary-strip">
        <div><span>Status</span><strong><?= e($deviceConnected ? 'Online' : 'Offline') ?></strong></div>
        <div><span>Phone</span><strong><?= e($session['phone'] ?? 'None') ?></strong></div>
        <div><span>Worker</span><strong>Polling</strong></div>
    </div>
    <div class="device-page-grid">
        <section class="card device-status-card">
            <div class="section-head">
                <div>
                    <h2>Device Status</h2>
                    <span id="statusBadge" class="device-state-badge <?= e($session['status']) ?>"><?= e($session['status'] === 'connected' ? 'Connected' : (($session['status'] === 'qr') ? 'QR Required' : $session['status'])) ?></span>
                </div>
            </div>
            <div class="device-detail-list">
                <div class="device-detail-item">
                    <span class="detail-icon">PH</span>
                    <div><small>Phone Number</small><strong id="phoneText"><?= e($session['phone'] ?? 'Not connected') ?></strong></div>
                </div>
                <div class="device-detail-item">
                    <span class="detail-icon">NM</span>
                    <div><small>WhatsApp Profile Name</small><strong id="nameText"><?= e($session['push_name'] ?? 'Unknown') ?></strong></div>
                </div>
                <div class="device-detail-item">
                    <span class="detail-icon">LC</span>
                    <div><small>Last Connected</small><strong><?= e($session['connected_at'] ?? 'Never') ?></strong></div>
                </div>
                <div class="device-detail-item">
                    <span class="detail-icon warning">ER</span>
                    <div><small>Last Error</small><strong><?= e($session['last_error'] ?? 'No errors') ?></strong></div>
                </div>
            </div>
            <div class="device-action-stack">
                <button id="connectBtn" type="button">Connect via WhatsApp</button>
                <button id="disconnectBtn" class="ghost" type="button">Logout Device</button>
                <button class="ghost danger-text" type="button">Reset Session</button>
            </div>
        </section>
        <section class="card qr-card">
            <h2>Scan QR Code</h2>
            <p class="muted">Scan the QR code from your WhatsApp mobile app to connect.</p>
            <div class="qr-box device-qr-box" id="qrBox">
                <div id="qrPlaceholder"><strong>No QR yet</strong><span>Click connect to create a new linked-device QR.</span></div>
                <img id="qrImage" alt="WhatsApp QR code" hidden>
            </div>
            <div class="scan-note">Scan from <strong>WhatsApp &gt; Linked devices</strong></div>
            <p class="muted center">QR code will refresh automatically.<br>If you face any issues, try resetting the session.</p>
        </section>
    </div>
<?php elseif ($section === 'api-keys'): ?>
    <?php page_header('API Keys', 'Create separate keys for apps, rotate secrets, and control rate limits.'); ?>
    <div class="summary-strip">
        <div><span>Total keys</span><strong><?= count($apiKeys) ?></strong></div>
        <div><span>Active keys</span><strong><?= $activeApiKeyCount ?></strong></div>
        <div><span>Default limit</span><strong>60/min</strong></div>
    </div>
    <section class="card">
        <div class="section-head">
            <div><h2>Keys</h2><p class="muted">Secrets are shown only once after creation.</p></div>
            <form class="mini-form" method="post" action="<?= e(base_url('index.php?page=credentials')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input name="name" placeholder="Key name" value="Production key">
                <button type="submit">Create Key</button>
            </form>
        </div>
        <?php if ($newCredentials): ?>
            <div class="credential-reveal">
                <label>API Key<input readonly value="<?= e($newCredentials['api_key']) ?>" onclick="this.select()"></label>
                <label>API Secret<input readonly value="<?= e($newCredentials['api_secret']) ?>" onclick="this.select()"></label>
            </div>
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Prefix</th><th>Status</th><th>Limits</th><th>IP allowlist</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($apiKeys as $key): ?>
                        <tr>
                            <td><?= e($key['name']) ?></td>
                            <td><code><?= e($key['key_prefix']) ?>...</code></td>
                            <td><span class="pill <?= e($key['status'] === 'active' ? 'sent' : 'failed') ?>"><?= e($key['status']) ?></span></td>
                            <td><?= (int) $key['rate_per_minute'] ?>/min, <?= (int) $key['rate_per_hour'] ?>/hr, <?= (int) $key['rate_per_day'] ?>/day</td>
                            <td><?= e($key['ip_allowlist'] ?: 'Any IP') ?></td>
                            <td class="row-actions">
                                <form method="post" action="<?= e(base_url('index.php?page=api-key-status')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $key['id'] ?>"><input type="hidden" name="status" value="<?= e($key['status'] === 'active' ? 'disabled' : 'active') ?>"><button class="ghost" type="submit"><?= e($key['status'] === 'active' ? 'Disable' : 'Enable') ?></button></form>
                                <form method="post" action="<?= e(base_url('index.php?page=api-key-delete')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $key['id'] ?>"><button class="ghost danger-text" type="submit">Delete</button></form>
                            </td>
                        </tr>
                        <tr class="settings-row">
                            <td colspan="6">
                                <form class="limits-form" method="post" action="<?= e(base_url('index.php?page=api-key-limits')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                    <input name="rate_per_minute" type="number" min="1" max="300" value="<?= (int) $key['rate_per_minute'] ?>">
                                    <input name="rate_per_hour" type="number" min="1" max="5000" value="<?= (int) $key['rate_per_hour'] ?>">
                                    <input name="rate_per_day" type="number" min="1" max="50000" value="<?= (int) $key['rate_per_day'] ?>">
                                    <input name="ip_allowlist" value="<?= e($key['ip_allowlist'] ?? '') ?>" placeholder="Allowed IPs, comma separated">
                                    <button class="ghost" type="submit">Save Limits</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$apiKeys): ?>
                        <tr><td colspan="6" class="empty-state">No API keys yet. Create a key to start sending messages.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif ($section === 'queue'): ?>
    <?php page_header('Message Queue', 'Track queued, processing, sent, failed, and cancelled messages.'); ?>
    <div class="summary-strip">
        <div><span>Queued</span><strong><?= $queuedCount ?></strong></div>
        <div><span>Processing</span><strong><?= $processingCount ?></strong></div>
        <div><span>Failed</span><strong><?= $failedCount ?></strong></div>
    </div>
    <section class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>To</th><th>Status</th><th>Attempts</th><th>Message</th><th>Error</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>#<?= (int) $message['id'] ?></td>
                            <td><?= e($message['normalized_recipient'] ?: $message['recipient']) ?></td>
                            <td><span class="pill <?= e($message['status']) ?>"><?= e($message['status']) ?></span></td>
                            <td><?= (int) $message['attempts'] ?>/<?= (int) $message['max_attempts'] ?></td>
                            <td><?= e(mb_substr($message['body'], 0, 80)) ?></td>
                            <td><?= e($message['error_message'] ?? '') ?></td>
                            <td class="row-actions">
                                <?php if ($message['status'] === 'queued'): ?>
                                    <form method="post" action="<?= e(base_url('index.php?page=message-cancel')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $message['id'] ?>"><button class="ghost danger-text" type="submit">Cancel</button></form>
                                <?php endif; ?>
                                <?php if (in_array($message['status'], ['failed', 'cancelled'], true)): ?>
                                    <form method="post" action="<?= e(base_url('index.php?page=message-retry')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $message['id'] ?>"><button class="ghost" type="submit">Retry</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$messages): ?>
                        <tr><td colspan="7" class="empty-state">The queue is empty. New API requests will appear here.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif ($section === 'contacts'): ?>
    <?php page_header('Contacts', "Store opted-in recipients. {$optedInCount} contacts are opted in."); ?>
    <div class="summary-strip">
        <div><span>Total contacts</span><strong><?= count($contacts) ?></strong></div>
        <div><span>Opted in</span><strong><?= $optedInCount ?></strong></div>
        <div><span>Export format</span><strong>VCF</strong></div>
    </div>
    <section class="card">
        <div class="section-head">
            <div><h2>Contact Manager</h2><p class="muted">Add recipients manually, import CSV, or export your saved list.</p></div>
            <a class="button small" href="<?= e(base_url('index.php?page=contacts-export-vcf')) ?>">Export VCF</a>
        </div>
        <div class="form-grid">
            <form class="inline-form contact-form" method="post" action="<?= e(base_url('index.php?page=contact-create')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input name="name" placeholder="Name" required><input name="phone" placeholder="Phone" required>
                <label class="check"><input name="opt_in" type="checkbox" checked> Opted in</label><button type="submit">Save</button>
            </form>
            <form class="inline-form import-form" method="post" action="<?= e(base_url('index.php?page=contact-import')) ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input name="contacts_csv" type="file" accept=".csv,text/csv" required><button class="ghost" type="submit">Import CSV</button>
            </form>
        </div>
        <p class="muted">CSV columns: name, phone, opt_in, notes</p>
        <div class="table-wrap"><table><tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr><td><?= e($contact['name']) ?></td><td><?= e($contact['phone']) ?></td><td><?= $contact['opt_in'] ? 'Opted in' : 'No opt-in' ?></td><td><form method="post" action="<?= e(base_url('index.php?page=contact-delete')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $contact['id'] ?>"><button class="ghost danger-text" type="submit">Delete</button></form></td></tr>
            <?php endforeach; ?>
            <?php if (!$contacts): ?>
                <tr><td colspan="4" class="empty-state">No contacts yet. Add your first opted-in recipient above.</td></tr>
            <?php endif; ?>
        </tbody></table></div>
    </section>
<?php elseif ($section === 'webhooks'): ?>
    <?php page_header('Webhooks', 'Receive message status callbacks after the worker sends or fails messages.'); ?>
    <div class="summary-strip">
        <div><span>Status</span><strong><?= $webhookEnabled ? 'Enabled' : 'Disabled' ?></strong></div>
        <div><span>Signing</span><strong><?= !empty($webhook['secret']) ? 'Secret set' : 'Not set' ?></strong></div>
        <div><span>Events</span><strong>sent/failed</strong></div>
    </div>
    <section class="card settings-card">
        <div class="section-head compact">
            <div><h2>Delivery Callback</h2><p class="muted">Configure one HTTPS endpoint for delivery status notifications.</p></div>
            <span class="pill <?= $webhookEnabled ? 'sent' : 'queued' ?>"><?= $webhookEnabled ? 'Active' : 'Inactive' ?></span>
        </div>
        <form class="form compact-form" method="post" action="<?= e(base_url('index.php?page=webhook')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Webhook URL<input name="target_url" placeholder="https://example.com/webhook" value="<?= e($webhook['target_url'] ?? '') ?>"></label>
            <label>Signing Secret<input name="secret" placeholder="Webhook signing secret" value="<?= e($webhook['secret'] ?? '') ?>"></label>
            <?php if ($newWebhookSecret): ?>
                <div class="credential-reveal">
                    <label>New Webhook Secret<input readonly value="<?= e($newWebhookSecret) ?>" onclick="this.select()"></label>
                </div>
            <?php endif; ?>
            <label class="check"><input name="rotate_secret" type="checkbox"> Rotate signing secret</label>
            <label class="check"><input name="enabled" type="checkbox" <?= !empty($webhook['enabled']) ? 'checked' : '' ?>> Enable webhook</label>
            <button type="submit">Save Webhook</button>
        </form>
    </section>
<?php elseif ($section === 'campaigns'): ?>
    <?php page_header('Campaigns', 'Queue one message per opted-in contact with template replacement.'); ?>
    <div class="summary-strip">
        <div><span>Recent campaigns</span><strong><?= count($campaigns) ?></strong></div>
        <div><span>Eligible contacts</span><strong><?= $optedInCount ?></strong></div>
        <div><span>Template token</span><strong>{name}</strong></div>
    </div>
    <section class="card">
        <div class="section-head compact">
            <div><h2>Queue Campaign</h2><p class="muted">Use a lightweight template to send one message per opted-in contact.</p></div>
        </div>
        <form class="campaign-form" method="post" action="<?= e(base_url('index.php?page=campaign-create')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input name="name" placeholder="Campaign name" value="Opt-in announcement" required>
            <textarea name="message_template" placeholder="Hi {name}, this is a message from WhatsApp API Hub." required></textarea>
            <button type="submit">Queue Campaign</button>
        </form>
        <div class="table-wrap">
            <table><thead><tr><th>ID</th><th>Name</th><th>Targets</th><th>Queued</th><th>Status</th><th>Created</th></tr></thead><tbody>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr><td>#<?= (int) $campaign['id'] ?></td><td><?= e($campaign['name']) ?></td><td><?= (int) $campaign['target_count'] ?></td><td><?= (int) $campaign['queued_count'] ?></td><td><span class="pill <?= e($campaign['status'] === 'completed' ? 'sent' : 'queued') ?>"><?= e($campaign['status']) ?></span></td><td><?= e($campaign['created_at']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$campaigns): ?>
                    <tr><td colspan="6" class="empty-state">No campaigns yet. Queue one above when contacts are opted in.</td></tr>
                <?php endif; ?>
            </tbody></table>
        </div>
    </section>
<?php endif; ?>
</section>

<?php if ($section === 'device'): ?>
    <script src="<?= e(base_url('assets/dashboard.js')) ?>"></script>
<?php endif; ?>
