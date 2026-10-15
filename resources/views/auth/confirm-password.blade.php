<x-guest-layout>
    <p class="text-muted small mb-4">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn btn-auth w-100 py-2 fw-semibold rounded-3">{{ __('Confirm') }}</button>
    </form>
</x-guest-layout>