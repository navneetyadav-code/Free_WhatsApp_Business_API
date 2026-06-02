# WhatsApp Web Automation - Free WhatsApp API Dashboard

Free open-source WhatsApp Web automation API dashboard built with PHP, MySQL, Node.js, and Baileys. This repository helps developers build a local WhatsApp API system with linked-device QR login, API keys, queued message sending, rate limits, contacts, message logs, campaign tools, and webhook callbacks.

Use this repo if you are looking for a free WhatsApp API, WhatsApp Web automation, WhatsApp API PHP project, Baileys WhatsApp API starter, open-source WhatsApp API dashboard, WhatsApp message API, WhatsApp webhook API, or a self-hosted WhatsApp automation panel.

Important: this uses WhatsApp Web linked-device automation through Baileys. It is not Meta's official WhatsApp API. For public or commercial production use, Meta's WhatsApp Business Cloud API is safer.

## GitHub Repository Description

Free open-source WhatsApp Web automation API with PHP, MySQL, Node.js, Baileys, message queue, contacts, webhooks, and dashboard.

## GitHub Topics

`whatsapp-api` `whatsapp-web` `whatsapp-automation` `whatsapp-web-automation` `baileys` `baileys-whatsapp` `php` `mysql` `nodejs` `webhooks` `message-queue` `open-source`

## Search Keywords

free WhatsApp API, WhatsApp Web automation API, WhatsApp API PHP, Baileys WhatsApp API, open source WhatsApp API, WhatsApp message API, WhatsApp webhook API, WhatsApp API dashboard, WhatsApp linked device API, Node.js WhatsApp worker, PHP MySQL WhatsApp API

## Core Features

- Free WhatsApp API endpoint for queued message sending.
- WhatsApp Web automation through Baileys linked-device sessions.
- PHP and MySQL dashboard for API keys, contacts, campaigns, queue, and logs.
- Node.js WhatsApp worker for background message processing.
- Webhook callbacks for sent and failed message events.
- API key authentication, rate limits, IP allowlists, login throttling, and safe session storage.
- Local XAMPP setup for fast testing and open-source development.

## 1. Database

Start MySQL in XAMPP, then run:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/whatsapp-api/database.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/whatsapp-api/upgrade.sql"
```

The default config uses MySQL user `root` with no password. Change `config/app.php` and the worker environment if your XAMPP MySQL credentials are different.

For production, create a least-privilege database user instead of using `root`:

```sql
CREATE USER 'whatsapp_api_app'@'127.0.0.1' IDENTIFIED BY 'change-this-db-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON whatsapp_api.* TO 'whatsapp_api_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

## 2. Environment

Copy `.env.example` to `.env`, then set strong values before exposing the app:

```powershell
Copy-Item .env.example .env
```

Important production values:

```text
APP_ENV=production
APP_SECURE_COOKIES=true
ENABLE_TEST_PAGE=false
DB_USER=whatsapp_api_app
DB_PASSWORD=change-this-db-password
WORKER_TOKEN=use-a-long-random-token
WA_SESSION_DIR=C:\xampp\whatsapp-api-data\sessions
```

The worker refuses to start in production if `WORKER_TOKEN` is still the default. WhatsApp linked-device auth files should stay outside `C:\xampp\htdocs`.

## 3. Worker

```powershell
cd C:\xampp\htdocs\whatsapp-api\worker
npm install
npm start
```

Useful worker environment variables:

```powershell
$env:WORKER_TOKEN="change-this-worker-token-before-production"
$env:WA_SESSION_DIR="C:\xampp\whatsapp-api-data\sessions"
$env:DEFAULT_COUNTRY_CODE="91"
$env:SEND_DELAY_MS="2500"
$env:QUEUE_POLL_MS="3000"
$env:DB_HOST="127.0.0.1"
$env:DB_USER="root"
$env:DB_PASSWORD=""
$env:DB_NAME="whatsapp_api"
npm start
```

For production-style hosting, run the worker with `pm2` so it restarts if it crashes.

## 4. Dashboard

Start Apache and MySQL in XAMPP, then open:

```text
http://localhost/whatsapp-api/public/index.php
```

Register, copy the API key and secret, click Connect, and scan the QR code from WhatsApp > Linked devices.

## 5. Send API

The public API queues messages. The worker sends them in the background.

```powershell
Invoke-WebRequest -Uri "http://localhost/whatsapp-api/public/api/send.php" `
  -Method Post `
  -Headers @{
    "X-API-Key" = "your_api_key"
    "X-API-Secret" = "your_api_secret"
  } `
  -ContentType "application/json" `
  -Body '{"to":"9507286092","message":"Hello from the API"}'
```

Response:

```json
{
  "ok": true,
  "status": "queued",
  "message_id": 123,
  "request_id": "..."
}
```

Use full international numbers, or 10-digit Indian numbers. By default, 10-digit Indian numbers are sent as `91xxxxxxxxxx`.

## 6. Security Notes

- `.htaccess` denies direct access to `config`, `src`, `worker`, `.env`, SQL, log, and package files.
- `public/test-page.php` is available only to authenticated local development requests and is disabled when `ENABLE_TEST_PAGE=false`.
- Webhooks must use HTTPS and cannot resolve to localhost, private, or reserved IP addresses.
- Login attempts are throttled by email and IP. Re-run `upgrade.sql` after pulling these changes.
- Webhook delivery attempts are recorded in `webhook_deliveries`.

## 7. Features Added

- Multiple API keys per user.
- Enable, disable, delete API keys.
- Per-key rate limits by minute, hour, and day.
- Optional IP allowlist per API key.
- Message queue with `queued`, `processing`, `sent`, `failed`, and `cancelled` states.
- Worker retries failed queue items.
- WhatsApp number validation before sending.
- Dashboard quick queue test.
- Contacts storage with opt-in flag.
- Webhook callback on sent/failed messages.
- API request audit logs.
