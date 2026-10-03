<?php

declare(strict_types=1);
if (function_exists('opcache_reset')) { opcache_reset(); }

require_once dirname(__DIR__) . '/src/bootstrap.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login', 'register', 'docs', 'forgot-password', 'google-auth'];
$dashboardPages = ['dashboard', 'device', 'api-keys', 'queue', 'contacts', 'webhooks', 'campaigns', 'audit', 'chatbot'];

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

if ($page === 'message-send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();

    $type = $_POST['recipient_mode'] ?? 'number';
    $message = trim((string) ($_POST['message'] ?? ''));
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($message === '') {
        if ($isAjax) {
            json_response(['ok' => false, 'error' => 'Message is required.'], 400);
        }
        flash('error', 'Message is required.');
        redirect_to('index.php?page=message');
    }

    // Media upload logic
    $payload = [];
    if (isset($_FILES['attachment'])) {
        if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid('media_') . '.' . $ext;
            $target = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                $payload['attachment'] = [
                    'path' => $target,
                    'name' => $_FILES['attachment']['name'],
                    'type' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : (in_array($ext, ['mp4', 'mov']) ? 'video' : 'document'),
                    'mime' => $_FILES['attachment']['type']
                ];
            } else {
                if ($isAjax) { json_response(['ok' => false, 'error' => 'Failed to save uploaded file to disk.'], 500); }
                flash('error', 'Failed to save uploaded file.');
                redirect_to('index.php?page=message');
            }
        } elseif ($_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini (Default is usually 2MB).',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            ];
            $errMsg = $uploadErrors[$_FILES['attachment']['error']] ?? 'Unknown upload error.';
            if ($isAjax) { json_response(['ok' => false, 'error' => $errMsg], 400); }
            flash('error', $errMsg);
            redirect_to('index.php?page=message');
        }
    }

    if (!empty($_POST['humanize'])) {
        $payload['humanize'] = true;
    }

    try {
        $queuedCount = 0;
        if ($type === 'number') {
            $phone = trim((string) ($_POST['manual_number'] ?? ''));
            if (!$phone) throw new Exception('Phone number required');
            enqueue_message((int) $user['id'], null, $phone, $message, $payload);
            $queuedCount = 1;
        } elseif ($type === 'contact') {
            $contactId = (int) ($_POST['contact_id'] ?? 0);
            $stmt = Database::pdo()->prepare('SELECT * FROM contacts WHERE id = ? AND user_id = ?');
            $stmt->execute([$contactId, $user['id']]);
            $contact = $stmt->fetch();
            if (!$contact) throw new Exception('Contact not found');
            
            $formattedMsg = apply_contact_template($message, $contact);
            enqueue_message((int) $user['id'], null, $contact['phone'], $formattedMsg, $payload);
            $queuedCount = 1;
        } elseif ($type === 'campaign') {
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $contacts = campaign_contacts((int) $user['id'], $campaignId);
            foreach ($contacts as $contact) {
                $formattedMsg = apply_contact_template($message, $contact);
                enqueue_message((int) $user['id'], null, $contact['phone'], $formattedMsg, $payload);
                $queuedCount++;
            }
        }
        
        if ($isAjax) {
            json_response(['ok' => true, 'message' => "Successfully queued $queuedCount message(s)."]);
        }
        flash('success', "Successfully queued $queuedCount message(s).");
        redirect_to('index.php?page=queue');
        
    } catch (Exception $e) {
        if ($isAjax) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 400);
        }
        flash('error', $e->getMessage());
        redirect_to('index.php?page=message');
    }
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

if ($page === 'chatbot-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    
    $keyword = trim((string) ($_POST['keyword'] ?? ''));
    $matchType = $_POST['match_type'] ?? 'exact';
    $replyText = trim((string) ($_POST['reply_text'] ?? ''));
    
    if (!in_array($matchType, ['exact', 'contains', 'starts_with', 'catch_all'])) {
        $matchType = 'exact';
    }
    
    if ($matchType !== 'catch_all' && $keyword === '') {
        flash('error', 'Keyword is required unless match type is catch_all.');
        redirect_to('index.php?page=chatbot');
    }
    if ($replyText === '') {
        flash('error', 'Reply text cannot be empty.');
        redirect_to('index.php?page=chatbot');
    }

    $stmt = Database::pdo()->prepare(
        'INSERT INTO chatbot_rules (user_id, keyword, match_type, reply_text) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([(int) $user['id'], $keyword, $matchType, $replyText]);
    
    flash('success', 'Chatbot automation rule created successfully.');
    redirect_to('index.php?page=chatbot');
}

if ($page === 'chatbot-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? 'disabled') === 'active' ? 'active' : 'disabled';
    
    $stmt = Database::pdo()->prepare('UPDATE chatbot_rules SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $id, (int) $user['id']]);
    redirect_to('index.php?page=chatbot');
}

if ($page === 'chatbot-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    
    $stmt = Database::pdo()->prepare('DELETE FROM chatbot_rules WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, (int) $user['id']]);
    
    flash('success', 'Chatbot rule deleted.');
    redirect_to('index.php?page=chatbot');
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

