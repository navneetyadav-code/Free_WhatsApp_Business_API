<?php
$envPath = __DIR__ . '/.env';
$isInstalled = file_exists($envPath);

$logs = [];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isInstalled) {
    $dbHost = $_POST['db_host'] ?? '127.0.0.1';
    $dbName = $_POST['db_name'] ?? 'whatsapp_api';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    
    try {
        $logs[] = "Attempting to connect to database server at $dbHost...";
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $logs[] = "Connected successfully!";

        // 1. Create DB and Import Schema
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");
        $logs[] = "Database `$dbName` selected.";

        if (file_exists('database.sql')) {
            $sql = file_get_contents('database.sql');
            $pdo->exec($sql);
            $logs[] = "Database schema imported successfully from database.sql.";
            
            // Clear out demo data and insert new admin
            $adminName = $_POST['admin_name'] ?? 'Admin';
            $adminEmail = $_POST['admin_email'] ?? 'admin@localhost.com';
            $adminPass = $_POST['admin_pass'] ?? 'password';
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $tables = ['users', 'api_keys', 'api_request_logs', 'campaigns', 'chatbot_rules', 'contacts', 'message_logs', 'message_queue', 'webhooks', 'whatsapp_sessions'];
            foreach($tables as $table) {
                try {
                    $pdo->exec("TRUNCATE TABLE `$table`");
                } catch(Exception $e) {}
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$adminName, $adminEmail, password_hash($adminPass, PASSWORD_DEFAULT)]);
            $logs[] = "Admin user ($adminEmail) created successfully and demo data cleared.";
            
        } else {
            $logs[] = "Warning: database.sql not found! Schema was not imported.";
        }

        // 2. Generate .env file
        $workerToken = bin2hex(random_bytes(16));
        $envContent = <<<ENV
APP_ENV=local
APP_NAME="WhatsApp API Hub"
APP_PUBLIC_URL="http://localhost/whatsapp-api/public"
APP_BASE_PATH="/whatsapp-api/public"

DB_DSN="mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4"
DB_USER="{$dbUser}"
DB_PASSWORD="{$dbPass}"

WORKER_URL="http://127.0.0.1:3107"
WORKER_TOKEN="{$workerToken}"
ENV;
        if (file_put_contents($envPath, $envContent)) {
            $logs[] = "Configuration file (.env) generated successfully.";
        } else {
            throw new Exception("Could not write to .env file. Check folder permissions.");
        }

        // 3. NPM Install
        $workerDir = __DIR__ . DIRECTORY_SEPARATOR . 'worker';
        if (is_dir($workerDir)) {
            $logs[] = "Running npm install in worker directory... (This might take a minute)";
            $npmCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'npm.cmd' : 'npm';
            $output = shell_exec("cd " . escapeshellarg($workerDir) . " && $npmCmd install 2>&1");
            $logs[] = "NPM Output:\n" . htmlspecialchars($output ?: 'No output or command failed.');
        } else {
            $logs[] = "Warning: worker directory not found. Skipping npm install.";
        }

        $success = true;
        $isInstalled = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - WhatsApp Business API</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #334155; padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
        h1 { margin-top: 0; color: #0f172a; font-size: 24px; display: flex; align-items: center; gap: 10px; }
        h1 svg { width: 28px; height: 28px; color: #10b981; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 0.5rem; color: #475569; }
        input[type="text"], input[type="password"] { width: 100%; box-sizing: border-box; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 14px; }
        button { background: #10b981; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 15px; width: 100%; transition: background 0.2s; }
        button:hover { background: #059669; }
        .logs { margin-top: 1.5rem; padding: 1rem; background: #1e293b; color: #a5b4fc; border-radius: 6px; font-family: monospace; font-size: 13px; white-space: pre-wrap; max-height: 250px; overflow-y: auto; }
        .error-msg { padding: 1rem; background: #fef2f2; border: 1px solid #f87171; color: #991b1b; border-radius: 6px; margin-bottom: 1.5rem; font-size: 14px; }
        .success-box { padding: 2rem; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; border-radius: 6px; text-align: center; }
        .btn-outline { display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; border: 2px solid #10b981; color: #10b981; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .btn-outline:hover { background: #10b981; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.12.553 4.11 1.526 5.836L.2 23.8l6.108-1.583A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm5.666 17.202c-.237.666-1.378 1.282-1.93 1.348-.515.06-1.18.15-3.667-.88-2.99-1.24-4.887-4.32-5.02-4.502-.132-.18-1.198-1.597-1.198-3.044 0-1.448.75-2.158 1.018-2.436.268-.278.583-.348.775-.348.193 0 .386.002.556.01.19.008.448-.075.7.533.268.643.916 2.238.996 2.398.08.16.132.348.026.56-.106.21-.16.34-.316.523-.16.182-.338.396-.48.542-.16.16-.33.336-.143.657.185.32 8.23 1.417 9.4 2.13.11.066.175.158.213.242.04.084.045.242-.016.425z"></path></svg>
            API Setup Wizard
        </h1>

        <?php if ($success): ?>
            <div class="success-box">
                <h2 style="margin-top: 0;">🎉 Setup Complete!</h2>
                <p>The database is ready, .env is created, and NPM packages are installed.</p>
                
                <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1.5rem 0; text-align: left; font-size: 14px; border: 1px solid #cbd5e1;">
                    <strong>Next Steps:</strong>
                    <ol style="margin-bottom: 0;">
                        <li>Double-click <code>start-worker.bat</code> in your folder to boot up the WhatsApp engine.</li>
                        <li>Click the button below to access your dashboard.</li>
                        <li>Log in using the email and password you just created!</li>
                    </ol>
                </div>
                
                <a href="public/index.php" class="btn-outline">Go to Dashboard</a>
            </div>
            
            <?php if (!empty($logs)): ?>
                <div class="logs">
                    <?= implode("\n", $logs) ?>
                </div>
            <?php endif; ?>

        <?php elseif ($isInstalled): ?>
            <div class="success-box">
                <h2>System Already Installed</h2>
                <p>A <code>.env</code> file was detected. The system is already configured.</p>
                <p style="font-size: 13px; color: #64748b;">If you need to reinstall, delete the .env file first.</p>
                <a href="public/index.php" class="btn-outline">Go to Dashboard</a>
            </div>
        <?php else: ?>
            <p style="color: #64748b; font-size: 15px; margin-bottom: 2rem;">Welcome! Let's get your Free WhatsApp Business API configured in seconds.</p>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 16px; color: #334155;">Database Configuration</h3>
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="127.0.0.1" required>
                </div>
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="whatsapp_api" required>
                </div>
                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass" placeholder="(Leave blank if default XAMPP)">
                </div>

                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 16px; color: #334155;">Create Admin Account</h3>
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="admin_name" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email Address (Login ID)</label>
                    <input type="text" name="admin_email" placeholder="admin@example.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="admin_pass" placeholder="••••••••" required>
                </div>
                
                <button type="submit" id="submitBtn" onclick="this.innerHTML='Installing... Please wait (Installing NPM might take 1 min)'; this.style.opacity='0.8';">Complete Setup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
