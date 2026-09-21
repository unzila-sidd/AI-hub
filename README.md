# AI-hub

An AI productivity chat application built with Laravel 12, integrated with the OpenAI API.

## Features

- User authentication (register / login)
- Conversation history with a sidebar
- Chat with OpenAI (gpt-4o-mini)
- Voice reply playback
- Dockerized environment (app, nginx, MySQL, phpMyAdmin, Mailhog)

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

3. Run migrations inside the app container:

   ```
   docker compose exec app php artisan migrate
   docker compose exec app php artisan key:generate
   docker compose exec app npm install
   docker compose exec app npm run build
   ```

4. Open the app at http://localhost:8003

## Services / Ports

| Service      | URL                       |
|--------------|---------------------------|
| App (nginx)  | http://localhost:8003     |
| phpMyAdmin   | http://localhost:8083     |
| Mailhog UI   | http://localhost:8027     |
| MySQL        | localhost:3308            |