<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label fw-medium">{{ __('Name') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input id="name" type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    name="name" value="{{ old('name') }}" required autofocus
                    autocomplete="name" placeholder="John Doe">
            </div>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-medium">{{ __('Email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    name="email" value="{{ old('email') }}" required
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
                    name="password" required autocomplete="new-password"
                    placeholder="At least 8 characters">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-medium">{{ __('Confirm Password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                <input id="password_confirmation" type="password"
                    class="form-control" name="password_confirmation" required
                    autocomplete="new-password" placeholder="Repeat your password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-auth w-100 py-2 fw-semibold rounded-3">{{ __('Create account') }}</button>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="link-soft">{{ __('Log in') }}</a>
    </p>
</x-guest-layout>