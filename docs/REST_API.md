# REST API & Postman Guide

This document explains the REST API **exactly** as it works in this project, and walks you through testing every endpoint in **Postman** — step by step, no prior API experience needed.

---

## 1. What is an API? (30-second version)

A website has **pages**; an API has **endpoints**. An endpoint is a URL that your code *or Postman* calls to read or change data, and it answers with **JSON** (structured text: `{ "key": "value" }`).

Example — this endpoint lists all products:

```
GET http://localhost:8003/api/products
```

While the website needs a **browser login + cookies**, the API needs a **token**. You send the token as a header so the server knows *who you are*:

```
Authorization: Bearer <your-token>
```

---

## 2. How it works in this app

| Concept | Detail |
|---|---|
| Base URL | `http://localhost:8003/api` (dev), or `https://your-server.com/api` in production |
| Auth method | **Bearer token** — you get it by logging in or registering |
| Token storage | Only the token's **hash** is saved in the `api_tokens` table; the plaintext you receive is shown **once** |
| Who can do what | Your token belongs to a user, who has a **role**, who has **permissions** (same rules as the website). E.g. only users with `products.add` can `POST /api/products` |
| Errors | Always JSON. Standard HTTP codes: `200` OK, `201` Created, `204` No content, `401` bad/missing token, `403` no permission, `404` not found, `422` validation failed |
| Rate limit | 120 requests/minute per user (login/register limited to 5/minute) |

**One rule to remember:** to change data safely at scale, an API never trusts the browser's session — it trusts the token, and it **always** re-checks permissions server-side.

---

## 3. Setup before you can test

1. **Start the servers** (Docker Desktop):
   ```
   docker compose up -d
   ```
2. **Run migrations + seeders** (creates roles, permissions, products, an admin, and the new `api_tokens` table):
   ```
   docker exec AI_chat php artisan migrate --force
   docker exec AI_chat php artisan db:seed --force
   ```
   The seeder prints the admin password when it creates the account:
   ```
   Admin account created: admin@example.com / <random-password>
   ```
   (If seeding ran before, log in on the website and change the admin password.)

3. **Open Postman** and create a new collection, e.g. `AI Hub`. Every request below goes inside it. Like folders in Windows, the collection is just a place to organise your requests.

---

## 4. All endpoints at a glance

| Method | Endpoint | Permission needed | What it does |
|---|---|---|---|
| `POST` | `/api/register` | public | Create a user (role `cashier`) and get a token |
| `POST` | `/api/login` | public | Log in and get a token |
| `POST` | `/api/logout` | any token | Revoke the current token |
| `GET` | `/api/me` | any token | Your user, role, permissions |
| `GET` | `/api/products` | `products.view` etc. | List products (search + pagination) |
| `POST` | `/api/products` | `products.add` | Create a product |
| `PUT` | `/api/products/{id}` | `products.edit` | Update a product |
| `DELETE` | `/api/products/{id}` | `products.delete` | Delete a product |
| `GET` | `/api/sales` | `sales.view` | List sales |
| `POST` | `/api/checkout` | `sales.create` | Checkout a sale (POS) |
| `GET` | `/api/payments` | `payments.view` | List payments |
| `POST` | `/api/payments` | `payments.add` | Record a payment on a sale |
| `PUT` | `/api/payments/{id}` | `payments.edit` | Edit a payment |
| `DELETE` | `/api/payments/{id}` | `payments.delete` | Delete a payment |
| `GET` | `/api/reports` | `reports.view` | Reports summary for a date range |

---

## 5. Step-by-step in Postman

> **Tip:** Postman stores things like the token in **variables**, so you don't have to keep pasting it. We'll use the variable `{{token}}`.

### Step 1 — Login (get your token)

1. Click **+ New request** → set method **POST** and URL:
   ```
   http://localhost:8003/api/login
   ```
2. Go to the **Body** tab → choose **raw** → type **JSON** (dropdown on the right).
3. Paste:
   ```json
   {
     "email": "admin@example.com",
     "password": "PASTE_THE_PASSWORD_THE_SEEDER_PRINTED"
   }
   ```
4. Press **Send**.

You get back:
```json
{
  "token": "YUZGAls7eDgqGF6KCu2Di3VGTeY32yr1...",
  "user": { "id": 3, "name": "Admin", "email": "admin@example.com" }
}
```

**Save the token into a variable (do this once):**

1. Click the **Send** button area → the **Tests** tab of the request.
2. Paste this — it stores the token automatically after every login:
   ```js
   const json = pm.response.json();
   pm.collectionVariables.set('token', json.token);
   ```
3. **Send** again. Now `{{token}}` is ready everywhere.

### Step 2 — See who you are

- New request → **GET**
  ```
  http://localhost:8003/api/me
  ```
- **Authorization** tab → type **Bearer Token** → paste `{{token}}` in the field.
- **Send.** You'll see your role and the list of permissions you have:
  ```json
  {
    "user": { "id": 3, "name": "Admin", "email": "admin@example.com" },
    "role": "Admin",
    "permissions": ["products.add", "products.view", "sales.create", "..."]
  }
  ```

