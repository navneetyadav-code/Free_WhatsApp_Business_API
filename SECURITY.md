# Security Policy

## Reporting a Vulnerability

Please do not open public issues that include API keys, worker tokens, database passwords, webhook secrets, or WhatsApp session files.

If you find a vulnerability, report it privately to the project maintainer with:

- A short description of the issue.
- Reproduction steps.
- The affected files or endpoints.
- Any safe proof-of-concept details that do not expose real credentials.

## Sensitive Files

Never commit these files or directories:

- `.env`
- `worker/node_modules/`
- `worker/sessions/`
- `*.log`
- WhatsApp linked-device credential JSON files

Use `WA_SESSION_DIR` to keep WhatsApp session data outside the web root.
