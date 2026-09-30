# AI-Productivity-Hub — The Complete Project Guide

*A plain-English, book-style walkthrough of everything in this project: what it does, how it is built, how the pieces fit together, and how to run, secure and operate it.*

---

## How to use this guide

Read it like a book, from front to back, the first time. After that, use the table of contents to jump to the section you need. Technical terms are explained the first time they appear, and there is a glossary at the end.

The project contains **two applications in one**:

1. An **AI chat assistant** — talk to OpenAI (GPT-4o-mini) and keep a history of your conversations.
2. A **Point of Sale (POS) system** — sell products, record payments, manage stock, users, roles and permissions, and view reports.

Both share the same login, the same database, and the same Laravel framework underneath.

---

# Part I — The Big Picture

## 1. What this project is

| | |
|---|---|
| **Project name** | AI-Productivity-Hub |
| **Purpose** | AI chat + small-shop Point of Sale |
| **Framework** | Laravel 12 (PHP 8.2) |
| **Frontend** | Blade templates + AdminLTE 3 theme + Bootstrap 4 |
| **Database** | MySQL 8 (MariaDB-compatible use) |
| **Runs in** | Docker (PHP-FPM + Nginx + MySQL + phpMyAdmin + Mailhog) |
| **AI provider** | OpenAI (gpt-4o-mini) |
| **Works offline?** | Yes — the app writes to its local database even with no internet, then syncs queued changes when the connection returns. |

## 2. Technology stack

| Layer | Technology | What it does for you |
|---|---|---|
| Server | **Nginx** | Receives web requests, serves static files, forwards PHP requests |
| Language | **PHP 8.2** | Runs the Laravel application |
| Framework | **Laravel 12** | Routing, database, security, templating — the "engine" |
| Database | **MySQL 8** | Stores users, products, sales, payments, roles, sync queue |
| Templates | **Blade** | PHP template engine for writing pages |
| Admin theme | **AdminLTE 3** | The professional dashboard look and feel |
| CSS/JS | **Bootstrap 4 + jQuery** | Layout, buttons, modals, interactivity |
| AI | **OpenAI PHP client** | Calls the ChatGPT API |
| Containerisation | **Docker Compose** | Runs everything in repeatable containers |
| Build tool | **Vite** | Compiles CSS/JS assets |

## 3. How the app runs (the "engine room")

The project is designed to run inside **Docker containers**, so you do not need PHP or MySQL installed on your computer.

`docker-compose.yml` defines five services:

| Service | Container name | Purpose | Port |
|---|---|---|---|
| `app` | AI_chat | PHP-FPM that runs the Laravel code | (internal) |
| `nginx` | AI_nginx | Web server, front door of the app | **8003** |
| `db` | ai_chat | MySQL database | **3308** |
| `phpmyadmin` | ai_phpmyadmin | Visual database browser | **8083** |
| `mailhog` | ai_mailhog | Catches email (password resets, verification) | **8027** |

**Flow of a request:**

```
Browser ──► nginx (port 8003) ──► PHP-FPM (Laravel) ──► MySQL
```

The Dockerfile (`docker/php/Dockerfile`) builds a PHP 8.2 image with the PHP extensions needed (MySQL, mbstring, etc.) plus Node.js for building front-end assets and Composer for PHP packages.

The key idea: **your code lives in a shared folder mounted into the containers**, so edits you make are seen immediately.

## 4. Folder map (annotated)

