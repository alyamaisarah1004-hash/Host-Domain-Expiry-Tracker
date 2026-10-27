<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cal Studio Expiry Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-brand">
            <h1>Cal Studio</h1>
            <p>Expiry Tracker</p>
        </div>

        {{-----GENERAL LOGIN ERROR-----}}
        @if ($errors->has('login'))
            <div class="toast toast-error" id="login-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="form-group">
                <label for="user_email">Email</label>
                <div class="input-shell">
                    <input type="email"
                           id="user_email"
                           name="user_email"
                           value="{{ old('user_email') }}"
                           placeholder="you@calstudio.com"
                           required
                           autofocus>
                </div>
                @error('user_email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="user_password">Password</label>
                <div class="input-shell">
                    <input type="password"
                           id="user_password"
                           name="user_password"
                           placeholder="pass1234"
                           required>

                    <button type="button"
                            class="eye-toggle"
                            id="eye-toggle"
                            data-target="user_password"
                            aria-label="Show password">

                        <svg class="eye-open"
                             xmlns="http://www.w3.org/2000/svg"
                             width="18" height="18"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        <svg class="eye-closed"
                             xmlns="http://www.w3.org/2000/svg"
                             width="18" height="18"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>

                    </button>
                </div>
                @error('user_password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>

    </div>
</div>

<script>
    // Eye toggle
    document.querySelectorAll('.eye-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;

            const openSvg   = btn.querySelector('.eye-open');
            const closedSvg = btn.querySelector('.eye-closed');

            const showing = target.type === 'text';
            target.type = showing ? 'password' : 'text';

            openSvg.style.display   = showing ? 'block' : 'none';
            closedSvg.style.display = showing ? 'none'  : 'block';
        });
    });

    document.querySelectorAll('.toast').forEach(toast => {
        setTimeout(() => {
            toast.classList.add('toast-hide');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    });
</script>

</body>
</html>