<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AI-Hub') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style>
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 24px;
                background:
                    radial-gradient(1200px 600px at 100% -10%, #312e81 0%, transparent 55%),
                    radial-gradient(1000px 520px at -10% 110%, #0ea5e9 0%, transparent 50%),
                    linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #172554 100%);
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            }

            .auth-blob {
                position: fixed;
                border-radius: 50%;
                filter: blur(90px);
                opacity: 0.45;
                z-index: 0;
                pointer-events: none;
            }

            .brand-mark {
                width: 58px;
                height: 58px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                color: #fff;
                font-weight: 800;
                font-size: 1.35rem;
                letter-spacing: 0.02em;
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
            }

            .auth-card {
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 1.25rem;
                background: rgba(255, 255, 255, 0.97);
                box-shadow: 0 25px 60px -15px rgba(2, 6, 23, 0.6);
                width: 100%;
                max-width: 440px;
            }

            .btn-auth {
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                color: #fff;
                border: 0;
                transition: filter .15s ease, transform .1s ease;
            }
            .btn-auth:hover, .btn-auth:focus {
                color: #fff;
                filter: brightness(1.1);
            }
            .btn-auth:active {
                transform: translateY(1px);
            }

            .form-control:focus,
            .form-check-input:focus {
                border-color: #7c3aed;
                box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.15);
            }

            .input-group-text {
                background: #f8fafc;
            }

            a.link-soft {
                color: #4f46e5;
                text-decoration: none;
                font-weight: 600;
            }
            a.link-soft:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="auth-blob" style="width: 340px; height: 340px; background: #6366f1; top: -80px; right: -60px;"></div>
        <div class="auth-blob" style="width: 300px; height: 300px; background: #06b6d4; bottom: -70px; left: -50px;"></div>

        <div class="card auth-card position-relative">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <span class="brand-mark mb-3">AI</span>
                    <h1 class="h4 fw-bold mb-1 text-dark">{{ config('app.name', 'AI-Hub') }}</h1>
                    <p class="text-muted small mb-0">Your AI productivity &amp; POS hub</p>
                </div>

                {{ $slot }}
            </div>

            <div class="card-footer text-center bg-transparent border-0 pt-0 pb-4">
                <small class="text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'AI-Hub') }}. All rights reserved.</small>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            function togglePassword(inputId, btn) {
                const input = document.getElementById(inputId);
                const icon = btn.querySelector('i');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('bi-eye-slash', show);
                icon.classList.toggle('bi-eye', !show);
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            }
        </script>
    </body>
</html>