```
AI-Productivity-Hub/
│
├── app/
│   ├── Console/Commands/        # artisan commands (e.g. sync:run)
│   ├── Http/
│   │   ├── Controllers/         # the "traffic controllers" for each page
│   │   │   ├── Auth/            # login, register, password tools
│   │   │   ├── ChatController   # AI chat logic
│   │   │   ├── PosController    # POS terminal + checkout
│   │   │   ├── ProductController
│   │   │   ├── PaymentController
│   │   │   ├── ReportController
│   │   │   ├── RoleController
│   │   │   └── UserController
│   │   └── Middleware/          # goes-between-filters (SecurityHeaders)
│   ├── Models/                  # database "blueprints" (User, Product, Sale…)
│   ├── Services/                # helper classes (OpenAIService, SyncService)
│   └── View/                    # layout components
│
├── bootstrap/
│   └── app.php                  # middleware + proxy configuration
│
├── config/                      # Laravel configuration files
├── database/
│   ├── migrations/              # instructions that create/change tables
│   └── seeders/                 # sample data (roles, products, admin user)
│
├── docker/                      # Dockerfile + nginx config
├── public/
│   ├── css/  js/                # hand-written CSS and JavaScript
│   └── libs/adminlte/           # the downloaded admin theme (offline-friendly)
│
├── resources/views/             # all the pages (Blade templates)
│   ├── layouts/                 # shared page layouts
│   │   ├── app.blade.php        # layout for the main app / chat
│   │   └── admin.blade.php      # layout for the POS (AdminLTE)
│   ├── auth/                    # login/register screens
│   ├── chat/                    # the AI chat screen
│   └── pos/                     # POS pages (products, payments, reports…)
│
├── routes/
│   ├── web.php                  # web routes (which URL → which controller)
│   ├── auth.php                 # login/register routes
│   └── console.php              # scheduler (offline sync)
```

---

# Part II — How a Request Flows

## 5. The request lifecycle

When you type a URL and press Enter, this happens:

1. **Nginx** receives the request and sends it to PHP-FPM.
2. Laravel boots, loads the **routes** file (`routes/web.php`) and finds the matching **route**.
3. Any **middleware** on that route runs first (checking login, permission, security headers, rate limits).
4. The matching **controller method** runs — it talks to **models** (database) and does the business logic.
5. The controller returns a **view** (a Blade page) or **JSON**.
6. Laravel sends the page back to the browser.

**Concrete example — a cashier goes to the POS page:**

```
GET /pos
  1. matches route: Route::get('/pos', [PosController::class, 'index'])
  2. middleware 'auth'       → is the user logged in? (if not, redirect to /login)
  3. middleware 'throttle'   → more than 30 requests/min? (then 429 Too Many Requests)
  4. PosController::index()
       → checks permission: user must have 'sales.create'
       → loads products from the Product model
       → returns the Blade view pos/index
  5. Browser receives the HTML page
```

## 6. Routing

All URLs are defined in `routes/web.php`. The important groups:

| URL pattern | Purpose | Middleware |
|---|---|---|
| `/`, `/dashboard`, `/chat` | Landing, dashboard, chat | `auth` (+ `verified` for dashboard) |
| `/pos` … | POS terminal, sales, receipts | `auth` + `throttle` |
| `/admin/products` | Product CRUD | `auth` + `throttle` + permission checks |
| `/admin/payments` | Payment CRUD | `auth` + `throttle` + permission checks |
| `/admin/reports` | Reports | `auth` + permission |
| `/admin/roles` | Roles & Permissions | `auth` + permission |
| `/admin/users` | User management | `auth` + permission |
| `/login`, `/register` | Who can use the app | `guest` |

A **route name** (e.g. `pos.products.index`) is the friendly name used in templates instead of hard-coding URLs.

## 7. Controllers

Controllers are the middle-man: they receive the request, do the work, and decide what to show. A typical method (from `ProductController`):

```
show the create form   → ProductController::create()
save a new product     → ProductController::store()
show the edit form     → ProductController::edit()
save changes           → ProductController::update()
delete a product       → ProductController::destroy()
```

Every method starts with a **permission check**:

```php
abort_unless(auth()->user()->hasPermission('products.add'), 403);
```

If the user lacks permission, Laravel shows *"403 Forbidden"*.

## 8. Middleware

Middleware is code that runs *before or after* a request is handled — like airport security. The project uses:

| Middleware | Job |
|---|---|
| `auth` | Block page unless logged in |
| `guest` | Only allow visitors (not logged-in users) |
| `verified` | Require the email to be verified |
| `throttle:60,1` | Max 60 requests per minute |
| `signed` | Only allow signed (tamper-proof) URLs |
| `SecurityHeaders` | Adds protective HTTP headers to every response |
| CSRF protection | Built-in to every form/POST |

## 9. Views and Blade

