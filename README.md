# AI-hub

An AI productivity chat application built with Laravel 12, with an integrated Point-of-Sale (POS) module.

## Features

### AI Chat
- User authentication (register / login)
- Conversation history with a sidebar
- Chat with OpenAI (gpt-4o-mini)
- Voice reply playback

### POS
- POS terminal (click product -> cart -> checkout, prints receipt)
- Products CRUD with stock and low-stock alerts
- Payments, where **add / edit / delete are three separate permissions** on a role
- Sales history and reports (daily sales, top products, payments by method, low stock)
- Roles & Permissions manager
- Users manager (assign roles)
- AdminLTE 3 theme, bundled locally so it works with no internet

### Offline-first sync
- Every product, sale and payment change is written to the local DB immediately (works with no wifi)
- Changes are queued in a `sync_events` table
- When wifi is back, `php artisan sync:run` (scheduled every 5 minutes) pushes the queued events to `SYNC_REMOTE_URL` and marks them synced

## Requirements

- Docker Desktop
- OpenAI API key (set `OPENAI_API_KEY` in `.env`)

## Setup

1. Copy the environment file and add your OpenAI key:

   ```
   cp .env.example .env
   # then set OPENAI_API_KEY in .env
   ```

2. Build and start the containers:

   ```
   docker compose up -d --build
   ```

3. Run migrations and seeders inside the app container:

   ```
   docker compose exec app php artisan migrate --seed
   ```

   This creates the roles (`admin`, `manager`, `cashier`), their permissions (payments are split into Add / Edit / Delete), sample products, and an admin account `admin@example.com` — the seeder prints a randomly generated password.
   Default login for a freshly seeded DB is `admin@example.com` / the password shown in the `db:seed` output (never `password`).

4. Build front-end assets:

   ```
   docker compose exec app npm install
   docker compose exec app npm run build
   ```

5. Open the POS at http://localhost:8003/pos

## Offline sync setup

- Set `SYNC_REMOTE_URL` to the endpoint of your central server that should receive the queued events, and `SYNC_TOKEN` to a shared bearer token.
- The remote endpoint should accept a POST with `{ entity, entity_id, action, payload }` and return 2xx to confirm receipt.
- Offline, the app runs fully on the local database. Queued items are pushed automatically every 5 minutes once online, or manually with:

  ```
  docker compose exec app php artisan sync:run
  ```

## Services / Ports

| Service      | URL                       |
|--------------|---------------------------|
| App (nginx)  | http://localhost:8003     |
| phpMyAdmin   | http://localhost:8083     |
| Mailhog UI   | http://localhost:8027     |
| MySQL        | localhost:3308            |