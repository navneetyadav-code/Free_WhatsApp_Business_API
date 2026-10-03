import express from 'express';
import QRCode from 'qrcode';
import pino from 'pino';
import mysql from 'mysql2/promise';
import crypto from 'crypto';
import dns from 'dns/promises';
import net from 'net';
import { existsSync, mkdirSync, readFileSync, statSync } from 'fs';
import { rm } from 'fs/promises';
import { dirname, join, resolve } from 'path';
import { fileURLToPath } from 'url';
import makeWASocket, {
  DisconnectReason,
  fetchLatestBaileysVersion,
  useMultiFileAuthState
} from '@whiskeysockets/baileys';

const __dirname = dirname(fileURLToPath(import.meta.url));
loadEnv(resolve(__dirname, '..', '.env'));

const PORT = Number(process.env.PORT || 3107);
const WORKER_TOKEN = process.env.WORKER_TOKEN || 'change-this-worker-token-before-production';
const APP_ENV = process.env.APP_ENV || 'local';
const DEFAULT_COUNTRY_CODE = String(process.env.DEFAULT_COUNTRY_CODE || '91').replace(/\D/g, '');
const WORKER_ID = `worker-${process.pid}-${Date.now()}`;
const SEND_DELAY_MS = Number(process.env.SEND_DELAY_MS || 2500);
const QUEUE_POLL_MS = Number(process.env.QUEUE_POLL_MS || 3000);
const SESSION_DIR = resolve(process.env.WA_SESSION_DIR || join(__dirname, '..', '..', '..', 'whatsapp-api-data', 'sessions'));
const sessions = new Map();
let supportsSleepingColumn = true;

function isMissingSleepingColumnError(error) {
  return error?.code === 'ER_BAD_FIELD_ERROR'
    && typeof error?.sqlMessage === 'string'
    && error.sqlMessage.includes('sleeping');
}
const logger = pino({ level: process.env.LOG_LEVEL || 'warn' });

if (APP_ENV === 'production' && WORKER_TOKEN === 'change-this-worker-token-before-production') {
  throw new Error('Set a strong WORKER_TOKEN before running the worker in production.');
}

mkdirSync(SESSION_DIR, { recursive: true });

const db = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'whatsapp_api',
  waitForConnections: true,
  connectionLimit: 5
});

const app = express();
app.use(express.json({ limit: '1mb' }));

function loadEnv(path) {
  if (!existsSync(path)) {
    return;
  }

  const lines = readFileSync(path, 'utf8').split(/\r?\n/);
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#') || !trimmed.includes('=')) {
      continue;
    }

    const [key, ...rest] = trimmed.split('=');
    if (!process.env[key]) {
      process.env[key] = rest.join('=').trim().replace(/^["']|["']$/g, '');
    }
  }
}

process.on('uncaughtException', (error) => {
  logger.error({ error }, 'Uncaught worker exception');
});

process.on('unhandledRejection', (error) => {
  logger.error({ error }, 'Unhandled worker rejection');
});

app.use((req, res, next) => {
  if (req.path === '/health') {
    next();
    return;
  }

  if (req.get('X-Worker-Token') !== WORKER_TOKEN) {
    res.status(401).json({ ok: false, error: 'Invalid worker token.' });
    return;
  }

  next();
});

function emptySession(userId) {
  return {
    userId,
    state: 'idle',
    sleeping: false,
    qr: null,
    hasQr: false,
    phone: null,
    pushName: null,
    error: null,
    sock: null,
    starting: null
  };
}

function publicSession(session) {
  return {
    userId: session.userId,
    state: session.state,
    sleeping: Boolean(session.sleeping),
    qr: session.qr,
    hasQr: Boolean(session.qr),
    phone: session.phone,
    pushName: session.pushName,
      profilePicture: session.profilePicture,
    error: session.error
  };
}

function zonedParts(date, timeZone) {
  const formatter = new Intl.DateTimeFormat('en-CA', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hourCycle: 'h23'
  });

  const parts = {};
  for (const part of formatter.formatToParts(date)) {
    if (part.type !== 'literal') {
      parts[part.type] = Number(part.value);
    }
  }

  return {
    year: parts.year,
    month: parts.month,
    day: parts.day,
    hour: parts.hour,
    minute: parts.minute,
    second: parts.second
  };
}

