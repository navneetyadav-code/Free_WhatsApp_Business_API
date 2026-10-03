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
$queuePage = max(1, (int) ($_GET['p'] ?? 1));
$queuePerPage = 20;
$totalQueueMessages = count_messages((int) $user['id']);
$totalPages = max(1, ceil($totalQueueMessages / $queuePerPage));
$queueMessages = paginate_messages((int) $user['id'], $queuePage, $queuePerPage);
$apiRequestCountStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ?');
$apiRequestCountStmt->execute([(int) $user['id']]);
$apiRequestCount = (int) $apiRequestCountStmt->fetchColumn();

$auditPage = max(1, (int) ($_GET['p'] ?? 1));
$auditPerPage = 25;
$auditOffset = ($auditPage - 1) * $auditPerPage;
$auditLogs = [];
$totalAuditPages = 1;

if ($section === 'audit') {
    $totalAuditPages = max(1, ceil($apiRequestCount / $auditPerPage));
    $auditLogsStmt = $pdo->prepare('
        SELECT api_request_logs.*, api_keys.name AS key_name, api_keys.key_prefix 
        FROM api_request_logs 
        LEFT JOIN api_keys ON api_keys.id = api_request_logs.api_key_id 
        WHERE api_request_logs.user_id = ? 
        ORDER BY api_request_logs.id DESC 
        LIMIT ' . (int)$auditPerPage . ' OFFSET ' . (int)$auditOffset
    );
    $auditLogsStmt->execute([$user['id']]);
    $auditLogs = $auditLogsStmt->fetchAll();
}

