# AI-Hub — Run & API Manual

An **AI productivity chat app** built with Laravel 12, with an integrated **Point-of-Sale (POS)** module and a **REST API** for the POS. This README is the one-stop manual: how to run the whole system and how to use the API.

> Tip: a long-form, book-style guide lives in `docs/PROJECT_BOOK.md`, a full Postman tutorial in `docs/REST_API.md`, and this README is the quick-start version of both.

---

## 1. Features

### AI Chat
- Register / login, conversation history sidebar, chat with OpenAI (`gpt-4o-mini`), voice reply playback.

### POS
- POS terminal (click product → cart → checkout → printed receipt)
- Products CRUD with stock + low-stock alerts
- Payments — **Add / Edit / Delete are three separate role permissions**
- Sales history + reports (daily sales, top products, payment methods, low stock)
- Roles & Permissions manager, Users manager
- AdminLTE 3 theme bundled locally (works offline)

### Offline-first sync
- Every product/sale/payment write goes to the local DB immediately (works with no wifi)
- Changes queued in `sync_events`; pushed to `SYNC_REMOTE_URL` every 5 min (`php artisan sync:run`) once back online

### REST API (new)
- Bearer-token auth (`api_tokens` table, hashed tokens) for all POS resources — see section 5.

---

## 2. Requirements

- **Docker Desktop**
- OpenAI API key (set `OPENAI_API_KEY` in `.env`)
- (Optional, for sync) `SYNC_REMOTE_URL` + `SYNC_TOKEN`

---

## 3. How to run the system

### 3.1 First-time setup

```bash
# 1. Copy the environment file, then put your OpenAI key in .env
cp .env.example .env

# 2. Build and start all containers
docker compose up -d --build

# 3. Install and build front-end assets
docker exec AI_chat npm install
docker exec AI_chat npm run build

# 4. Run migrations and seeders
docker exec AI_chat php artisan migrate --seed
```

The seeder creates roles (`admin`, `manager`, `cashier`), permissions (payments split into Add/Edit/Delete), sample products, and an admin account. **It prints a random admin password** — note it:

```
Admin account created: admin@example.com / <random-password>
```

Default web login: `admin@example.com` / the printed password (never the literal word `password`).

> If the app already ran before, the seeder only fills in the gaps (it never overwrites existing data), and an existing admin user is just re-assigned the `admin` role.

### 3.2 Every day

```bash
docker compose up -d
```

The app is then at http://localhost:8003 — open:
- AI chat: http://localhost:8003/chat
- POS terminal: http://localhost:8003/pos
- Reports / products / payments / roles / users: the AdminLTE sidebar in the app

### 3.3 Useful commands

| Task | Command |
|---|---|
| Run migrations | `docker exec AI_chat php artisan migrate --force` |
| Re-seed data | `docker exec AI_chat php artisan db:seed --force` |
| Rebuild assets | `docker exec AI_chat npm run build` |
| Trigger sync now | `docker exec AI_chat php artisan sync:run` |
| List API routes | `docker exec AI_chat php artisan route:list --path=api` |
| Container shell | `docker exec -it AI_chat bash` |
| Stop everything | `docker compose down` |

### 3.4 Ports / services

| Service | URL |
|---|---|
| App (nginx) | http://localhost:8003 |
| phpMyAdmin | http://localhost:8083 |
| MailHog UI | http://localhost:8027 |
| MySQL | localhost:3308 |

---

## 4. Roles & permissions (both web and API)

| Permission | Who needs it |
|---|---|
| `products.view/add/edit/delete` | Managing products |
| `sales.create` | Running the POS checkout |
| `sales.view` | Viewing sales history |
| `payments.view/add/edit/delete` | Payments (three separate actions) |
| `reports.view` | Viewing reports |
| `roles.manage`, `users.manage` | Admin only |

Roles seeded: **Admin** (everything), **Manager** (products, sales, payments except delete, reports), **Cashier** (view products, create sales, view/add payments). Registered users and API registrations default to **Cashier**.

---

## 5. REST API

Base URL: `http://localhost:8003/api`

Auth: send the token in every request:

```
Authorization: Bearer <your-token>
```

You get a token from `POST /api/login` or `POST /api/register`. Tokens are stored **hashed** (SHA-256) in `api_tokens`; the plaintext is shown only once.

### 5.1 Endpoint reference

