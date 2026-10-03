USE whatsapp_api;

ALTER TABLE whatsapp_sessions
    ADD COLUMN IF NOT EXISTS sleeping TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    ip_address VARCHAR(64) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_email_ip_time (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL DEFAULT 'Default key',
    key_hash CHAR(64) NOT NULL UNIQUE,
    key_prefix VARCHAR(32) NOT NULL,
    secret_hash VARCHAR(255) NOT NULL,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    ip_allowlist TEXT DEFAULT NULL,
    rate_per_minute INT UNSIGNED NOT NULL DEFAULT 20,
    rate_per_hour INT UNSIGNED NOT NULL DEFAULT 300,
    rate_per_day INT UNSIGNED NOT NULL DEFAULT 1000,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_keys_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_keys_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    api_key_id BIGINT UNSIGNED DEFAULT NULL,
    recipient VARCHAR(80) NOT NULL,
    normalized_recipient VARCHAR(80) DEFAULT NULL,
    message_type ENUM('text','image','document','video') NOT NULL DEFAULT 'text',
    body TEXT NOT NULL,
    payload JSON DEFAULT NULL,
    status ENUM('queued','processing','sent','failed','cancelled') NOT NULL DEFAULT 'queued',
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
    available_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_at TIMESTAMP NULL DEFAULT NULL,
    locked_by VARCHAR(80) DEFAULT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    provider_message_id VARCHAR(120) DEFAULT NULL,
    provider_response JSON DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_queue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_queue_api_key FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL,
    INDEX idx_message_queue_worker (status, available_at, locked_at),
    INDEX idx_message_queue_user_created (user_id, created_at),
    INDEX idx_message_queue_api_key_created (api_key_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_request_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    api_key_id BIGINT UNSIGNED DEFAULT NULL,
    endpoint VARCHAR(120) NOT NULL,
    ip_address VARCHAR(64) NOT NULL,
    status_code INT UNSIGNED NOT NULL,
    request_id VARCHAR(64) NOT NULL,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_request_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_api_request_logs_api_key FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL,
    INDEX idx_api_request_logs_user_created (user_id, created_at),
    INDEX idx_api_request_logs_key_created (api_key_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(80) NOT NULL,
    normalized_phone VARCHAR(80) DEFAULT NULL,
    opt_in TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_contacts_user_phone (user_id, phone),
    INDEX idx_contacts_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    target_url VARCHAR(500) DEFAULT NULL,
    secret VARCHAR(120) DEFAULT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_webhooks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    webhook_id BIGINT UNSIGNED DEFAULT NULL,
    message_id BIGINT UNSIGNED DEFAULT NULL,
    event VARCHAR(80) NOT NULL,
    target_url VARCHAR(500) NOT NULL,
    status_code INT UNSIGNED DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    attempt INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_webhook_deliveries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_webhook_deliveries_webhook FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE SET NULL,
    INDEX idx_webhook_deliveries_user_created (user_id, created_at),
    INDEX idx_webhook_deliveries_message (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    message_template TEXT NOT NULL,
    target_count INT UNSIGNED NOT NULL DEFAULT 0,
    queued_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('queued','completed','cancelled') NOT NULL DEFAULT 'queued',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_campaigns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_campaigns_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS birthday_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    recipient_source ENUM('contact','number') NOT NULL DEFAULT 'contact',
    contact_id BIGINT UNSIGNED DEFAULT NULL,
    recipient_name VARCHAR(160) NOT NULL,
    recipient_phone VARCHAR(80) NOT NULL,
    timezone VARCHAR(80) NOT NULL DEFAULT 'Asia/Kolkata',
    birthday_month TINYINT UNSIGNED NOT NULL,
    birthday_day TINYINT UNSIGNED NOT NULL,
    send_time TIME NOT NULL DEFAULT '00:00:00',
    final_message_template TEXT NOT NULL,
    emoji_pool JSON DEFAULT NULL,
    countdown_enabled TINYINT(1) NOT NULL DEFAULT 0,
    countdown_days_start INT UNSIGNED NOT NULL DEFAULT 0,
    day_message_template TEXT DEFAULT NULL,
    countdown_hours_start INT UNSIGNED NOT NULL DEFAULT 0,
    hour_message_template TEXT DEFAULT NULL,
    countdown_minutes_start INT UNSIGNED NOT NULL DEFAULT 0,
    minute_interval INT UNSIGNED NOT NULL DEFAULT 1,
    minute_message_template TEXT DEFAULT NULL,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_birthday_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_birthday_tasks_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    INDEX idx_birthday_tasks_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS birthday_task_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    birthday_task_id BIGINT UNSIGNED NOT NULL,
    occurrence_year INT UNSIGNED NOT NULL,
    event_key VARCHAR(80) NOT NULL,
    scheduled_for DATETIME NOT NULL,
    queued_message_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_birthday_task_logs_task FOREIGN KEY (birthday_task_id) REFERENCES birthday_tasks(id) ON DELETE CASCADE,
    UNIQUE KEY uq_birthday_task_event (birthday_task_id, occurrence_year, event_key),
    INDEX idx_birthday_task_logs_schedule (scheduled_for)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO api_keys (user_id, name, key_hash, key_prefix, secret_hash, status)
SELECT id, 'Default key', api_key_hash, api_key_prefix, api_secret_hash, 'active'
FROM users
WHERE api_key_hash IS NOT NULL
  AND api_secret_hash IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM api_keys WHERE api_keys.user_id = users.id
  );

INSERT INTO webhooks (user_id)
SELECT id FROM users
WHERE NOT EXISTS (
    SELECT 1 FROM webhooks WHERE webhooks.user_id = users.id
);