function zonedTimeToUtc({ year, month, day, hour, minute, second }, timeZone) {
  const approx = new Date(Date.UTC(year, month - 1, day, hour, minute, second));
  const observed = zonedParts(approx, timeZone);
  const observedUtc = Date.UTC(
    observed.year,
    observed.month - 1,
    observed.day,
    observed.hour,
    observed.minute,
    observed.second
  );
  const targetUtc = Date.UTC(year, month - 1, day, hour, minute, second);
  return new Date(approx.getTime() + (targetUtc - observedUtc));
}

function parseEmojiPool(rawValue) {
  if (!rawValue) {
    return [];
  }

  if (Array.isArray(rawValue)) {
    return rawValue.filter((item) => typeof item === 'string' && item.trim() !== '').map((item) => item.trim());
  }

  if (typeof rawValue === 'string') {
    try {
      const parsed = JSON.parse(rawValue);
      return parseEmojiPool(parsed);
    } catch {
      return rawValue.split(/[\r\n,]+/).map((item) => item.trim()).filter(Boolean);
    }
  }

  return [];
}

async function updateSessionRecord(userId, session) {
  try {
    if (supportsSleepingColumn) {
      await db.execute(
        `UPDATE whatsapp_sessions
         SET status = ?, sleeping = ?, phone = ?, push_name = ?,
             connected_at = IF(? = 'connected', NOW(), connected_at),
             disconnected_at = IF(? IN ('disconnected', 'error', 'idle'), NOW(), disconnected_at),
             last_error = ?
         WHERE user_id = ?`,
        [session.state, Number(Boolean(session.sleeping)), session.phone, session.pushName, session.state, session.state, session.error, userId]
      );
      return;
    }

    await db.execute(
      `UPDATE whatsapp_sessions
       SET status = ?, phone = ?, push_name = ?,
           connected_at = IF(? = 'connected', NOW(), connected_at),
           disconnected_at = IF(? IN ('disconnected', 'error', 'idle'), NOW(), disconnected_at),
           last_error = ?
       WHERE user_id = ?`,
      [session.state, session.phone, session.pushName, session.state, session.state, session.error, userId]
    );
  } catch (error) {
    if (supportsSleepingColumn && isMissingSleepingColumnError(error)) {
      supportsSleepingColumn = false;
      await updateSessionRecord(userId, session);
      return;
    }
    logger.warn({ error, userId }, 'Could not update session record');
  }
}

function getSession(userId) {
  if (!sessions.has(userId)) {
    sessions.set(userId, emptySession(userId));
  }

  return sessions.get(userId);
}

function normalizeRecipient(value) {
  const raw = String(value || '').trim();
  if (!raw) {
    return { jid: '', normalizedTo: '', isGroup: false, isLid: false };
  }

  if (raw.endsWith('@g.us')) {
    return { jid: raw, normalizedTo: raw, isGroup: true, isLid: false };
  }

  if (raw.endsWith('@lid')) {
    const normalizedTo = raw.replace('@lid', '');
    return { jid: raw, normalizedTo, isGroup: false, isLid: true };
  }

  if (raw.endsWith('@s.whatsapp.net')) {
    const normalizedTo = raw.replace('@s.whatsapp.net', '');
    return { jid: raw, normalizedTo, isGroup: false, isLid: false };
  }

  let digits = raw.replace(/[^\d]/g, '');
  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.length === 11 && digits.startsWith('0') && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits.slice(1)}`;
  }

  if (digits.length === 10 && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits}`;
  }

  if (digits.length < 8 || digits.length > 15) {
    return { jid: '', normalizedTo: digits, isGroup: false };
  }

  return { jid: `${digits}@s.whatsapp.net`, normalizedTo: digits, isGroup: false };
}

