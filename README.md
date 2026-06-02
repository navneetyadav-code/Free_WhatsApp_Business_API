# WhatsApp Web Automation - Free WhatsApp API Dashboard

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4.svg)](https://www.php.net/)
[![Node.js](https://img.shields.io/badge/Node.js-18%2B-339933.svg)](https://nodejs.org/)
[![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1.svg)](https://www.mysql.com/)
[![Baileys](https://img.shields.io/badge/Baileys-WhatsApp%20Web-25D366.svg)](https://github.com/WhiskeySockets/Baileys)

Free open-source WhatsApp Web automation API dashboard built with PHP, MySQL, Node.js, and Baileys. This repository helps developers create a self-hosted WhatsApp API system with linked-device QR login, API keys, queued message sending, rate limits, contact management, campaign tools, delivery logs, and webhook callbacks.

It is useful for developers searching for a free WhatsApp API, WhatsApp Web automation API, WhatsApp API PHP project, Baileys WhatsApp API starter, open-source WhatsApp API dashboard, WhatsApp message API, or WhatsApp webhook API.

Important: this project uses WhatsApp Web linked-device automation through Baileys. It is not Meta's official WhatsApp API. For large commercial production use, compare this project with Meta's official WhatsApp Business Cloud API.

## Repository Metadata

Recommended GitHub description:

```text
Free open-source WhatsApp Web automation API with PHP, MySQL, Node.js, Baileys, message queue, contacts, webhooks, and dashboard.
```

Recommended GitHub topics:

```text
whatsapp-api, whatsapp-web, whatsapp-automation, whatsapp-web-automation, baileys, baileys-whatsapp, php, mysql, nodejs, webhooks, message-queue, open-source
```

## Highlights

- Free WhatsApp API endpoint for queued message sending.
- WhatsApp Web automation through Baileys linked-device sessions.
- PHP and MySQL dashboard for API keys, contacts, campaigns, queue, and logs.
- Node.js WhatsApp worker for background message processing.
- Webhook callbacks for sent and failed message events.
- API key authentication, per-key rate limits, and optional IP allowlists.
- Login throttling, webhook URL validation, and safer session storage outside the web root.
- Local XAMPP setup for fast testing and open-source development.

## Use Cases

- Build a local WhatsApp Web automation dashboard.
- Prototype a WhatsApp message API for internal tools.
- Test a Baileys WhatsApp API workflow with a queue and webhooks.
- Manage contacts, opted-in recipients, and simple campaigns.
- Learn how PHP, MySQL, and Node.js can work together for background message delivery.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Dashboard | PHP, HTML, CSS, JavaScript |
| Database | MySQL |
| Worker | Node.js, Express |
| WhatsApp Web library | Baileys |
| Local environment | XAMPP |

## 1. Database

Start MySQL in XAMPP, then run:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/whatsapp-api/database.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/whatsapp-api/upgrade.sql"
```

The default local config uses MySQL user `root` with no password. For production, create a least-privilege database user instead:

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

## Security Notes

- `.htaccess` denies direct access to `config`, `src`, `worker`, `.env`, SQL, log, and package files.
- `public/test-page.php` is available only to authenticated local development requests and is disabled when `ENABLE_TEST_PAGE=false`.
- Webhooks must use HTTPS and cannot resolve to localhost, private, or reserved IP addresses.
- Login attempts are throttled by email and IP. Re-run `upgrade.sql` after pulling these changes.
- Webhook delivery attempts are recorded in `webhook_deliveries`.

## FAQ

### Is this a free WhatsApp API?

It is a free open-source WhatsApp Web automation API starter. It runs locally or on your own server and sends messages through a linked WhatsApp Web device using Baileys.

### Is this the official WhatsApp Business API?

No. This is a WhatsApp Web automation project. The official production API from Meta is WhatsApp Business Cloud API.

### Can I use this as a PHP WhatsApp API?

Yes. The dashboard and public send endpoint are PHP-based, while the background sender is a Node.js worker.

### Does it support webhooks?

Yes. The worker can send webhook callbacks for sent and failed message events.

### Does it include rate limits?

Yes. API keys support per-minute, per-hour, and per-day limits, plus optional IP allowlists.
