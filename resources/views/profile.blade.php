<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Cal Studio Expiry Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>

<div class="layout">

    {{-----SIDEBAR-----}}
    <aside class="sidebar">

        <div class="brand">
            <h2>Cal Studio</h2>
            <span>Expiry Tracker</span>
        </div>

        <nav class="nav">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('domains.index') }}"
               class="nav-item {{ request()->routeIs('domains.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Domain</span>
            </a>
            <a href="{{ route('hosting.index') }}"
               class="nav-item {{ request()->routeIs('hosting.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>Hosting</span>
            </a>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                   class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>User</span>
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <strong>{{ auth()->user()->user_name }}</strong>
                <span>{{ ucfirst(auth()->user()->user_role) }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Log Out</button>
            </form>
        </div>
    </aside>

    {{-----MAIN AREA-----}}
    <div class="main-area">

        @include('topbar')

        <main class="main">

            <header class="page-header page-header-row">
                <div>
                    <h1>My Profile</h1>
                    <p>Manage your account information.</p>
                </div>
                <button type="button" class="btn-primary" id="btn-open-edit">Edit</button>
            </header>

            <section class="panel">
                <div class="profile-card">

                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->user_name, 0, 1)) }}
                    </div>

                    <div class="profile-divider"></div>

                    <div class="profile-details">

                        <div class="profile-row">
                            <span class="profile-label">Name</span>
                            <span class="profile-value">{{ $user->user_name }}</span>
                        </div>

                        <div class="profile-row">
                            <span class="profile-label">Email</span>
                            <span class="profile-value">{{ $user->user_email }}</span>
                        </div>

                        <div class="profile-row">
                            <span class="profile-label">Role</span>
                            <span class="profile-value">{{ ucfirst($user->user_role) }}</span>
                        </div>

                    </div>

                </div>

                <div class="profile-actions">
                    <button type="button" class="btn-secondary" id="btn-open-password">Change Password</button>
                </div>

            </section>

        </main>

    </div>

</div>

{{-----TOAST ALERTS-----}}
@if (session('success'))
    <div class="toast toast-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="toast toast-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="toast toast-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

{{-----EDIT PROFILE MODAL-----}}
<div class="modal-overlay {{ $errors->has('user_name') || $errors->has('user_email') ? 'open' : '' }}" id="modal-edit">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Profile</h3>
            <button type="button" class="modal-close" data-close="modal-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="edit_name">Name</label>
                <div class="input-shell">
                    <input type="text" id="edit_name" name="user_name"
                           value="{{ old('user_name', $user->user_name) }}" required>
                </div>
                @error('user_name')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="edit_email">Email</label>
                <div class="input-shell">
                    <input type="email" id="edit_email" name="user_email"
                           value="{{ old('user_email', $user->user_email) }}" required>
                </div>
                @error('user_email')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="edit_role">Role</label>
                <div class="input-shell">
                    <input type="text" id="edit_role"
                           value="{{ ucfirst($user->user_role) }}" disabled>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-edit">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-----CHANGE PASSWORD MODAL-----}}
<div class="modal-overlay {{ $errors->has('current_password') || $errors->has('new_password') ? 'open' : '' }}" id="modal-password">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button type="button" class="modal-close" data-close="modal-password">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('profile.update-password') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="input-shell">
                    <input type="password" id="current_password" name="current_password" required>
                    <button type="button" class="eye-toggle" data-target="current_password">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="input-shell">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="eye-toggle" data-target="new_password">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('new_password')
                    <small class="field-error">{{ $message }}</small>
                @enderror
                <small class="hint">Min 8 characters, must contain at least one number.</small>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <div class="input-shell">
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
                    <button type="button" class="eye-toggle" data-target="new_password_confirmation">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-password">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ---------- Modal helpers ----------
    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.close));
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    // ---------- Open modals ----------
    document.getElementById('btn-open-edit').addEventListener('click', () => {
        openModal('modal-edit');
    });

    document.getElementById('btn-open-password').addEventListener('click', () => {
        openModal('modal-password');
    });

    // ---------- Eye toggle ----------
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

    // ---------- Toast auto-fade ----------
    document.querySelectorAll('.toast').forEach(toast => {
        setTimeout(() => {
            toast.classList.add('toast-hide');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    });
</script>

</body>
</html>