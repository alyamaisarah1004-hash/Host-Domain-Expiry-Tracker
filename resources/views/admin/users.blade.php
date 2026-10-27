<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Cal Studio Expiry Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
</head>
<body>

<div class="layout">
    {{-----sidebar-----}}
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
            <a href="{{ route('domains.index') }}" class="nav-item {{ request()->routeIs('domains.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Domain</span>
            </a>
            <a href="{{ route('hosting.index') }}" class="nav-item {{ request()->routeIs('hosting.*') ? 'active' : '' }}">
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

    {{-----main area-----}}
    <div class="main-area">

        @include('topbar')

        <main class="main">

            <header class="page-header page-header-row">
                <div>
                    <h1>Users</h1>
                    <p>Manage all user accounts.</p>
                </div>
                <button type="button" class="btn-primary" id="btn-open-add">+ Add User</button>
            </header>

            <section class="panel">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="col-status">Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->user_name }}</td>
                                <td>{{ $user->user_email }}</td>
                                <td>{{ ucfirst($user->user_role) }}</td>
                                <td>
                                    @if ($user->user_role === 'admin')
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <label class="toggle">
                                            <input type="checkbox"
                                                   class="toggle-input"
                                                   data-user-id="{{ $user->user_id }}"
                                                   data-user-name="{{ $user->user_name }}"
                                                   {{ $user->is_active ? 'checked' : '' }}>
                                            <span class="toggle-track">
                                                <span class="toggle-thumb"></span>
                                            </span>
                                            <span class="toggle-label">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    @endif
                                </td>
                                <td class="actions">
                                    @if ($user->user_role !== 'admin')
                                        <button type="button"
                                                class="btn-sm btn-edit"
                                                data-user-id="{{ $user->user_id }}"
                                                data-user-name="{{ $user->user_name }}"
                                                data-user-email="{{ $user->user_email }}"
                                                data-user-role="{{ $user->user_role }}">
                                            Edit
                                        </button>

                                        <button type="button"
                                                class="btn-sm btn-reset"
                                                data-user-id="{{ $user->user_id }}"
                                                data-user-name="{{ $user->user_name }}">
                                            Reset Password
                                        </button>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">No users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

        </main>

    </div>

</div>

{{-----FLOATING TOAST ALERTS-----}}
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

{{-----ADD USER MODAL-----}}
\<div class="modal-overlay {{ $errors->any() && old('_modal') === 'add' ? 'open' : '' }}" id="modal-add">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add User</h3>
            <button type="button" class="modal-close" data-close="modal-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <input type="hidden" name="_modal" value="add">

            <div class="form-group">
                <label for="add_name">Name</label>
                <div class="input-shell">
                    <input type="text" id="add_name" name="user_name"
                           value="{{ old('user_name') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_email">Email</label>
                <div class="input-shell">
                    <input type="email" id="add_email" name="user_email"
                           value="{{ old('user_email') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_password">Password</label>
                <div class="input-shell">
                    <input type="password" id="add_password" name="user_password" required>
                    <button type="button" class="eye-toggle" data-target="add_password">
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
                <small class="hint">Min 8 characters, must contain at least one number.</small>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-add">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-----EDIT USER MODAL-----}}
<div class="modal-overlay {{ $errors->any() && old('_modal') === 'edit' ? 'open' : '' }}" id="modal-edit">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button type="button" class="modal-close" data-close="modal-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" id="form-edit">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit">

            <div class="form-group">
                <label for="edit_name">Name</label>
                <div class="input-shell">
                    <input type="text" id="edit_name" name="user_name"
                           value="{{ old('user_name') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_email">Email</label>
                <div class="input-shell">
                    <input type="email" id="edit_email" name="user_email"
                           value="{{ old('user_email') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_role">Role</label>
                <div class="input-shell">
                    <select id="edit_role" name="user_role">
                        <option value="staff" {{ old('user_role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('user_role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-edit">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-----RESET PASSWORD MODAL-----}}
<div class="modal-overlay {{ $errors->any() && old('_modal') === 'reset' ? 'open' : '' }}" id="modal-reset">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Reset Password</h3>
            <button type="button" class="modal-close" data-close="modal-reset">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <p class="modal-text">
            Set a new password for <strong id="reset_user_name">—</strong>.
        </p>

        <form method="POST" id="form-reset">
            @csrf
            <input type="hidden" name="_modal" value="reset">

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
                <small class="hint">Min 8 characters, must contain at least one number.</small>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm Password</label>
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
                <button type="button" class="btn-secondary" data-close="modal-reset">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-----CONFIRM DEACTIVATE MODAL-----}}
<div class="modal-overlay" id="modal-deactivate">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Deactivate User</h3>
            <button type="button" class="modal-close" data-close="modal-deactivate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <p class="modal-text">
            Are you sure you want to deactivate <strong id="deactivate_user_name">—</strong>?
            They will no longer be able to log in.
        </p>

        <form method="POST" id="form-deactivate">
            @csrf

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-deactivate">Cancel</button>
                <button type="submit" class="btn-danger-solid">Deactivate</button>
            </div>
        </form>
    </div>
</div>

<script>
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

    document.getElementById('btn-open-add').addEventListener('click', () => {
        document.getElementById('add_name').value = '';
        document.getElementById('add_email').value = '';
        document.getElementById('add_password').value = '';
        openModal('modal-add');
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const editName = document.getElementById('edit_name');
            if (!editName.value) {
                editName.value  = btn.dataset.userName;
                document.getElementById('edit_email').value = btn.dataset.userEmail;
                document.getElementById('edit_role').value  = btn.dataset.userRole;
            }
            document.getElementById('form-edit').action = '/admin/users/' + btn.dataset.userId;
            openModal('modal-edit');
        });
    });

    document.querySelectorAll('.btn-reset').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('reset_user_name').textContent = btn.dataset.userName;
            document.getElementById('new_password').value = '';
            document.getElementById('new_password_confirmation').value = '';
            document.getElementById('form-reset').action =
                '/admin/users/' + btn.dataset.userId + '/reset-password';
            openModal('modal-reset');
        });
    });

    document.querySelectorAll('.toggle-input').forEach(input => {
        input.addEventListener('change', () => {
            const userId   = input.dataset.userId;
            const userName = input.dataset.userName;

            if (input.checked) {
                submitToggle(userId);
            } else {
                document.getElementById('deactivate_user_name').textContent = userName;
                document.getElementById('form-deactivate').action =
                    '/admin/users/' + userId + '/toggle-active';
                input.checked = true;
                openModal('modal-deactivate');
            }
        });
    });

    function submitToggle(userId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/users/' + userId + '/toggle-active';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

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

    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.getElementById('modal-edit');
        if (editModal && editModal.classList.contains('open')) {
            const match = window.location.pathname.match(/\/admin\/users\/(\d+)/);
            if (match) {
                document.getElementById('form-edit').action = '/admin/users/' + match[1];
            }
        }
    });
</script>

</body>
</html>