$chatbotRules = [];
if ($section === 'chatbot') {
    $stmt = $pdo->prepare('SELECT * FROM chatbot_rules WHERE user_id = ? ORDER BY id DESC');
    $stmt->execute([$user['id']]);
    $chatbotRules = $stmt->fetchAll();
}
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
$contactOptionsStmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC, id DESC');
$contactOptionsStmt->execute([$user['id']]);
$contactOptions = $contactOptionsStmt->fetchAll();
$editingCampaignId = max(0, (int) ($_GET['edit'] ?? 0));
$editingCampaign = $editingCampaignId ? get_campaign((int) $user['id'], $editingCampaignId) : null;

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
    <!-- Page Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Overview</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Monitor your WhatsApp API traffic and account health.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= e(base_url('index.php?page=webhooks')) ?>" class="px-3.5 py-2 text-[13px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-saas hover:bg-slate-50 hover:shadow-saas-hover focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all text-center">
                Manage Webhooks
            </a>
            <a href="<?= e(base_url('index.php?page=message')) ?>" class="px-3.5 py-2 text-[13px] font-semibold text-white bg-brand-accent rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-accent transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Send Message
            </a>
        </div>
    </div>

    <!-- Top KPI Cards (High Density SaaS Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1 -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-saas relative overflow-hidden group hover:shadow-saas-hover transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[13px] font-semibold text-slate-500">Total Messages</p>
                <div class="p-1.5 bg-slate-50 rounded-md border border-slate-100 text-slate-400 group-hover:text-brand-wa_med transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight"><?= number_format($sentCount + $failedCount + $queuedCount + $processingCount) ?></h3>
            </div>
            <div class="mt-4 flex items-center gap-2 text-[12px] font-medium">
                <span class="text-emerald-600 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded flex items-center gap-0.5">
                    <?= number_format($sentCount) ?>
                </span>
                <span class="text-slate-400">delivered successfully</span>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-saas relative overflow-hidden group hover:shadow-saas-hover transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[13px] font-semibold text-slate-500">Delivery Rate</p>
                <div class="p-1.5 bg-slate-50 rounded-md border border-slate-100 text-slate-400 group-hover:text-brand-wa_med transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight"><?= $sentRate ?>%</h3>
            </div>
            <!-- Mini sparkline precise -->
            <div class="mt-4 flex items-end gap-1 h-5">
                <div class="w-full bg-slate-100 rounded-sm h-[60%]"></div>
                <div class="w-full bg-slate-100 rounded-sm h-[80%]"></div>
                <div class="w-full bg-slate-100 rounded-sm h-[70%]"></div>
                <div class="w-full bg-slate-100 rounded-sm h-[90%]"></div>
                <div class="w-full bg-brand-wa_light rounded-sm h-[100%] shadow-[0_0_8px_rgba(37,211,102,0.4)]"></div>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-saas relative overflow-hidden group hover:shadow-saas-hover transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[13px] font-semibold text-slate-500">API Requests</p>
                <div class="p-1.5 bg-slate-50 rounded-md border border-slate-100 text-slate-400 group-hover:text-brand-wa_med transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight"><?= number_format($apiRequestCount) ?></h3>
                <span class="text-[12px] font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded flex items-center">
                    Audited
                </span>
            </div>
            <p class="text-[12px] text-slate-500 mt-4 truncate font-medium">Last active: <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-slate-600"><?= e($lastActivity === 'No activity yet' ? 'None' : substr((string) $lastActivity, 5, 11)) ?></span></p>
        </div>

        <!-- KPI 4 -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-saas relative overflow-hidden group hover:shadow-saas-hover transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[13px] font-semibold text-slate-500">Worker Queue</p>
                <div class="p-1.5 bg-slate-50 rounded-md border border-slate-100 text-slate-400 group-hover:text-brand-wa_med transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-900 tracking-tight"><?= number_format($queueDepth) ?></h3>
                <span class="text-[12px] font-semibold text-slate-500">pending</span>
            </div>
            <div class="mt-4 flex justify-between text-[12px] font-medium text-slate-500 border-t border-slate-100 pt-3">
                <span class="flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div><?= $processingCount ?> processing</span>
                <span class="flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div><?= $failedCount ?> failed</span>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Traffic Chart (Spans 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-saas flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h2 class="text-[14px] font-bold text-slate-900">Workspace Readiness</h2>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-50 text-slate-600 border border-slate-200/80 shadow-sm"><?= $readinessScore ?>% Ready</span>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-center items-center bg-slate-50/30">
                <div class="w-full max-w-md space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full <?= $deviceConnected ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]' : 'bg-rose-500' ?>"></div>
                            <span class="font-medium text-[13px] text-slate-700">WhatsApp Device</span>
                        </div>
                        <span class="text-[12px] font-semibold <?= $deviceConnected ? 'text-emerald-600' : 'text-rose-600' ?>"><?= $deviceConnected ? 'Connected' : 'Pair Required' ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full <?= $activeApiKeyCount > 0 ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]' : 'bg-rose-500' ?>"></div>
                            <span class="font-medium text-[13px] text-slate-700">API Credentials</span>
                        </div>
                        <span class="text-[12px] font-semibold <?= $activeApiKeyCount > 0 ? 'text-emerald-600' : 'text-rose-600' ?>"><?= $activeApiKeyCount ?> Active Keys</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full <?= $webhookEnabled ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]' : 'bg-amber-400' ?>"></div>
                            <span class="font-medium text-[13px] text-slate-700">Delivery Webhook</span>
                        </div>
                        <span class="text-[12px] font-semibold <?= $webhookEnabled ? 'text-emerald-600' : 'text-amber-600' ?>"><?= $webhookEnabled ? 'Enabled' : 'Optional (Disabled)' ?></span>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="<?= e($nextActionUrl) ?>" class="px-4 py-2 bg-brand-wa_med text-white rounded-lg text-[13px] font-semibold shadow-sm hover:bg-brand-wa_dark transition-colors"><?= e($nextAction) ?></a>
                </div>
            </div>
        </div>

        <!-- Platform Health & Connectivity -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-saas flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-white flex justify-between items-center">
                <h2 class="text-[14px] font-bold text-slate-900">Platform Status</h2>
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="<?= $deviceConnected ? 'animate-ping' : '' ?> absolute inline-flex h-full w-full rounded-full <?= $deviceConnected ? 'bg-emerald-400' : 'bg-slate-400' ?> opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 <?= $deviceConnected ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]' : 'bg-slate-500' ?>"></span>
                </span>
            </div>
            <div class="p-0 flex-1 flex flex-col">
                <ul class="divide-y divide-slate-100 flex-1">
                    <!-- Status Item 1 -->
                    <li class="px-5 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg <?= $deviceConnected ? 'bg-emerald-50 border-emerald-100 text-emerald-600 group-hover:bg-emerald-100' : 'bg-slate-50 border-slate-200 text-slate-500 group-hover:bg-slate-100' ?> border flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-slate-900 tracking-tight"><?= e($session['phone'] ?? 'No Phone Linked') ?></p>
                                <p class="text-[11px] text-slate-500 font-medium"><?= e($session['push_name'] ?? 'Unknown Profile') ?></p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold <?= $deviceConnected ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/80' ?> shadow-sm"><?= $deviceConnected ? 'Connected' : 'Offline' ?></span>
                    </li>
                    <!-- Status Item 2 -->
                    <li class="px-5 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg <?= $webhookEnabled ? 'bg-emerald-50 border-emerald-100 text-emerald-600 group-hover:bg-emerald-100' : 'bg-slate-50 border-slate-200 text-slate-500 group-hover:bg-slate-100' ?> border flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-slate-900 tracking-tight">Main Webhook</p>
                                <p class="text-[11px] text-slate-500 font-mono tracking-tight truncate max-w-[110px]"><?= e($webhookEnabled ? parse_url($webhook['target_url'] ?? '', PHP_URL_HOST) : 'Not Configured') ?></p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold <?= $webhookEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/80' ?> shadow-sm"><?= $webhookEnabled ? 'Active' : 'N/A' ?></span>
                    </li>
                    <!-- Status Item 3 -->
                    <li class="px-5 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-slate-900 tracking-tight">Database & Core</p>
                                <p class="text-[11px] text-slate-500 font-mono tracking-tight">Local</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm">Operational</span>
                    </li>
                </ul>
                <div class="p-3 bg-slate-50 border-t border-slate-100 text-center mt-auto">
                    <a href="<?= e(base_url('index.php?page=device')) ?>" class="text-[12px] font-semibold text-slate-500 hover:text-slate-900 transition-colors flex items-center justify-center gap-1">Manage Device <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent API Activity Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
            <h2 class="text-[14px] font-bold text-slate-900">Recent Queue Activity</h2>
            <div class="flex gap-2">
                <a href="<?= e(base_url('index.php?page=queue')) ?>" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors border border-transparent hover:border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Event</th>
                        <th class="px-5 py-3.5">Recipient</th>
                        <th class="px-5 py-3.5 hidden sm:table-cell">Message Preview</th>
                        <th class="px-5 py-3.5 text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] divide-y divide-slate-100">
                    <?php foreach (array_slice($messages, 0, 5) as $message): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group cursor-default">
                            <td class="px-5 py-3">
                                <?php if ($message['status'] === 'sent'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sent
                                    </span>
                                <?php elseif ($message['status'] === 'failed'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                    </span>
                                <?php elseif ($message['status'] === 'queued' || $message['status'] === 'processing'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> <?= e(ucfirst($message['status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60 shadow-sm">MSG</span>
                                    <span class="font-mono text-slate-700 text-[12px] tracking-tight">Outgoing</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600 font-mono text-[12px] tracking-tight"><?= e($message['normalized_recipient'] ?: $message['recipient']) ?></td>
                            <td class="px-5 py-3 text-slate-500 hidden sm:table-cell font-medium truncate max-w-xs"><?= e(mb_substr($message['body'], 0, 40)) ?><?= mb_strlen($message['body']) > 40 ? '...' : '' ?></td>
                            <td class="px-5 py-3 text-right text-slate-400 text-[12px] group-hover:text-slate-600 transition-colors"><?= e(substr((string) $message['created_at'], 11, 8)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$messages): ?>
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No messages have been queued yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 text-center">
            <a href="<?= e(base_url('index.php?page=queue')) ?>" class="text-[13px] font-semibold text-slate-600 hover:text-slate-900 transition-colors">View full queue log</a>
        </div>
    </div>

    <!-- Footer Spacer -->
    <div class="h-8"></div>
<?php elseif ($section === 'message'): ?>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Compose Message</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Queue a message to saved contacts, campaign audiences, or a manual number.</p>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Saved Contacts</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1"><?= count($contactOptions) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Active Campaigns</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1"><?= count($campaigns) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Rich Media</p>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight mt-1">Supported</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
            </div>
        </div>
    </div>

    <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="text-[15px] font-bold text-slate-900">New Message</h2>
                <p class="text-[13px] text-slate-500 mt-0.5">Select a recipient source and write your content.</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-white text-slate-600 border border-slate-200/80 shadow-sm flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-wa_light animate-pulse"></span> Pipeline Ready
            </span>
        </div>
        
        <div class="p-6">
            <form method="post" action="<?= e(base_url('index.php?page=message-send')) ?>" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Target Selection -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Recipient Source</label>
                            <div class="relative">
                                <select name="recipient_mode" id="recipientMode" data-message-mode-select class="block w-full pl-3 pr-10 py-2.5 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-slate-50/50 appearance-none transition-all shadow-sm">
                                    <option value="contact">Saved Contact</option>
                                    <option value="campaign">Campaign Audience</option>
                                    <option value="number">New Number</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Target Inputs -->
                        <div class="p-4 bg-slate-50/80 rounded-lg border border-slate-100">
                            <!-- Contact -->
                            <div data-message-panel="contact">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Select Contact</label>
                                <div class="relative">
                                    <select name="contact_id" class="block w-full pl-3 pr-10 py-2 text-[13px] border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-white appearance-none transition-all shadow-sm">
                                        <option value="">-- Choose a recipient --</option>
                                        <?php foreach ($contactOptions as $contact): ?>
                                            <option value="<?= (int) $contact['id'] ?>"><?= e($contact['name']) ?> &middot; <?= e($contact['phone']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Campaign -->
                            <div data-message-panel="campaign" hidden>
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Select Campaign</label>
                                <div class="relative">
                                    <select name="campaign_id" class="block w-full pl-3 pr-10 py-2 text-[13px] border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-white appearance-none transition-all shadow-sm">
                                        <option value="">-- Choose a campaign --</option>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <option value="<?= (int) $campaign['id'] ?>">#<?= (int) $campaign['id'] ?> - <?= e($campaign['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- New Number -->
                            <div data-message-panel="number" hidden>
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Phone Number</label>
                                <input name="manual_number" type="text" placeholder="+1234567890" class="block w-full px-3 py-2 text-[13px] border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-white transition-all shadow-sm font-mono">
                                <p class="text-[11px] text-slate-500 mt-1.5">Include country code (e.g., +91 for India).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment Upload -->
                    <div class="space-y-4">
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Media Attachment <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 bg-slate-50/50 hover:bg-slate-50 hover:border-brand-wa_light/50 transition-colors group relative cursor-pointer flex flex-col items-center justify-center text-center">
                            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 group-hover:text-brand-wa_med group-hover:border-brand-wa_light/30 transition-colors mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            </div>
                            <p class="text-[13px] font-medium text-slate-700">Click to upload or drag and drop</p>
                            <p class="text-[11px] text-slate-500 mt-1">Images, MP4, PDF, DOCX, TXT (up to 15MB)</p>
                            <input id="messageAttachment" name="attachment" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.txt,image/*,video/mp4,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        </div>
                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-md border border-slate-100">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span class="text-[12px] font-medium text-slate-600 truncate" id="attachmentStatus">No file selected</span>
                        </div>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5 flex justify-between">
                        Message Content
                        <span class="text-slate-400 font-normal">Supports *bold*, _italic_, ~strikethrough~</span>
                    </label>
                    <textarea name="message" data-presets='["{name}", "{phone}"]' class="wa-editor block w-full px-3.5 py-3 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-white transition-all shadow-sm placeholder:text-slate-300" placeholder="Type your WhatsApp message here..." required></textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="humanize" value="1" checked class="w-4 h-4 rounded border-slate-300 text-brand-wa_light focus:ring-brand-wa_light focus:ring-offset-0 transition-colors cursor-pointer">
                        <span class="text-[13px] font-semibold text-slate-700 group-hover:text-slate-900 transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Humanized Delivery
                        </span>
                    </label>
                    <div class="flex items-center gap-3">
                        <button type="reset" class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:text-slate-900 transition-colors">Clear form</button>
                        <button type="submit" class="px-5 py-2.5 text-[13px] font-semibold text-white bg-brand-accent rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-accent transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Queue Message
                    </button>
                </div>
            </form>
        </div>
    </section>
    
    <!-- Footer Spacer -->
    <div class="h-8"></div>

<?php elseif ($section === 'device'): ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">WhatsApp Device</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Connect and manage your WhatsApp Web linked device.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="disconnectBtn" class="px-3.5 py-2 text-[13px] font-semibold text-rose-600 bg-white border border-rose-200 rounded-lg shadow-saas hover:bg-rose-50 hover:shadow-saas-hover focus:outline-none focus:ring-2 focus:ring-rose-200 transition-all text-center <?= $deviceConnected ? '' : 'hidden' ?>">
                Logout Device
            </button>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Device Status</p>
                <div class="flex items-center gap-2 mt-1">
                    <span id="statusIndicator" class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 <?= $deviceConnected ? 'bg-emerald-400' : 'bg-slate-400' ?>"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 <?= $deviceConnected ? 'bg-emerald-500' : 'bg-slate-500' ?>"></span>
                    </span>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="statusText"><?= $deviceConnected ? 'Online' : 'Offline' ?></h3>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $deviceConnected ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-600' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Phone Number</p>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight mt-1" id="topPhoneText"><?= e($session['phone'] ?? 'None') ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Worker Process</p>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight mt-1">Polling</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Device Info Card -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900 tracking-tight">Connection Details</h2>
                    <p class="text-[12px] text-slate-500 mt-0.5">Live session attributes.</p>
                </div>
                <span id="statusBadge" class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex items-center gap-1.5 <?= $deviceConnected ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' ?>"><?= $deviceConnected ? 'Connected' : 'Disconnected' ?></span>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-semibold text-slate-500">Linked Number</p>
                        <p class="text-[14px] font-medium text-slate-900 mt-0.5" id="phoneText"><?= e($session['phone'] ?? 'Not connected') ?></p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-slate-100"></div>
                
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-semibold text-slate-500">WhatsApp Profile Name</p>
                        <p class="text-[14px] font-medium text-slate-900 mt-0.5" id="nameText"><?= e($session['push_name'] ?? 'Unknown') ?></p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-slate-100"></div>
                
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-semibold text-slate-500">Last Connected</p>
                        <p class="text-[14px] font-medium text-slate-900 mt-0.5" id="lastConnectedText"><?= e($session['connected_at'] ?? 'Never') ?></p>
                    </div>
                </div>
                
                <?php if (!empty($session['last_error'])): ?>
                <div class="w-full h-px bg-slate-100"></div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center text-rose-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-semibold text-rose-600">Last Error</p>
                        <p class="text-[13px] font-mono text-rose-700 mt-0.5"><?= e($session['last_error']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center gap-3" id="deviceActions">
                <!-- Connect / Sleep buttons toggled via JS -->
                <button id="connectBtn" type="button" class="<?= $deviceConnected ? 'hidden' : '' ?> px-4 py-2.5 text-[13px] font-semibold text-white bg-brand-accent rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-accent transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Connect via WhatsApp
                </button>

                <button id="sleepBtn" type="button" class="<?= !$deviceConnected || $deviceSleeping ? 'hidden' : '' ?> px-4 py-2 text-[13px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg shadow-sm hover:bg-amber-100 focus:outline-none transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    Sleep Connection
                </button>
                
                                <button id="resetBtn" type="button" class="px-4 py-2 text-[13px] font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg shadow-sm hover:bg-rose-100 focus:outline-none transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Session
                </button>
                <button id="wakeupBtn" type="button" class="<?= !$deviceConnected || !$deviceSleeping ? 'hidden' : '' ?> px-4 py-2 text-[13px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg shadow-sm hover:bg-emerald-100 focus:outline-none transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Wake Up Connection
                </button>
            </div>
        </section>

        <!-- QR Code / Profile Card -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden flex flex-col items-center justify-center p-8 text-center min-h-[400px]">
            <div id="profileContainer" class="<?= $deviceConnected ? '' : 'hidden' ?> flex flex-col items-center">
                <div class="relative mb-4">
                    <img id="profileImage" src="" alt="WhatsApp Profile" class="w-32 h-32 rounded-full border-4 border-emerald-100 shadow-lg object-cover bg-slate-100">
                    <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-2 border-white rounded-full"></div>
                </div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="profileName"><?= e($session['push_name'] ?? 'Connected') ?></h3>
                <p class="text-[13px] text-slate-500 mt-1">Your WhatsApp device is securely linked and ready.</p>
            </div>

            <div id="qrContainer" class="<?= $deviceConnected ? 'hidden' : '' ?> flex flex-col items-center w-full">
                <div class="w-16 h-16 bg-brand-wa_light/10 text-brand-wa_med rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
                <h2 class="text-[16px] font-bold text-slate-900">Pair Device</h2>
                <p class="text-[13px] text-slate-500 mt-1 mb-6 max-w-[250px]">Open WhatsApp on your phone, tap <strong>Linked devices</strong>, and scan the QR code to connect.</p>
                
                <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-sm inline-block" id="qrBox">
                    <div id="qrPlaceholder" class="w-[250px] h-[250px] bg-slate-50 rounded-xl flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-200">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <span class="text-[12px] font-medium max-w-[150px] text-center">Click Connect to generate QR code</span>
                    </div>
                    <img id="qrImage" alt="WhatsApp QR code" class="w-[250px] h-[250px] rounded-xl hidden">
                </div>
            </div>
        </section>
    </div>

<?php elseif ($section === 'api-keys'): ?>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">API Keys</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Create and manage access keys for your integrations.</p>
        </div>
        <form method="post" action="<?= e(base_url('index.php?page=credentials')) ?>" class="flex items-center gap-2">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input name="name" type="text" placeholder="Key name (e.g. Zapier)" required class="px-3 py-2 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med bg-white shadow-sm w-48 transition-all">
            <button type="submit" class="px-4 py-2 text-[13px] font-semibold text-white bg-slate-900 rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Generate Key
            </button>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Active Keys</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1"><?= $activeApiKeyCount ?> <span class="text-[14px] font-medium text-slate-400">/ <?= count($apiKeys) ?></span></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Default Limit</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">60 <span class="text-[14px] font-medium text-slate-400">/ min</span></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Security</p>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight mt-1">IP Restricted</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
        </div>
    </div>

    <!-- New Credentials Reveal -->
    <?php if ($newCredentials): ?>
        <div class="mb-6 p-6 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
                <svg class="w-32 h-32 text-emerald-600 transform translate-x-4 -translate-y-4" fill="currentColor" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-emerald-900 tracking-tight">API Key Generated Successfully</h3>
                </div>
                <p class="text-[13px] text-emerald-700 font-medium mb-5">Please download or copy your API Token now. <strong class="text-emerald-900">It will never be shown again.</strong></p>
                
                <div class="mb-6">
                    <label class="block text-[12px] font-bold text-emerald-800 uppercase tracking-wider mb-1.5">Authorization Bearer Token</label>
                    <input readonly value="<?= e($newCredentials['api_key']) ?>" onclick="this.select()" class="block w-full px-4 py-3 text-[13px] border border-emerald-300 rounded-lg bg-white text-slate-800 font-mono shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-copy" title="Click to copy">
                </div>

                <button type="button" onclick="downloadApiKey('<?= e($newCredentials['api_key']) ?>')" class="px-5 py-2.5 text-[13px] font-bold text-white bg-emerald-600 border border-emerald-700 rounded-lg shadow-sm hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download JSON Keyfile
                </button>
            </div>
        </div>
        <script>
            function downloadApiKey(token) {
                const data = JSON.stringify({ 
                    access_token: token, 
                    created_at: new Date().toISOString(),
                    note: "Use this token in the Authorization header: Bearer <token>"
                }, null, 2);
                const blob = new Blob([data], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'whatsapp_api_credentials.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
    <?php endif; ?>

    <!-- Table Section -->
    <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-[15px] font-bold text-slate-900">Active Keys</h2>
            <p class="text-[13px] text-slate-500 mt-0.5">Manage permissions, IP restrictions, and rate limits.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">Key Name & Prefix</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Rate Limits</th>
                        <th class="px-6 py-4 font-semibold">IP Allowlist</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($apiKeys as $key): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-[13px] font-bold text-slate-900"><?= e($key['name']) ?></div>
                                <div class="text-[12px] font-mono text-slate-500 mt-0.5"><code><?= e($key['key_prefix']) ?>...</code></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex inline-flex items-center gap-1.5 <?= e($key['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= e($key['status'] === 'active' ? 'bg-emerald-500' : 'bg-rose-500') ?>"></span>
                                    <?= e(ucfirst($key['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-[12px] text-slate-600 font-medium">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded"><?= (int) $key['rate_per_minute'] ?>/m</span>
                                    <span class="bg-slate-100 px-2 py-0.5 rounded"><?= (int) $key['rate_per_hour'] ?>/h</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($key['ip_allowlist']): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded-md border border-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Restricted
                                    </span>
                                <?php else: ?>
                                    <span class="text-[12px] text-slate-400 font-medium">Any IP</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Toggle Settings Button -->
                                    <button type="button" onclick="document.getElementById('settings-<?= (int) $key['id'] ?>').classList.toggle('hidden')" class="px-2.5 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition-colors shadow-sm">
                                        Configure
                                    </button>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-status')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <input type="hidden" name="status" value="<?= e($key['status'] === 'active' ? 'disabled' : 'active') ?>">
                                        <button type="submit" class="px-2.5 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition-colors shadow-sm">
                                            <?= e($key['status'] === 'active' ? 'Disable' : 'Enable') ?>
                                        </button>
                                    </form>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-delete')) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this API key? Apps using it will immediately lose access.');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Expandable Settings Row -->
                        <tr id="settings-<?= (int) $key['id'] ?>" class="hidden bg-slate-50/80 border-b border-slate-200 shadow-inner">
                            <td colspan="5" class="p-0">
                                <div class="p-6 border-l-2 border-brand-wa_light ml-px">
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-limits')) ?>" class="space-y-4">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <label class="block">
                                                    <span class="block text-[12px] font-semibold text-slate-700 mb-1.5">Rate Limit / Minute</span>
                                                    <input name="rate_per_minute" type="number" min="1" max="300" value="<?= (int) $key['rate_per_minute'] ?>" class="block w-full px-3 py-2 text-[13px] border border-slate-200 rounded-md bg-white shadow-sm focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med">
                                                </label>
                                                <label class="block">
                                                    <span class="block text-[12px] font-semibold text-slate-700 mb-1.5">Rate Limit / Hour</span>
                                                    <input name="rate_per_hour" type="number" min="1" max="5000" value="<?= (int) $key['rate_per_hour'] ?>" class="block w-full px-3 py-2 text-[13px] border border-slate-200 rounded-md bg-white shadow-sm focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med">
                                                </label>
                                                <label class="block">
                                                    <span class="block text-[12px] font-semibold text-slate-700 mb-1.5">Rate Limit / Day</span>
                                                    <input name="rate_per_day" type="number" min="1" max="50000" value="<?= (int) $key['rate_per_day'] ?>" class="block w-full px-3 py-2 text-[13px] border border-slate-200 rounded-md bg-white shadow-sm focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med">
                                                </label>
                                                
                                                <label class="block md:col-span-3">
                                                    <span class="block text-[12px] font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                                                        IP Allowlist 
                                                        <span class="text-[11px] font-normal text-slate-400 font-mono">(Comma separated, e.g. 192.168.1.1, 10.0.0.5)</span>
                                                    </span>
                                                    <input name="ip_allowlist" value="<?= e($key['ip_allowlist'] ?? '') ?>" placeholder="Leave blank to allow any IP" class="block w-full px-3 py-2 text-[13px] border border-slate-200 rounded-md bg-white shadow-sm focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med font-mono">
                                                </label>
                                                <label class="flex items-center gap-2 mt-4 cursor-pointer md:col-span-3">
                                                    <input type="checkbox" name="allow_humanize" value="1" <?= empty($key['allow_humanize']) ? '' : 'checked' ?> class="w-4 h-4 rounded border-slate-300 text-brand-wa_light focus:ring-brand-wa_light focus:ring-offset-0 transition-colors cursor-pointer">
                                                    <span class="text-[13px] font-semibold text-slate-700">Force Humanized Delivery for this API Key</span>
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-end justify-end">
                                                <button type="submit" class="w-full md:w-auto px-5 py-2.5 text-[13px] font-bold text-white bg-slate-900 rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
                                                    Save Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$apiKeys): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-[13px] text-slate-500">No API keys created yet. Generate one above to integrate with your apps.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($section === 'queue'): ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Message Queue</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Track queued, processing, sent, failed, and cancelled messages.</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Queued</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1" id="liveQueuedCount"><?= $queuedCount ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Processing</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1" id="liveProcessingCount"><?= $processingCount ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Failed</p>
                <h3 class="text-2xl font-bold text-rose-600 tracking-tight mt-1" id="liveQueueFailedCount"><?= $failedCount ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>
    
    <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">To</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Attempts</th>
                        <th class="px-6 py-4 font-semibold">Message</th>
                        <th class="px-6 py-4 font-semibold">Error</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="liveQueueTable" class="divide-y divide-slate-100">
                    <?php foreach ($queueMessages as $message): ?>
                        <?php 
                            $statusColors = [
                                'queued' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'cancelled' => 'bg-orange-50 text-orange-700 border-orange-200'
                            ];
                            $dotColors = [
                                'queued' => 'bg-slate-500',
                                'processing' => 'bg-blue-500 animate-pulse',
                                'sent' => 'bg-emerald-500',
                                'failed' => 'bg-rose-500',
                                'cancelled' => 'bg-orange-500'
                            ];
                            $sc = $statusColors[$message['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            $dc = $dotColors[$message['status']] ?? 'bg-slate-500';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-[13px] font-mono text-slate-500">#<?= (int) $message['id'] ?></td>
                            <td class="px-6 py-4 text-[13px] font-medium text-slate-900"><?= e($message['normalized_recipient'] ?: $message['recipient']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex inline-flex items-center gap-1.5 <?= $sc ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dc ?>"></span>
                                    <?= e(ucfirst($message['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[13px] text-slate-600 font-mono"><?= (int) $message['attempts'] ?>/<?= (int) $message['max_attempts'] ?></td>
                            <td class="px-6 py-4 text-[13px] text-slate-600 truncate max-w-xs" title="<?= e($message['body']) ?>"><?= e(mb_substr($message['body'], 0, 60)) ?><?= mb_strlen($message['body']) > 60 ? '...' : '' ?></td>
                            <td class="px-6 py-4 text-[12px] text-rose-600 max-w-xs truncate" title="<?= e($message['error_message'] ?? '') ?>"><?= e($message['error_message'] ?? '') ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($message['status'] === 'queued'): ?>
                                        <form method="post" action="<?= e(base_url('index.php?page=message-cancel')) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1.5 text-[12px] font-semibold text-rose-600 bg-white border border-rose-200 rounded hover:bg-rose-50 transition-colors shadow-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (in_array($message['status'], ['failed', 'cancelled'], true)): ?>
                                        <form method="post" action="<?= e(base_url('index.php?page=message-retry')) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition-colors shadow-sm">Retry</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$queueMessages): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-[13px] text-slate-500">The queue is empty. New API requests will appear here.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="text-[13px] text-slate-500 font-medium">
                Showing <strong class="text-slate-900"><?= (($queuePage - 1) * $queuePerPage) + 1 ?></strong> to <strong class="text-slate-900"><?= min($totalQueueMessages, $queuePage * $queuePerPage) ?></strong> of <strong class="text-slate-900"><?= $totalQueueMessages ?></strong> messages
            </div>
            <div class="flex items-center gap-1">
                <?php if ($queuePage > 1): ?>
                    <a href="<?= e(base_url('index.php?page=queue&p=' . ($queuePage - 1))) ?>" class="px-3 py-1.5 text-[13px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-sm">Previous</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1.5 text-[13px] font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-md cursor-not-allowed">Previous</button>
                <?php endif; ?>
                
                <div class="px-3 text-[13px] font-semibold text-slate-700">
                    Page <?= $queuePage ?> of <?= $totalPages ?>
                </div>

                <?php if ($queuePage < $totalPages): ?>
                    <a href="<?= e(base_url('index.php?page=queue&p=' . ($queuePage + 1))) ?>" class="px-3 py-1.5 text-[13px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-sm">Next</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1.5 text-[13px] font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-md cursor-not-allowed">Next</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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
            <textarea name="message_template" class="wa-editor w-full border border-slate-200 rounded p-2 text-sm" data-presets='["{name}", "{phone}"]' placeholder="Hi {name}, this is a message from WhatsApp API Hub." required></textarea>
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

<?php elseif ($section === 'audit'): ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">API Audit Log</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Detailed history of all incoming API requests and activity.</p>
        </div>
        <div class="flex items-center gap-3 bg-white px-4 py-2 border border-slate-200 rounded-lg shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.5)]"></span>
            <span class="text-[13px] font-semibold text-slate-700">Logging Active</span>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Total Requests</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1"><?= number_format($apiRequestCount) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Last 24 Hours</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">
                    <?php
                    $last24hStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
                    $last24hStmt->execute([$user['id']]);
                    echo number_format((int) $last24hStmt->fetchColumn());
                    ?>
                </h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-saas flex items-center justify-between">
            <div>
                <p class="text-[12px] font-semibold text-slate-500">Blocked Requests</p>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">
                    <?php
                    $blockedStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ? AND status_code IN (401, 403, 429)');
                    $blockedStmt->execute([$user['id']]);
                    echo number_format((int) $blockedStmt->fetchColumn());
                    ?>
                </h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 border border-rose-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <section class="bg-white rounded-xl border border-slate-200 shadow-saas overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-[14px] font-bold text-slate-900">Request Logs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Endpoint</th>
                        <th class="px-6 py-3.5">API Key</th>
                        <th class="px-6 py-3.5">IP Address</th>
                        <th class="px-6 py-3.5 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] divide-y divide-slate-100">
                    <?php foreach ($auditLogs as $log): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-3">
                                <?php if ($log['status_code'] >= 200 && $log['status_code'] < 300): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm">
                                        <?= (int) $log['status_code'] ?> Success
                                    </span>
                                <?php elseif ($log['status_code'] == 429): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-sm">
                                        <?= (int) $log['status_code'] ?> Rate Limit
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-sm" title="<?= e($log['error_message'] ?? '') ?>">
                                        <?= (int) $log['status_code'] ?> Error
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 font-mono text-[12px] text-slate-600">
                                /api/<?= e($log['endpoint']) ?>
                            </td>
                            <td class="px-6 py-3">
                                <?php if ($log['key_name']): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[12px] font-medium text-slate-700"><?= e($log['key_name']) ?></span>
                                        <span class="text-[10px] bg-slate-100 border border-slate-200 text-slate-500 px-1.5 rounded font-mono"><?= e($log['key_prefix']) ?>...</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 italic text-[12px]">Unknown/Invalid</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <span class="font-mono text-[12px] text-slate-600 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded shadow-sm"><?= e($log['ip_address']) ?></span>
                            </td>
                            <td class="px-6 py-3 text-right text-slate-500 text-[12px] group-hover:text-slate-700 transition-colors">
                                <?= e(date('M j, Y g:i A', strtotime($log['created_at']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$auditLogs): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-[13px] text-slate-500">No API activity logged yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalAuditPages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/30">
            <span class="text-[12px] text-slate-500 font-medium">Page <?= $auditPage ?> of <?= $totalAuditPages ?></span>
            <div class="flex gap-1.5">
                <?php if ($auditPage > 1): ?>
                    <a href="?page=audit&p=<?= $auditPage - 1 ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 shadow-sm transition-colors">Previous</a>
                <?php endif; ?>
                <?php if ($auditPage < $totalAuditPages): ?>
                    <a href="?page=audit&p=<?= $auditPage + 1 ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 shadow-sm transition-colors">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <div class="h-8"></div>
<?php elseif ($section === 'chatbot'): ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Chatbots & Automation</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Create keyword rules to automatically reply to incoming messages.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <?php foreach ($chatbotRules as $rule): ?>
                <div class="bg-white rounded-xl border <?= $rule['status'] === 'active' ? 'border-slate-200' : 'border-slate-100 opacity-70' ?> p-5 shadow-saas transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <?php if ($rule['match_type'] === 'catch_all'): ?>
                                <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Catch All</span>
                                <h3 class="font-bold text-slate-900 text-[15px]">Default Fallback Reply</h3>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider"><?= e(str_replace('_', ' ', $rule['match_type'])) ?></span>
                                <h3 class="font-bold text-slate-900 text-[15px]">"<?= e($rule['keyword']) ?>"</h3>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <form method="post" action="<?= e(base_url('index.php?page=chatbot-status')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $rule['id'] ?>">
                                <input type="hidden" name="status" value="<?= $rule['status'] === 'active' ? 'disabled' : 'active' ?>">
                                <button type="submit" class="text-[12px] font-semibold px-2.5 py-1 rounded border <?= $rule['status'] === 'active' ? 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100' ?> transition-colors shadow-sm">
                                    <?= $rule['status'] === 'active' ? 'Pause' : 'Resume' ?>
                                </button>
                            </form>
                            <form method="post" action="<?= e(base_url('index.php?page=chatbot-delete')) ?>" onsubmit="return confirm('Delete this chatbot rule?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $rule['id'] ?>">
                                <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 relative">
                        <div class="absolute -top-3 left-4 bg-slate-50 px-1 text-[10px] font-bold text-slate-400">BOT REPLY</div>
                        <p class="text-[13px] text-slate-700 whitespace-pre-wrap font-mono mt-1"><?= e($rule['reply_text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$chatbotRules): ?>
                <div class="bg-white border border-slate-200 rounded-xl p-10 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-slate-900 mb-1">No Chatbot Rules Yet</h3>
                    <p class="text-[13px] text-slate-500">Create your first keyword rule to automate replies.</p>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-saas sticky top-6">
                <h2 class="text-[14px] font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create New Rule
                </h2>
                <form method="post" action="<?= e(base_url('index.php?page=chatbot-create')) ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Match Type</label>
                        <select name="match_type" onchange="document.getElementById('keyword_container').style.display = this.value === 'catch_all' ? 'none' : 'block'" class="w-full text-[13px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_med focus:border-brand-wa_med transition-shadow">
                            <option value="exact">Exact Match (e.g. "pricing")</option>
                            <option value="contains">Contains (e.g. "what is your pricing")</option>
                            <option value="starts_with">Starts With (e.g. "price ...")</option>
                            <option value="catch_all">Catch All (Default Reply)</option>
                        </select>
                    </div>

                    <div id="keyword_container">
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Keyword Trigger</label>
                        <input type="text" name="keyword" placeholder="e.g. hello" class="w-full text-[13px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_med focus:border-brand-wa_med transition-shadow">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Bot Reply Text</label>
                        <textarea name="reply_text" rows="4" required placeholder="Type the automated response here..." class="w-full text-[13px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-wa_med focus:border-brand-wa_med transition-shadow resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-brand-wa_med hover:bg-brand-wa_dark text-white text-[13px] font-bold py-2.5 px-4 rounded-lg transition-colors shadow-sm">
                        Create Automation
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="h-8"></div>

<?php endif; ?>
</section>
<script src="<?= e(base_url('assets/dashboard.js')) ?>?v=<?= time() ?>"></script>