function isPrivateIp(ip) {
  if (!net.isIP(ip)) {
    return true;
  }

  if (ip === '127.0.0.1' || ip === '::1' || ip === '0.0.0.0') {
    return true;
  }

  if (ip.startsWith('10.') || ip.startsWith('192.168.')) {
    return true;
  }

  const parts = ip.split('.').map((part) => Number(part));
  if (parts.length === 4) {
    if (parts[0] === 172 && parts[1] >= 16 && parts[1] <= 31) {
      return true;
    }
    if (parts[0] === 169 && parts[1] === 254) {
      return true;
    }
  }

  const normalized = ip.toLowerCase();
  return normalized.startsWith('fc') || normalized.startsWith('fd') || normalized.startsWith('fe80:');
}

async function assertSafeWebhookUrl(targetUrl) {
  let parsed;
  try {
    parsed = new URL(targetUrl);
  } catch {
    throw new Error('Webhook URL is invalid.');
  }

  const host = parsed.hostname.toLowerCase();
  if (parsed.protocol !== 'https:') {
    throw new Error('Webhook URL must use HTTPS.');
  }

  if (!host || ['localhost', '127.0.0.1', '::1'].includes(host) || host.endsWith('.local')) {
    throw new Error('Webhook URL cannot point to localhost or private hosts.');
  }

  const addresses = net.isIP(host)
    ? [{ address: host }]
    : await dns.lookup(host, { all: true, verbatim: true });

  if (addresses.some((entry) => isPrivateIp(entry.address))) {
    throw new Error('Webhook URL cannot resolve to a private or reserved IP address.');
  }
}

