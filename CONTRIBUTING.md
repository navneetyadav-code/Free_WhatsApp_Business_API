# Contributing

Thanks for helping improve WhatsApp API Hub.

## Development Setup

1. Copy `.env.example` to `.env`.
2. Start Apache and MySQL in XAMPP.
3. Import `database.sql`, then run `upgrade.sql`.
4. Install worker dependencies from `worker/` with `npm install`.
5. Start the worker with `npm start`.

## Pull Requests

- Keep changes focused and describe the user impact.
- Do not commit `.env`, logs, `worker/node_modules`, or WhatsApp session files.
- Run PHP syntax checks before opening a PR:

```powershell
foreach ($file in Get-ChildItem -Recurse -Filter *.php -File) { php -l $file.FullName }
node --check worker\server.mjs
```

## Platform Safety

This project uses WhatsApp Web linked-device automation through Baileys. It is not Meta's official WhatsApp API. Contributions should prioritize consent, opt-in contact management, rate limiting, and transparent sender behavior.