Views are the pages. The `@extends('layouts.admin')` line on every POS page means "wrap my content inside the AdminLTE layout". `@section('content')` … `@endsection` marks the part of the page that fills the layout.

Example from the roles page:

```blade
@extends('layouts.admin')

@section('content')
  <h1>Roles & Permissions</h1>
  ... page content ...
@endsection
```

**Security note:** Blade escapes everything inside `{{ }}` automatically, so user input (like a product name) is displayed as plain text and cannot inject JavaScript.

---

# Part III — The AI Chat App

## 10. How the chat works

The chat is a ChatGPT-style conversation page. The interesting part is **who holds the conversation state** — the database.

When you send a message:

1. The browser posts your text to `/chat/send` (route `chat.send`).
2. `ChatController::send()` does this:
   - If there is no conversation yet, it creates one (the first 40 characters of your message become the title).
   - It **saves your message** to the `messages` table (role = `user`).
   - It loads the **last 10 messages** of this conversation as "history".
   - It sends that history to **OpenAI** via `OpenAIService`.
   - It **saves the AI reply** (role = `assistant`).
   - It returns the reply as JSON.
3. The browser's `chat.js` puts the reply on screen and **speaks it aloud** with the browser's speech API.

## 11. Files involved

| File | Role |
|---|---|
| `app/Http/Controllers/ChatController.php` | The brains of the chat |
| `app/Services/OpenAIService.php` | Thin wrapper around the OpenAI API |
| `app/Models/Conversation.php` | A chat thread (belongs to a user) |
| `app/Models/Message.php` | One message in a thread |
| `resources/views/chat/index.blade.php` | The chat screen |
| `resources/views/layouts/app.blade.php` | The page shell |
| `public/js/chat.js` | Browser-side behaviour |
| `public/css/chat.css` | Chat styling |

## 12. How OpenAI is called

`OpenAIService` creates a client using the key from your `.env` file:

```php
$this->client = OpenAI::client(env('OPENAI_API_KEY'));
```

Then `chat($messages)` posts to the `chat/completions` endpoint:

```php
$response = $this->client->chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => $messages,
    'temperature' => 0.7,
]);
```

It returns the assistant's text, which is saved to the database.

> **Important:** the key lives in `.env`, which is **not** committed to Git. Anyone who clones the repo must add their own key.

---

# Part IV — The POS System

## 13. Feature overview

| Feature | Where | What you can do |
|---|---|---|
| POS terminal | `/pos` | Click products to build a cart, checkout, print receipt |
| Products | `/admin/products` | Add / edit / delete products, track stock |
| Payments | `/admin/payments` | Record and manage payments (add/edit/delete each a separate permission) |
| Reports | `/admin/reports` | Daily sales, revenue, top products, payments by method, low stock |
| Roles & Permissions | `/admin/roles` | Create roles and grant/deny each permission |
| Users | `/admin/users` | Add users and assign them roles |
| Sales history | `/pos/sales` | See all sales and open receipts |

## 14. Products

A product has: name, SKU, category, selling price, cost, current stock, low-stock alert level, description and an active/inactive flag.

- Products are managed by `ProductController`.
- The **POS terminal only shows active products with stock**.
- When stock is sold, it is decreased automatically.
- A product can be **soft-deleted** (moved to the recycle bin, not destroyed forever).

## 15. Making a sale (checkout)

The POS terminal page shows a grid of products on the left and a cart on the right.

The logic (`public/js/pos.js`):

1. Click a product → it is added to the cart (or quantity increases).
2. Use **+ / − / ✕** to adjust quantities.
3. Optionally type a customer name, discount and payment method.
4. Click **Complete Sale** → the cart is sent to `POST /pos/checkout`.

`PosController::store()` then does the following *inside a database transaction* (all-or-nothing):

1. For each item: locks the product row, checks there is enough stock.
2. Calculates subtotal, discount, total.
3. Creates a **Sale** (with a unique invoice number like `INV-20260930-AB12`) with status **paid**.
4. Creates the **SaleItems** rows.
5. Creates a **Payment** for the total.
6. Decreases each product's stock.
7. Records a **sync event** so the change can be pushed to the central server later (see Part VII).
8. Redirects to the **receipt page**, which can be printed.