### Step 3 — List products (first real data call)

- **GET**
  ```
  http://localhost:8003/api/products
  ```
- Authorization: `Bearer {{token}}` → **Send**.

You get a paginated result. The interesting parts are `data` (the products) and `total`.

Try the query parameters:
```
http://localhost:8003/api/products?search=Water&per_page=5
```
```
http://localhost:8003/api/products?stock=low
```

### Step 4 — Create a product (writing data)

- **POST**
  ```
  http://localhost:8003/api/products
  ```
- Body (raw → JSON):
  ```json
  {
    "name": "Blue Notepad",
    "sku": "STA-099",
    "category": "Stationery",
    "price": 3,
    "cost": 1.2,
    "stock": 25,
    "alert_stock": 5,
    "active": true
  }
  ```
- **Send** → `201 Created` and the new product comes back with its `id`. Write that id down; you'll use it next.

### Step 5 — Update and delete

- **PUT**
  ```
  http://localhost:8003/api/products/11
  ```
  (replace `11` with the id you got) — same style body, change `price` to `4`, send → `200`.

- **DELETE**
  ```
  http://localhost:8003/api/products/11
  ```
  → `204 No content` (empty body = success).

### Step 6 — Checkout a sale (the heart of the POS)

- **POST**
  ```
  http://localhost:8003/api/checkout
  ```
- Body:
  ```json
  {
    "items": [
      { "product_id": 8, "qty": 2 },
      { "product_id": 7, "qty": 1 }
    ],
    "customer_name": "Postman Test",
    "discount": 1,
    "method": "cash",
    "reference": null
  }
  ```
  `method` must be one of: `cash`, `card`, `upi`, `bank_transfer`, `other`.

- **Send** → `201`. You get the full sale back: `invoice_no`, the line `items`, and the `payments` that were auto-created. Stock was decremented automatically.

**Tip:** try buying more than the stock (e.g. `"qty": 9999`) — you'll see a `422` error: `Not enough stock`.

### Step 7 — Payments & Reports

- List payments with the sale it belongs to:
  ```
  GET http://localhost:8003/api/payments
  ```
- Add a **manual** payment to an unpaid sale (note: checkout makes sales `paid` automatically; a payment is the *real* money event):
  ```
  POST http://localhost:8003/api/payments
  ```
  ```json
  {
    "sale_id": 1,
    "amount": 5,
    "method": "upi",
    "reference": "UPI-REF-123",
    "note": "partial payment"
  }
  ```
  The sale's `payment_status` recomputes automatically.
- Reports for September:
  ```
  GET http://localhost:8003/api/reports?from=2026-09-01&to=2026-09-30
  ```
  Returns `summary`, `daily`, `top_products`, `by_method`, `low_stock`.

### Step 8 — Logout

- **POST**
  ```
  http://localhost:8003/api/logout
  ```
  with your token → `200` and the token is dead. Try `GET /api/me` again → `401`.

---

## 6. What each status code means (quick reference)

| Code | Meaning | You'll see it when… |
|---|---|---|
| `200` | OK | any successful read/update |
| `201` | Created | login, register, or anything that made a new row |
| `204` | No content | a successful delete |
| `401` | Unauthenticated | the token is missing, wrong, or revoked |
| `403` | Forbidden | token is valid but your role lacks the permission |
| `404` | Not found | the id doesn't exist (e.g. `/api/products/9999`) |
| `422` | Validation error | required field missing or stock too low; body has an `errors` object with the reason |

---

## 7. Common mistakes & fixes

| Symptom | Cause | Fix |
|---|---|---|
| `The email field is required.` on login | Body not sent as **raw JSON** | In Postman: Body → raw → JSON |
| Still `401` after login | Wrong/missing token header | Authorization tab → Bearer Token → `{{token}}` |
| `403` on a product edit | Your role lacks `products.edit` | Log in as `admin` (or assign the permission in the admin Users/Roles page) |
| `Route [login] not found` style HTML | You forgot the `Accept: application/json` header on an unauthenticated route | Postman adds it automatically; in code always set it |
| Everything works in Postman but not your frontend | CORS | For now the app is same-origin; when you connect an external app, enable CORS in `bootstrap/app.php` with the exact origins you trust |

---

## 8. Security notes you should know

- Tokens are hashed (`SHA-256`) in the database — a DB leak does **not** leak usable tokens.
- The plaintext token is returned **once** at login/register. If you lose it, log out and log in again.
- Permissions are enforced **server-side on every request**, exactly like the web UI (`abort_unless(..., 403)`).
- Login is throttled (5 attempts/min) and the whole API is throttled (120/min) to slow brute-force attacks.
- The checkout endpoint re-checks stock with row locking inside a DB transaction, so two terminals can't oversell the same item.
- If your shop goes offline, every API write also records a `sync_events` row — the same queue the web app uses for offline-first sync.