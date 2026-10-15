<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-medium">{{ __('Email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username" placeholder="you@example.com">
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-medium">{{ __('Password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input id="password" type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    name="password" required autocomplete="current-password"
                    placeholder="Enter your password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label small text-muted" for="remember_me">{{ __('Remember me') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a class="small link-soft" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <button type="submit" class="btn btn-auth w-100 py-2 fw-semibold rounded-3">{{ __('Log in') }}</button>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        {{ __("Don't have an account?") }}
        <a href="{{ route('register') }}" class="link-soft">{{ __('Create one') }}</a>
    </p>
</x-guest-layout>