**Safety feature:** because it is a transaction, if anything fails halfway (e.g. not enough stock), the whole sale is rolled back — no half-saved orders.

## 16. Payments — the three-permission split

This is one of the key custom requirements. In most systems "can handle payments" is one switch. Here it is **three separate permissions**:

```
payments.add      → can Record a payment
payments.edit     → can Change a payment
payments.delete   → can Remove a payment
```

Each action in `PaymentController` checks its own permission:

```php
// Store a new payment:
abort_unless(auth()->user()->hasPermission('payments.add'), 403);

// Save an edited payment:
abort_unless(auth()->user()->hasPermission('payments.edit'), 403);

// Delete a payment:
abort_unless(auth()->user()->hasPermission('payments.delete'), 403);
```

So you could give a manager "add" and "edit" but **not** "delete" — they simply will not see or be able to use the delete button, and even a direct URL is blocked.

The **role settings page** presents each of these as its own checkbox, grouped under "Payments", so a role can have Add ✓, Edit ✓, Delete ✗.

When a payment is added/edited/deleted, the sale's `payment_status` is recomputed: **paid** if sum of payments ≥ sale total, otherwise **unpaid**.

## 17. Reports

`ReportController::index()` builds a dashboard for a chosen date range (defaults to the current month):

| Report | What it shows |
|---|---|
| Summary cards | Number of sales, total revenue, items sold, average sale |
| Daily sales | A bar chart of revenue per day |
| Top products | Best-selling products by quantity and revenue |
| Payments by method | Money received per method (cash / card / UPI …) |
| Low stock | Products at or below their alert level |

The bar chart is pure CSS (no internet-dependent chart library), so it works fully offline.

## 18. Roles & Permissions (the RBAC)

This is a **hand-built Role-Based Access Control** system (no third-party package).

**The tables:**

- `roles` — the roles (e.g. admin, manager, cashier)
- `permissions` — every individual action (e.g. `products.add`)
- `role_permission` — which role has which permission (many-to-many)
- `users.role_id` — which role a user has

**The logic lives on the `User` model:**

```php
public function hasPermission(string $slug): bool
{
    if (!$this->role) return false;            // no role → no permissions
    if ($this->hasRole('admin')) return true;  // admin bypasses all checks
    return $this->role->permissions()
        ->where('slug', $slug)
        ->exists();
}
```

**The seeded roles:**

| Role | Can do |
|---|---|
| **Admin** | Everything (bypasses permission checks) |
| **Manager** | Products (all), Sales (all), Payments (add/edit/view but **not delete**), Reports |
| **Cashier** | View products, run the POS terminal, view payments, add payments |

New users who register get the **cashier** role automatically.

## 19. Users

The users page lets an admin:

- Add a user (name, email, password, role),
- Edit a user (including changing their role, or setting a new password),
- Delete a user.

Safety rules: you **cannot delete your own account**, and roles are protected (the admin role cannot be deleted).

---

# Part V — The Database

## 20. Tables and what they store

| Table | Content |
|---|---|
| `users` | Login accounts, each with a `role_id` |
| `roles` | Role definitions (name, slug, description) |
| `permissions` | Every grantable action, grouped by module |
| `role_permission` | Links roles to permissions |
| `conversations` | AI chat threads |
| `messages` | Individual chat messages (user or assistant) |
| `products` | Products with price, cost, stock, alert level |
| `sales` | One row per sale (invoice no, totals, status) |
| `sale_items` | Lines of a sale (product, qty, line total) |
| `payments` | Money received (sale, amount, method, reference) |
| `sync_events` | The offline sync queue (Part VII) |
| `sessions` | Login session data |
| `cache`, `jobs`, `password_reset_tokens` | Framework housekeeping |

## 21. Relationships (who belongs to whom)

```
User 1 ────► * Role          (a user has one role)
Role * ────► * Permission    (through role_permission)
User 1 ────► * Conversation  (chat threads)
Conversation 1 ────► * Message
User 1 ────► * Sale          (as cashier)
Sale 1 ────► * SaleItem
Sale 1 ────► * Payment
Product 1 ────► * SaleItem   (referenced by sold lines)
```

