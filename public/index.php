<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login', 'register', 'docs'];
$dashboardPages = ['dashboard', 'device', 'api-keys', 'queue', 'contacts', 'webhooks', 'campaigns'];

if ($page === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    logout_user();
    redirect_to('index.php?page=login');
}

if ($page === 'credentials' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? 'API key'));
    $_SESSION['new_api_credentials'] = create_api_key((int) $user['id'], $name);
    flash('success', 'New API key and secret generated. Copy them now; the secret is shown once.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    update_api_key_status((int) $user['id'], (int) ($_POST['id'] ?? 0), (string) ($_POST['status'] ?? 'disabled'));
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-limits' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    update_api_key_limits((int) $user['id'], (int) ($_POST['id'] ?? 0), $_POST);
    flash('success', 'API key limits updated.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    delete_api_key((int) $user['id'], (int) ($_POST['id'] ?? 0));
    flash('success', 'API key deleted.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'message-retry' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    retry_message((int) $user['id'], (int) ($_POST['id'] ?? 0));
    redirect_to('index.php?page=queue');
}

if ($page === 'message-cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    cancel_message((int) $user['id'], (int) ($_POST['id'] ?? 0));
    redirect_to('index.php?page=queue');
}

if ($page === 'contact-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $stmt = Database::pdo()->prepare(
        'INSERT INTO contacts (user_id, name, phone, opt_in, notes) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), opt_in = VALUES(opt_in), notes = VALUES(notes)'
    );
    $stmt->execute([
        (int) $user['id'],
        trim((string) ($_POST['name'] ?? 'Contact')),
        trim((string) ($_POST['phone'] ?? '')),
        isset($_POST['opt_in']) ? 1 : 0,
        trim((string) ($_POST['notes'] ?? '')) ?: null,
    ]);
    flash('success', 'Contact saved.');
    redirect_to('index.php?page=contacts');
}

if ($page === 'contact-import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();

    if (!isset($_FILES['contacts_csv']) || $_FILES['contacts_csv']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Upload a valid CSV file.');
        redirect_to('index.php?page=contacts');
    }

    $handle = fopen($_FILES['contacts_csv']['tmp_name'], 'r');
    if (!$handle) {
        flash('error', 'Could not read the CSV file.');
        redirect_to('index.php?page=contacts');
    }

    $imported = 0;
    $stmt = Database::pdo()->prepare(
        'INSERT INTO contacts (user_id, name, phone, opt_in, notes) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), opt_in = VALUES(opt_in), notes = VALUES(notes)'
    );

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 2) {
            continue;
        }

        $name = trim((string) $row[0]);
        $phone = trim((string) $row[1]);
        if ($name === '' || $phone === '' || strcasecmp($name, 'name') === 0) {
            continue;
        }

        $stmt->execute([
            (int) $user['id'],
            $name,
            $phone,
            isset($row[2]) ? ((int) $row[2] ? 1 : 0) : 1,
            trim((string) ($row[3] ?? '')) ?: null,
        ]);
        $imported++;
    }
    fclose($handle);

    flash('success', "Imported {$imported} contacts.");
    redirect_to('index.php?page=contacts');
}

if ($page === 'contacts-export-vcf') {
    $user = require_auth();
    $stmt = Database::pdo()->prepare('SELECT name, phone FROM contacts WHERE user_id = ? ORDER BY name ASC');
    $stmt->execute([(int) $user['id']]);

    header('Content-Type: text/vcard; charset=utf-8');
    header('Content-Disposition: attachment; filename="whatsapp-api-contacts.vcf"');
    foreach ($stmt->fetchAll() as $contact) {
        echo "BEGIN:VCARD\r\n";
        echo "VERSION:3.0\r\n";
        echo 'FN:' . str_replace(["\r", "\n"], '', (string) $contact['name']) . "\r\n";
        echo 'TEL;TYPE=CELL:' . preg_replace('/[^\d+]/', '', (string) $contact['phone']) . "\r\n";
        echo "END:VCARD\r\n";
    }
    exit;
}

if ($page === 'contact-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $stmt = Database::pdo()->prepare('DELETE FROM contacts WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0), (int) $user['id']]);
    redirect_to('index.php?page=contacts');
}

if ($page === 'campaign-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? 'Campaign'));
    $template = trim((string) ($_POST['message_template'] ?? ''));

    if ($template === '') {
        flash('error', 'Campaign message cannot be empty.');
        redirect_to('index.php?page=campaigns');
    }

    $campaignId = create_campaign((int) $user['id'], $name, $template);
    flash('success', "Campaign #{$campaignId} queued for opted-in contacts.");
    redirect_to('index.php?page=campaigns');
}

if ($page === 'webhook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    try {
        $targetUrl = validate_webhook_url($_POST['target_url'] ?? null);
    } catch (InvalidArgumentException $exception) {
        flash('error', $exception->getMessage());
        redirect_to('index.php?page=webhooks');
    }

    $secret = trim((string) ($_POST['secret'] ?? '')) ?: null;
    if (isset($_POST['rotate_secret']) || (isset($_POST['enabled']) && $targetUrl && !$secret)) {
        $secret = bin2hex(random_bytes(32));
        $_SESSION['new_webhook_secret'] = $secret;
    }

    $stmt = Database::pdo()->prepare(
        'INSERT INTO webhooks (user_id, target_url, secret, enabled) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE target_url = VALUES(target_url), secret = VALUES(secret), enabled = VALUES(enabled)'
    );
    $stmt->execute([
        (int) $user['id'],
        $targetUrl,
        $secret,
        isset($_POST['enabled']) && $targetUrl ? 1 : 0,
    ]);
    flash('success', 'Webhook settings saved.');
    redirect_to('index.php?page=webhooks');
}

if (!in_array($page, $publicPages, true)) {
    $user = require_auth();
}

$view = match (true) {
    $page === 'login' => dirname(__DIR__) . '/src/pages/login.php',
    $page === 'register' => dirname(__DIR__) . '/src/pages/register.php',
    $page === 'docs' => dirname(__DIR__) . '/src/pages/docs.php',
    in_array($page, $dashboardPages, true) => dirname(__DIR__) . '/src/pages/dashboard.php',
    default => dirname(__DIR__) . '/src/pages/dashboard.php',
};

ob_start();
require $view;
$content = ob_get_clean();

require dirname(__DIR__) . '/src/pages/layout.php';