async function recordWebhookDelivery(webhook, payload, status, error, attempt) {
  try {
    await db.execute(
      `INSERT INTO webhook_deliveries
       (user_id, webhook_id, message_id, event, target_url, status_code, error_message, attempt)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        webhook.user_id,
        webhook.id,
        payload.message_id || null,
        payload.event || 'message.event',
        webhook.target_url,
        status || null,
        error || null,
        attempt
      ]
    );
  } catch (deliveryError) {
    logger.warn({ error: deliveryError, userId: webhook.user_id }, 'Could not record webhook delivery');
  }
}

async function startSession(userId) {
  const session = getSession(userId);
  if (['connected', 'connecting', 'qr'].includes(session.state) || session.starting) {
    return session.starting || session;
  }

  session.state = 'connecting';
  session.sleeping = false;
  session.error = null;

  session.starting = (async () => {
    const authDir = join(SESSION_DIR, `user_${userId}`);
    const { state, saveCreds } = await useMultiFileAuthState(authDir);
    const { version } = await fetchLatestBaileysVersion();

    const sock = makeWASocket({
      auth: state,
      browser: ['Chrome', 'Windows', '10.0'],
      logger,
      printQRInTerminal: false,
      version
    });

    session.sock = sock;

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
      try {
        const { connection, lastDisconnect, qr } = update;

        if (session.sleeping) {
          session.qr = null;
          session.hasQr = false;

          if (connection === 'open' && sock) {
            session.state = 'idle';
            session.sock = null;
            sock.end?.();
            await updateSessionRecord(userId, session);
          }

          if (connection === 'close') {
            session.state = 'idle';
            session.sock = null;
            session.error = null;
            await updateSessionRecord(userId, session);
          }

          return;
        }

        if (qr) {
          session.qr = await QRCode.toDataURL(qr, { margin: 1, width: 320 });
          session.hasQr = true;
          session.state = 'qr';
        }

        if (connection === 'open') {
          session.state = 'connected';
          session.sleeping = false;
          session.qr = null;
          session.hasQr = false;
          session.error = null;
          session.phone = sock.user?.id || null;
          session.pushName = sock.user?.name || sock.user?.verifiedName || null;
          try { session.profilePicture = await sock.profilePictureUrl(sock.user.id, 'image'); } catch(e) { session.profilePicture = null; }
          await updateSessionRecord(userId, session);
        }

        if (connection === 'close') {
          const code = lastDisconnect?.error?.output?.statusCode;
          const shouldReconnect = code !== DisconnectReason.loggedOut && !session.sleeping;
          session.state = session.sleeping ? 'idle' : (shouldReconnect ? 'disconnected' : 'idle');
          session.qr = null;
          session.hasQr = false;
          session.sock = null;
          session.error = lastDisconnect?.error?.message || null;
          await updateSessionRecord(userId, session);

          if (shouldReconnect) {
            setTimeout(() => startSession(userId).catch((error) => {
              logger.error({ error, userId }, 'Reconnect failed');
            }), 2500);
          }
        }
      } catch (error) {
        session.state = 'error';
        session.error = error.message;
        await updateSessionRecord(userId, session);
        logger.error({ error, userId }, 'Connection update failed');
      }
    });

    sock.ev.on('messages.upsert', async (m) => {
      try {
        if (m.type !== 'notify') return;
        for (const msg of m.messages) {
          if (!msg.message || msg.key.fromMe || msg.key.remoteJid === 'status@broadcast') continue;
          
          const text = msg.message.conversation || msg.message.extendedTextMessage?.text || msg.message.buttonsResponseMessage?.selectedButtonId || msg.message.listResponseMessage?.title || '';
          if (!text.trim()) continue;

          const [rules] = await db.execute('SELECT * FROM chatbot_rules WHERE user_id = ? AND status = "active"', [userId]);
          if (!rules || rules.length === 0) continue;

          let matchedRule = null;
          const lowerText = text.toLowerCase().trim();

          for (const rule of rules) {
            if (rule.match_type === 'catch_all') continue;
            const lowerKeyword = rule.keyword.toLowerCase();
            if (rule.match_type === 'exact' && lowerText === lowerKeyword) { matchedRule = rule; break; }
            if (rule.match_type === 'starts_with' && lowerText.startsWith(lowerKeyword)) { matchedRule = rule; break; }
            if (rule.match_type === 'contains' && lowerText.includes(lowerKeyword)) { matchedRule = rule; break; }
          }

          if (!matchedRule) {
            matchedRule = rules.find(r => r.match_type === 'catch_all');
          }

          if (matchedRule) {
             const payload = JSON.stringify({ source: 'chatbot', rule_id: matchedRule.id, type: 'text' });
             await db.execute(
               `INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload) VALUES (?, NULL, ?, 'text', ?, ?)`,
               [userId, msg.key.remoteJid, matchedRule.reply_text, payload]
             );
          }
        }
      } catch (err) {
        logger.error({ err, userId }, 'Chatbot rule processing failed');
      }
    });

    return session;
  })();

  try {
    await session.starting;
  } finally {
    session.starting = null;
  }

  return session;
}

async function claimNextMessage() {
  await db.execute(
    `UPDATE message_queue
     SET status = 'queued', locked_at = NULL, locked_by = NULL
     WHERE status = 'processing' AND locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)`
  );

  try {
    if (supportsSleepingColumn) {
      await db.execute(
        `UPDATE message_queue
         SET status = 'processing', locked_at = NOW(), locked_by = ?, attempts = attempts + 1
         WHERE id = (
           SELECT id FROM (
             SELECT message_queue.id
             FROM message_queue
             LEFT JOIN whatsapp_sessions
               ON whatsapp_sessions.user_id = message_queue.user_id
             WHERE message_queue.status = 'queued'
               AND message_queue.available_at <= NOW()
               AND COALESCE(whatsapp_sessions.sleeping, 0) = 0
             ORDER BY message_queue.id ASC
             LIMIT 1
           ) AS next_message
         )`,
        [WORKER_ID]
      );
    } else {
      await db.execute(
        `UPDATE message_queue
         SET status = 'processing', locked_at = NOW(), locked_by = ?, attempts = attempts + 1
         WHERE id = (
           SELECT id FROM (
             SELECT id FROM message_queue
             WHERE status = 'queued' AND available_at <= NOW()
             ORDER BY id ASC
             LIMIT 1
           ) AS next_message
         )`,
        [WORKER_ID]
      );
    }
  } catch (error) {
    if (supportsSleepingColumn && isMissingSleepingColumnError(error)) {
      supportsSleepingColumn = false;
      return claimNextMessage();
    }
    throw error;
  }

  const [rows] = await db.execute(
    `SELECT * FROM message_queue
     WHERE status = 'processing' AND locked_by = ?
     ORDER BY locked_at DESC
     LIMIT 1`,
    [WORKER_ID]
  );

  return rows[0] || null;
}

async function releaseMessageToQueue(message) {
  await db.execute(
    `UPDATE message_queue
     SET status = 'queued', locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [message.id]
  );
}