- `belongsTo` = "points to one parent" (e.g. a Payment belongs to one Sale)
- `hasMany` = "has many children" (e.g. a Sale has many SaleItems)
- Many-to-many uses pivot tables (`role_permission`)

## 22. Migrations

Migrations are version-controlled instructions for the database. Instead of clicking around in phpMyAdmin, you write:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('sku')->unique();
    $table->decimal('price', 12, 2);
    // ...
});
```

Then `php artisan migrate` applies any not-yet-run migrations. This means every machine ends up with the same database structure.

---

# Part VI — Security

## 23. Layers of protection (already in place)

| Attack | How it is stopped |
|---|---|
| **Cross-Site Request Forgery (CSRF)** | Every form/POST carries a token that is validated; the browser `fetch` calls send `X-CSRF-TOKEN` |
| **Unauthorised access** | `auth` + `verified` middleware; only logged-in users reach protected pages |
| **Permission abuse** | Every controller method checks its specific permission (`abort_unless(…, 403)`) |
| **SQL injection** | All queries go through Eloquent with bound parameters; input is validated |
| **Script injection (XSS)** | Blade escapes all output; a strict Content-Security-Policy is sent |
| **Clickjacking** | `X-Frame-Options: SAMEORIGIN` + CSP `frame-ancestors 'self'` |
| **MIME-sniffing attacks** | `X-Content-Type-Options: nosniff` |
| **Login brute force** | Rate limiter: 5 failed attempts per email+IP with lockout |
| **API/route abuse** | Throttling on POS checkout (10/min) and admin area (60/min) |
| **Stolen session cookie** | `HttpOnly` + `SameSite=Lax`, `Secure` available via config |
| **Mass assignment** | Every model defines exactly which fields may be filled |
| **Weak default accounts** | Seeder generates a random admin password and prints it |
| **Secrets leaked to Git** | `.env` is ignored; keys never travel through the repository |

A dedicated `SecurityHeaders` middleware adds the protective headers to **every** response.

## 24. Things only YOU can do in production

Code alone cannot fix these — they are deployment settings:

1. **Serve over HTTPS** and set `SESSION_SECURE_COOKIE=true`.
2. Set `APP_ENV=production` and `APP_DEBUG=false` (never show error details to visitors).
3. Change the database passwords in `docker-compose.yml` (dev uses `root` / `laravel123`).
4. Use a per-environment OpenAI key (keep it secret, rotate leaked ones).
5. Keep the server and containers updated.

**Recommended next steps (not yet built):** enforce email verification on POS pages, add two-factor authentication for admins, add an audit log of who did what.

---

# Part VII — Offline-First Sync

## 25. The problem this solves

A shop loses its internet connection (or Wi-Fi is weak). With a normal cloud POS, the whole register would stop working. This app works differently:

- **Offline:** every action (sale, payment, product change, new user) is written to the **local** MySQL database immediately. The register keeps working normally.
- **Back online:** the app notices (a scheduler runs every 5 minutes) and pushes everything that happened while offline to the central server.

## 26. How the queue works

Every important change calls the `SyncService`:

```php
$this->sync->record('payment', $payment->id, 'created', $payment->toArray());
```

That inserts a row into `sync_events`:

| Column | Meaning | Example |
|---|---|---|
| `entity` | What kind of thing changed | `payment` |
| `entity_id` | Which record | `42` |
| `action` | What happened | `created` |
| `payload` | The full data in JSON | `{"amount": 10.00, ...}` |
| `synced` | Sent to server yet? | `false` |
| `attempts` | How many tries so far | `0` |
| `last_error` | Why the last attempt failed | `cURL error 7` |

## 27. Pushing when online

The command `php artisan sync:run` does one cycle:

```
For each unsynced event (up to 50):
    try to POST to SYNC_REMOTE_URL
        success → mark as synced ✓
        failure → retry later (up to 5 attempts), remember the error