| Method | Endpoint | Permission | Purpose |
|---|---|---|---|
| `POST` | `/api/register` | public | Create a cashier user + get a token |
| `POST` | `/api/login` | public | Log in + get a token |
| `POST` | `/api/logout` | any token | Revoke the current token |
| `GET` | `/api/me` | any token | Your user, role, permissions |
| `GET` | `/api/products` | product perms | List products (`?search=&stock=low&per_page=`) |
| `POST` | `/api/products` | `products.add` | Create a product |
| `PUT` | `/api/products/{id}` | `products.edit` | Update a product |
| `DELETE` | `/api/products/{id}` | `products.delete` | Delete a product |
| `GET` | `/api/sales` | `sales.view` | List sales (`?search=`) |
| `POST` | `/api/checkout` | `sales.create` | Checkout a sale |
| `GET` | `/api/payments` | `payments.view` | List payments (`?method=&search=`) |
| `POST` | `/api/payments` | `payments.add` | Record a payment |
| `PUT` | `/api/payments/{id}` | `payments.edit` | Edit a payment |
| `DELETE` | `/api/payments/{id}` | `payments.delete` | Delete a payment |
| `GET` | `/api/reports` | `reports.view` | Reports (`?from=YYYY-MM-DD&to=YYYY-MM-DD`) |

### 5.2 Quick start (any REST client — Postman, curl, code)

```bash
# 1. Login
curl -X POST http://localhost:8003/api/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"admin@example.com","password":"<SEEDED_ADMIN_PASSWORD>"}'
# → { "token": "...", "user": {...} }

# 2. Use the token
curl http://localhost:8003/api/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"

# 3. Checkout a sale
curl -X POST http://localhost:8003/api/checkout \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"items":[{"product_id":8,"qty":2}],"method":"cash"}'
```

### 5.3 Example: checkout response (201)

```json
{
  "data": {
    "invoice_no": "INV-20260930-3B10",
    "customer_name": "Postman Test",
    "subtotal": 9,
    "discount": 1,
    "total": 8,
    "payment_status": "paid",
    "items": [ { "product_name": "Mineral Water", "qty": 2, "line_total": 2 } ],
    "payments": [ { "amount": 8, "method": "cash" } ]
  }
}
```

### 5.4 Status codes

| Code | Meaning |
|---|---|
| `200` | OK (read/update) |
| `201` | Created (login/register/new row) |
| `204` | No content (delete) |
| `401` | Bad / missing / revoked token |
| `403` | No permission for the action |
| `404` | Not found |
| `422` | Validation failed (includes an `errors` object) |

### 5.5 Guards

- Rate limits: whole API `120/min`, login/register `5/min`, checkout `10/min`
- Checkout locks the product rows and re-checks stock inside a DB transaction (two registers can't oversell)
- API writes also queue `sync_events`, keeping the offline-first sync consistent

Full step-by-step Postman tutorial: **`docs/REST_API.md`**.

---

## 6. Offline sync setup

1. Set `SYNC_REMOTE_URL` (your central server endpoint) and `SYNC_TOKEN` (shared bearer token) in `.env`.
2. The remote endpoint must accept `POST` with `{ entity, entity_id, action, payload }` and reply `2xx`.
3. The scheduler pushes queued events every 5 minutes — or run manually:

```bash
docker exec AI_chat php artisan sync:run
```

---

## 7. Security notes

- Tokens hashed in DB; plaintext shown once at login/register
- Login throttled; API-wide throttle; `SESSION_SECURE_COOKIE` in prod
- Permission checks server-side on every web + API request
- Headers middleware (CSP, X-Frame-Options, nosniff, Referrer-Policy) on web routes
- Production checklist: `APP_DEBUG=false`, HTTPS, rotate the seeded admin password, keep `.env` out of git

---

## 8. Troubleshooting

| Problem | Fix |
|---|---|
| Ports already in use | `docker compose down`, then `docker compose up -d` |
| `driver [sqlite] not found` at root | Delete the stray `laravel` SQLite file in the project root |
| Roles fail on `db:seed` ("Column not found: permissions") | Pull latest commit — seeder bug was fixed; re-run the seeder |
| Admin password unknown | Re-run seeder — it prints a fresh one and keeps your data |
| `401` from API | Token wrong/revoked → login again |
| `422 Not enough stock` | Check the product's stock first (`GET /api/products`) |