async function sendQueuedMessage(message) {
  const currentSession = getSession(String(message.user_id));
  currentSession.lastActivityAt = Date.now();

  if (supportsSleepingColumn) {
    try {
      const [sessionRows] = await db.execute(
        'SELECT sleeping FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
        [message.user_id]
      );
      if (sessionRows[0] && Number(sessionRows[0].sleeping) === 1) {
        currentSession.sleeping = true;
      }
    } catch (error) {
      if (isMissingSleepingColumnError(error)) {
        supportsSleepingColumn = false;
      } else {
        throw error;
      }
    }
  }

  if (currentSession.sleeping) {
    currentSession.wasSleeping = true;
    currentSession.sleeping = false;
    await updateSessionRecord(currentSession.userId, currentSession);
    logger.info('Auto-waking session for user ' + currentSession.userId + ' to process queue.');
  }

  const session = await startSession(String(message.user_id));
  await new Promise((resolve) => setTimeout(resolve, SEND_DELAY_MS));

  if (session.sleeping) {
    await releaseMessageToQueue(message);
    return;
  }

  if (session.state !== 'connected' || !session.sock) {
    throw new Error('WhatsApp device is not connected.');
  }

  const recipient = normalizeRecipient(message.recipient);
  if (!recipient.jid) {
    await markMessageFailed(message, 'Invalid recipient. Use an international number or a 10-digit Indian mobile number.', recipient.normalizedTo);
    return;
  }

  if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
    const matches = await session.sock.onWhatsApp(recipient.jid);
    const exists = Array.isArray(matches) && matches.some((match) => match.exists);
    if (!exists) {
      await markMessageFailed(message, `The number ${recipient.normalizedTo} is not registered on WhatsApp.`, recipient.normalizedTo);
      return;
    }

  // "Humanized Delivery" Presence & Delay
  let isHumanized = false;
  let isAudio = false;
  try {
    if (message.payload) {
      const payload = typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload;
      if (payload.humanize) isHumanized = true;
      if (payload.media_url && payload.media_url.match(/\.(mp3|ogg|wav)$/i)) isAudio = true;
    }
  } catch (e) {
    // skip
  }

  if (isHumanized && typeof session.sock.sendPresenceUpdate === 'function') {
    const presenceStatus = isAudio ? 'recording' : 'composing';
    try {
      await session.sock.sendPresenceUpdate('available');
      await session.sock.sendPresenceUpdate(presenceStatus, recipient.jid);
      const delayMs = Math.floor(Math.random() * 3000) + 2000; // 2 to 5 seconds
      await new Promise(resolve => setTimeout(resolve, delayMs));
      await session.sock.sendPresenceUpdate('paused', recipient.jid);
    } catch (err) {
      logger.warn({ err }, 'Failed to send presence update, continuing anyway.');
    }
  }
  }

  let payload = {};
  try {
    payload = message.payload ? JSON.parse(String(message.payload)) : {};
  } catch {
    payload = {};
  }

  const attachment = payload?.attachment;
  let outgoingMessage;

  if (attachment?.path) {
    if (!existsSync(attachment.path)) {
      await markMessageFailed(message, 'Attachment file is missing from storage.', recipient.normalizedTo);
      return;
    }

    const attachmentType = String(attachment.type || message.message_type || 'document');
    const hasCaption = String(message.body || '').trim() !== '';
    const commonMedia = {
      mimetype: attachment.mime || undefined,
      fileName: attachment.name || undefined
    };
    if (hasCaption) {
      commonMedia.caption = String(message.body || '');
    }

    const fileStats = statSync(attachment.path);
    if (!fileStats.isFile()) {
      await markMessageFailed(message, 'Attachment path is invalid.', recipient.normalizedTo);
      return;
    }

    if (attachmentType === 'image') {
      outgoingMessage = { image: { url: attachment.path }, ...commonMedia };
    } else if (attachmentType === 'video') {
      outgoingMessage = { video: { url: attachment.path }, ...commonMedia };
    } else {
      outgoingMessage = { document: { url: attachment.path }, ...commonMedia };
    }
  } else {
    outgoingMessage = { text: String(message.body) };
  }

  if (!session.sock) {
      throw new Error('WhatsApp connection lost during humanized delay.');
    }
    const result = await session.sock.sendMessage(recipient.jid, outgoingMessage);
  await markMessageSent(message, result, recipient.normalizedTo);
}