```

It is scheduled automatically **every 5 minutes** in `routes/console.php`:

```php
Schedule::command('sync:run')->everyFiveMinutes()->withoutOverlapping();
```

You can also run it manually:

```
docker compose exec app php artisan sync:run
```

If no `SYNC_REMOTE_URL` is configured, the scheduler simply does nothing (it is single-machine mode).

---

# Part VIII — Theme & Frontend

## 28. The AdminLTE theme

The POS pages use **AdminLTE 3** — a professional admin dashboard theme (dark sidebar, top navbar, content cards, info boxes, tables).

Unlike many tutorials that load themes from a CDN, the needed files were **downloaded into the project**:

```
public/libs/adminlte/
    css/adminlte.min.css
    js/adminlte.min.js
    vendor/jquery/jquery.min.js
    vendor/bootstrap/css/bootstrap.min.css
    vendor/bootstrap/js/bootstrap.bundle.min.js
```

Why? **Offline support.** With the files stored locally, the dashboard looks the same with or without internet.

The layout that glues it together is `resources/views/layouts/admin.blade.php`. The sidebar menu automatically hides items the user lacks permission to use:

```blade
@if (auth()->user()->hasPermission('reports.view'))
  <li class="nav-item">…Reports…</li>
@endif
```

## 29. Front-end JavaScript

- `public/js/pos.js` — product grid → cart → checkout (uses `fetch`, no framework).
- `public/js/chat.js` — sends chat messages and plays the reply aloud.
- Vite compiles Tailwind CSS for the main app pages.

---

# Part IX — Everyday Operations

## 30. Command cheat-sheet

All of these run inside the app container:

```bash
# start everything
docker compose up -d --build

# see running containers
docker compose ps

# stop everything
docker compose down

# create/update the database tables
docker compose exec app php artisan migrate

# add sample/initial data (roles, products, admin user)
docker compose exec app php artisan db:seed

# do both in one go (for a fresh install)
docker compose exec app php artisan migrate --seed   # prints the admin password

# rebuild front-end assets
docker compose exec app npm install
docker compose exec app npm run build

# push queued offline changes to the central server now
docker compose exec app php artisan sync:run

# see logs
docker compose logs -f app
```

## 31. URLs you will use

| URL | Purpose |
|---|---|
| http://localhost:8003 | The app |
| http://localhost:8003/pos | POS terminal |
| http://localhost:8083 | phpMyAdmin (database browser) |
| http://localhost:8027 | Mailhog (see verification/reset emails) |

## 32. Common troubleshooting

| Problem | Likely fix |
|---|---|
| White page / "Whoops" | Run `docker compose exec app php artisan migrate`; check `.env` exists |
| "Database connection refused" | The `db` container is down — `docker compose up -d db` |
| Login doesn't work | Use a valid account; the seeded admin password was **printed during seeding** |
| 403 Forbidden on POS | Your role lacks that permission — assign permissions in Roles & Permissions |
| 429 Too Many Requests | You hit the rate limit — wait a minute |
| Chat not replying | `OPENAI_API_KEY` missing/wrong in `.env` |
| CSS looks unstyled | Run `npm install && npm run build` (or `npm run dev`) |

---

# Part X — Glossary

| Term | Meaning |
|---|---|
| **Blade** | Laravel's template language for writing pages |
| **Controller** | PHP class that handles requests for a feature |
| **CRUD** | Create, Read, Update, Delete — the four basic data operations |
| **CSRF** | Cross-Site Request Forgery — a fake-form attack, blocked with tokens |
| **Eloquent** | Laravel's way of talking to the database using PHP objects |
| **Middleware** | Code that runs before/after a request — like security checks |
| **Migration** | A script that alters the database structure in a controlled way |
| **Model** | PHP class that represents one database table |
| **Pivot table** | A join table storing many-to-many links (e.g. `role_permission`) |
| **RBAC** | Role-Based Access Control — permissions granted via roles |
| **Seeder** | A script that inserts starter/sample data |
| **Sync event** | A queued record of a change waiting to be sent to the central server |
| **Transaction** | A set of database operations that succeed or fail together |
| **XSS** | Cross-Site Scripting — injecting JavaScript via user input |

---

*End of guide. This document is maintained alongside the code in the repository.*