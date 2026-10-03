

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    api_key_hash CHAR(64) DEFAULT NULL,
    api_key_prefix VARCHAR(32) DEFAULT NULL,
    api_secret_hash VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    ip_address VARCHAR(64) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_email_ip_time (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    status ENUM('idle','connecting','qr','connected','disconnected','error') NOT NULL DEFAULT 'idle',
    sleeping TINYINT(1) NOT NULL DEFAULT 0,
    phone VARCHAR(80) DEFAULT NULL,
    push_name VARCHAR(120) DEFAULT NULL,
    qr_updated_at TIMESTAMP NULL DEFAULT NULL,
    connected_at TIMESTAMP NULL DEFAULT NULL,
    disconnected_at TIMESTAMP NULL DEFAULT NULL,
    last_error TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_whatsapp_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    recipient VARCHAR(80) NOT NULL,
    message_preview VARCHAR(255) NOT NULL,
    status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
    provider_response JSON DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_message_logs_user_created (user_id, created_at)
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
    INDEX idx_webhook_deliveries_user_created (user_id, created_at),
    INDEX idx_webhook_deliveries_message (message_id)
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