async function sendWebhook(userId, payload) {
  const [rows] = await db.execute(
    'SELECT * FROM webhooks WHERE user_id = ? AND enabled = 1 AND target_url IS NOT NULL LIMIT 1',
    [userId]
  );
  const webhook = rows[0];
  if (!webhook) {
    return;
  }

  const body = JSON.stringify({ ...payload, occurred_at: new Date().toISOString() });
  const headers = { 'Content-Type': 'application/json' };
  if (webhook.secret) {
    headers['X-WA-Hub-Signature'] = crypto.createHmac('sha256', webhook.secret).update(body).digest('hex');
  }

  try {
    await assertSafeWebhookUrl(webhook.target_url);
  } catch (error) {
    await recordWebhookDelivery(webhook, payload, null, error.message, 1);
    logger.warn({ error, userId }, 'Blocked unsafe webhook target');
    return;
  }

  for (let attempt = 1; attempt <= 3; attempt++) {
    try {
      const response = await fetch(webhook.target_url, { method: 'POST', headers, body, signal: AbortSignal.timeout(8000) });
      await recordWebhookDelivery(webhook, payload, response.status, response.ok ? null : `HTTP ${response.status}`, attempt);
      if (response.ok) {
        return;
      }
    } catch (error) {
      await recordWebhookDelivery(webhook, payload, null, error.message, attempt);
      logger.warn({ error, userId, attempt }, 'Webhook delivery failed');
    }

    await new Promise((resolve) => setTimeout(resolve, attempt * 1000));
  }
}

async function markMessageSent(message, result, normalizedTo) {
  const response = JSON.stringify({ ok: true, messageId: result?.key?.id || null, normalizedTo });
  await db.execute(
    `UPDATE message_queue
     SET status = 'sent', normalized_recipient = ?, sent_at = NOW(), provider_message_id = ?,
         provider_response = ?, error_message = NULL, locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [normalizedTo, result?.key?.id || null, response, message.id]
  );
  await db.execute(
    `INSERT INTO message_logs (user_id, recipient, message_preview, status, provider_response)
     VALUES (?, ?, ?, 'sent', ?)`,
    [message.user_id, normalizedTo, String(message.body).slice(0, 255), response]
  );
  await sendWebhook(message.user_id, {
    event: 'message.sent',
    message_id: message.id,
    to: normalizedTo,
    provider_message_id: result?.key?.id || null
  });
}

async function markMessageFailed(message, error, normalizedTo = null) {
  const finalFail = Number(message.attempts) >= Number(message.max_attempts);
  const nextStatus = finalFail ? 'failed' : 'queued';
  const delay = Math.min(300, 15 * Math.max(1, Number(message.attempts)));

  await db.execute(
    `UPDATE message_queue
     SET status = ?, normalized_recipient = COALESCE(?, normalized_recipient), error_message = ?,
         available_at = DATE_ADD(NOW(), INTERVAL ? SECOND), locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [nextStatus, normalizedTo, error, delay, message.id]
  );

  if (finalFail) {
    await db.execute(
      `INSERT INTO message_logs (user_id, recipient, message_preview, status, error_message)
       VALUES (?, ?, ?, 'failed', ?)`,
      [message.user_id, normalizedTo || message.recipient, String(message.body).slice(0, 255), error]
    );
    await sendWebhook(message.user_id, {
      event: 'message.failed',
      message_id: message.id,
      to: normalizedTo || message.recipient,
      error
    });
  }
}


