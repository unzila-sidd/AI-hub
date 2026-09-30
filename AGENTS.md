# AGENTS.md — Project conventions

These rules apply to every change made in this repository.

## Stand-up rule: keep the README manual current

The user runs the project and uses the API, and does NOT want to reverse-engineer anything.
Therefore, **whenever you change the system, update the README if the change affects any of**:

- how the system is run (commands, ports, Docker, migrations, seeders, credentials)
- the REST API (endpoints, request/response shapes, auth, permissions, status codes)
- environment / configuration variables (`.env`, `.env.example`)
- features a user can see or rely on

Update `README.md` (quick-start manual) and, when the change is meaningful, also update:

- `docs/REST_API.md` — if the API changed
- `docs/PROJECT_BOOK.md` — long-form book guide (regenerate the Word doc after edits with `python tools/md_to_docx.py docs/PROJECT_BOOK.md docs/AI-Productivity-Hub-Guide.docx`)

## Repo facts

- Branch: `feature/pos` (POS module + API live here); `main` has the chat app baseline.
- Run everything through Docker. Containers: `AI_chat` (app), `AI_nginx` (8003), `ai_chat` (MySQL 3308), `ai_phpmyadmin` (8083), `ai_mailhog` (8027).
- App is mounted at `.:/var/www`, so file edits are visible in the container immediately (no rebuild needed for PHP changes).

## Commands that work here

- docker compose up -d
- docker exec AI_chat php artisan <cmd>  (migrate / db:seed / sync:run / route:list / tinker)
- docker exec AI_chat npm install && npm run build

## Conventions

- The user commits and pushes themselves. **Do not run git commit/push unless explicitly asked.**
- No code comments unless asked. Follow existing controller/service patterns (constructor-injected services like `SyncService`, `abort_unless(..., 403)` permission checks).
- API endpoints always return JSON, enforce permissions server-side, and record `sync_events` on writes.
- Payments permissions are split: `payments.add`, `payments.edit`, `payments.delete` (three separate toggles).
- Seeders must be idempotent and never overwrite existing user data. Version the login/registration via roles.