let queueBusy = false;
async function processQueueOnce() {
  if (queueBusy) {
    return;
  }

  queueBusy = true;
  try {
    const message = await claimNextMessage();
    if (message) {
      try {
        await sendQueuedMessage(message);
      } catch (error) {
        await markMessageFailed(message, error.message);
      }
    } else {
      const now = Date.now();
      for (const session of sessions.values()) {
        if (session.wasSleeping && !session.sleeping) {
          const lastActivity = session.lastActivityAt || now;
          if (now - lastActivity > 5 * 60 * 1000) {
            logger.info('Session for user ' + session.userId + ' was inactive for 5 minutes. Returning to sleep mode.');
            session.wasSleeping = false;
            session.sleeping = true;
            session.qr = null;
            session.hasQr = false;
            if (session.sock) {
              session.sock.end?.();
            }
            await updateSessionRecord(session.userId, session);
          }
        }
      }
    }
  } catch (error) {
    logger.error({ error }, 'Queue processor failed');
  } finally {
    queueBusy = false;
  }
}

app.get('/health', (req, res) => {
  res.json({ ok: true, service: 'whatsapp-worker' });
});

app.get('/sessions/:userId/status', (req, res) => {
  res.json({ ok: true, session: publicSession(getSession(req.params.userId)) });
});

app.post('/sessions/:userId/start', async (req, res) => {
  try {
    const session = await startSession(req.params.userId);
    res.json({ ok: true, session: publicSession(session) });
  } catch (error) {
    const session = getSession(req.params.userId);
    session.state = 'error';
    session.error = error.message;
    res.status(500).json({ ok: false, error: error.message, session: publicSession(session) });
  }
});

app.post('/sessions/:userId/logout', async (req, res) => {
  const session = getSession(req.params.userId);
  const authDir = join(SESSION_DIR, `user_${req.params.userId}`);

  try {
    if (session.sock) {
      await session.sock.logout();
      session.sock.end?.();
    }
    await rm(authDir, { recursive: true, force: true });
  } catch (error) {
    session.error = error.message;
  }

  sessions.set(req.params.userId, emptySession(req.params.userId));
  res.json({ ok: true, session: publicSession(getSession(req.params.userId)) });
});

app.post('/sessions/:userId/sleep', async (req, res) => {
  const session = getSession(req.params.userId);
  session.sleeping = true;
  session.qr = null;
  session.hasQr = false;
  session.error = null;

  try {
    if (session.sock) {
      session.sock.end?.();
    }
  } catch (error) {
    session.error = error.message;
  }

  session.sock = null;
  session.state = 'idle';
  await updateSessionRecord(req.params.userId, session);
  res.json({ ok: true, session: publicSession(session) });
});

app.post('/sessions/:userId/wake', async (req, res) => {
  try {
    const session = getSession(req.params.userId);
    session.sleeping = false;
    const started = await startSession(req.params.userId);
    res.json({ ok: true, session: publicSession(started) });
  } catch (error) {
    const session = getSession(req.params.userId);
    session.state = 'error';
    session.error = error.message;
    res.status(500).json({ ok: false, error: error.message, session: publicSession(session) });
  }
});

app.post('/sessions/:userId/send', async (req, res) => {
  const session = getSession(req.params.userId);
  const recipient = normalizeRecipient(req.body.to);
  const message = String(req.body.message || '').trim();

  if (!recipient.jid || !message) {
    res.status(422).json({
      ok: false,
      error: 'Enter a valid recipient and message. Use an international number, or a 10-digit Indian mobile number.',
      normalizedTo: recipient.normalizedTo
    });
    return;
  }

  if (session.state !== 'connected' || !session.sock) {
    res.status(409).json({ ok: false, error: 'WhatsApp device is not connected.', session: publicSession(session) });
    return;
  }

  try {
    if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
      const matches = await session.sock.onWhatsApp(recipient.jid);
      const exists = Array.isArray(matches) && matches.some((match) => match.exists);

      if (!exists) {
        res.status(404).json({
          ok: false,
          error: `The number ${recipient.normalizedTo} is not registered on WhatsApp.`,
          normalizedTo: recipient.normalizedTo
        });
        return;
      }
    }

    const result = await session.sock.sendMessage(recipient.jid, { text: message });
    res.json({ ok: true, messageId: result?.key?.id || null, normalizedTo: recipient.normalizedTo });
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message, normalizedTo: recipient.normalizedTo });
  }
});

app.listen(PORT, '127.0.0.1', () => {
  logger.info(`WhatsApp worker listening on http://127.0.0.1:${PORT}`);
  setInterval(processQueueOnce, QUEUE_POLL_MS);
});

