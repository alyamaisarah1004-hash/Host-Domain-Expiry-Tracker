<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domains — Cal Studio Expiry Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/domains.css') }}">
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

        {{-----Topbar-----}}
        @include('topbar')
        <main class="main">

            <header class="page-header page-header-row">
                <div>
                    <h1>Domains</h1>
                    <p>Track all domain names and their expiry dates.</p>
                </div>
                <button type="button" class="btn-primary" id="btn-open-add">+ Add Domain</button>
            </header>

            {{-----FILTERS-----}}
            <section class="filters-bar">

                <div class="status-tabs">
                    <a href="{{ route('domains.index', array_merge(request()->only('search'), ['status' => null])) }}"
                       class="status-tab {{ !request('status') ? 'active' : '' }}">
                        All <span class="tab-count">{{ $counts['all'] }}</span>
                    </a>
                    <a href="{{ route('domains.index', array_merge(request()->only('search'), ['status' => 'active'])) }}"
                       class="status-tab {{ request('status') === 'active' ? 'active' : '' }}">
                        <span class="dot dot-green"></span>
                        Active <span class="tab-count">{{ $counts['active'] }}</span>
                    </a>
                    <a href="{{ route('domains.index', array_merge(request()->only('search'), ['status' => 'month'])) }}"
                       class="status-tab {{ request('status') === 'month' ? 'active' : '' }}">
                        <span class="dot dot-yellow"></span>
                        Expiring This Month <span class="tab-count">{{ $counts['month'] }}</span>
                    </a>
                    <a href="{{ route('domains.index', array_merge(request()->only('search'), ['status' => 'week'])) }}"
                       class="status-tab {{ request('status') === 'week' ? 'active' : '' }}">
                        <span class="dot dot-red"></span>
                        Expiring This Week <span class="tab-count">{{ $counts['week'] }}</span>
                    </a>
                    <a href="{{ route('domains.index', array_merge(request()->only('search'), ['status' => 'expired'])) }}"
                       class="status-tab {{ request('status') === 'expired' ? 'active' : '' }}">
                        <span class="dot dot-grey"></span>
                        Expired <span class="tab-count">{{ $counts['expired'] }}</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('domains.index') }}" class="search-form">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="search-shell">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text"
                               name="search"
                               placeholder="Search domain, website, or provider..."
                               value="{{ request('search') }}">
                        @if (request('search'))
                            <a href="{{ route('domains.index', request()->only('status')) }}" class="search-clear" title="Clear search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </form>

            </section>

            <section class="panel">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Website</th>
                            <th>Provider</th>
                            <th>Expiry Date</th>
                            <th class="col-days">Days Left</th>
                            <th>Cost</th>
                            <th>Auto-Renew</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($domains as $domain)
                            <tr>
                                <td><strong>{{ $domain->domain_name }}</strong></td>
                                <td>
                                    @php
                                        $url = $domain->website_url;
                                        $href = preg_match('#^https?://#i', $url) ? $url : 'https://' . $url;
                                    @endphp
                                    <a href="{{ $href }}" target="_blank" rel="noopener" class="link">
                                        {{ $domain->website_url }}
                                    </a>
                                </td>
                                <td>{{ $domain->domain_provider }}</td>
                                <td>{{ $domain->expiry_date->format('d M Y') }}</td>
                                <td>
                                    <span class="days-badge days-{{ $domain->status }}">
                                        @if ($domain->days_remaining < 0)
                                            Expired {{ abs($domain->days_remaining) }}d ago
                                        @elseif ($domain->days_remaining === 0)
                                            Today
                                        @else
                                            {{ $domain->days_remaining }} days
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if ($domain->cost)
                                        RM {{ number_format($domain->cost, 2) }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($domain->auto_renew)
                                        <span class="badge badge-active">On</span>
                                    @else
                                        <span class="badge badge-inactive">Off</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <button type="button"
                                            class="btn-sm btn-view"
                                            data-domain-id="{{ $domain->domain_id }}"
                                            data-domain-name="{{ $domain->domain_name }}"
                                            data-website-url="{{ $domain->website_url }}"
                                            data-domain-provider="{{ $domain->domain_provider }}"
                                            data-expiry-date="{{ $domain->expiry_date->format('d M Y') }}"
                                            data-days-remaining="{{ $domain->days_remaining }}"
                                            data-status="{{ $domain->status }}"
                                            data-cost="{{ $domain->cost }}"
                                            data-auto-renew="{{ $domain->auto_renew ? '1' : '0' }}"
                                            data-notes="{{ $domain->notes }}"
                                            data-created-by="{{ $domain->creator->user_name ?? 'Unknown' }}"
                                            data-created-by-email="{{ $domain->creator->user_email ?? '—' }}"
                                            data-created-at="{{ $domain->created_at->format('d M Y, H:i') }}">
                                        View
                                    </button>

                                    <button type="button"
                                            class="btn-sm btn-edit"
                                            data-domain-id="{{ $domain->domain_id }}"
                                            data-domain-name="{{ $domain->domain_name }}"
                                            data-website-url="{{ $domain->website_url }}"
                                            data-domain-provider="{{ $domain->domain_provider }}"
                                            data-expiry-date="{{ $domain->expiry_date->format('Y-m-d') }}"
                                            data-cost="{{ $domain->cost }}"
                                            data-auto-renew="{{ $domain->auto_renew ? '1' : '0' }}"
                                            data-notes="{{ $domain->notes }}">
                                        Edit
                                    </button>

                                    <button type="button"
                                            class="btn-sm btn-danger btn-delete"
                                            data-domain-id="{{ $domain->domain_id }}"
                                            data-domain-name="{{ $domain->domain_name }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty">
                                    @if (request('status') || request('search'))
                                        No domains match your filters.
                                    @else
                                        No domains yet. Click "+ Add Domain" to get started.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

{{-----VIEW DOMAIN MODAL-----}}
<div class="modal-overlay" id="modal-view">
    <div class="modal-box modal-box-wide">
        <div class="modal-header">
            <h3>Domain Details</h3>
            <button type="button" class="modal-close" data-close="modal-view">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Domain Name</span>
                <span class="detail-value" id="view_domain_name">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Website</span>
                <span class="detail-value">
                    <a id="view_website_url" href="#" target="_blank" rel="noopener" class="link">—</a>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Provider</span>
                <span class="detail-value" id="view_domain_provider">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Expiry Date</span>
                <span class="detail-value" id="view_expiry_date">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Days Left</span>
                <span class="detail-value">
                    <span class="days-badge" id="view_days_badge">—</span>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Cost</span>
                <span class="detail-value" id="view_cost">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Auto-Renew</span>
                <span class="detail-value" id="view_auto_renew">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Added By</span>
                <span class="detail-value" id="view_created_by">—</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Added On</span>
                <span class="detail-value" id="view_created_at">—</span>
            </div>
            <div class="detail-item detail-item-full">
                <span class="detail-label">Notes</span>
                <span class="detail-value detail-notes" id="view_notes">—</span>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" data-close="modal-view">Close</button>
        </div>
    </div>
</div>

{{-----ADD DOMAIN MODAL-----}}
<div class="modal-overlay {{ $errors->any() && old('_modal') === 'add' ? 'open' : '' }}" id="modal-add">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Domain</h3>
            <button type="button" class="modal-close" data-close="modal-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('domains.store') }}">
            @csrf
            <input type="hidden" name="_modal" value="add">

            <div class="form-group">
                <label for="add_domain_name">Domain Name</label>
                <div class="input-shell">
                    <input type="text" id="add_domain_name" name="domain_name"
                           value="{{ old('domain_name') }}" placeholder="calstudio.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_website_url">Website URL</label>
                <div class="input-shell">
                    <input type="text" id="add_website_url" name="website_url"
                           value="{{ old('website_url') }}" placeholder="https://calstudio.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_domain_provider">Provider</label>
                <div class="input-shell">
                    <input type="text" id="add_domain_provider" name="domain_provider"
                           value="{{ old('domain_provider') }}" placeholder="Exabytes" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_expiry_date">Expiry Date</label>
                <div class="input-shell">
                    <input type="date" id="add_expiry_date" name="expiry_date"
                           value="{{ old('expiry_date') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add_cost">Cost (MYR) — Optional</label>
                <div class="input-shell">
                    <input type="number" id="add_cost" name="cost" step="0.01" min="0"
                           value="{{ old('cost') }}" placeholder="60.00">
                </div>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="add_auto_renew" name="auto_renew" value="1"
                           {{ old('auto_renew') ? 'checked' : '' }}>
                    <span>Auto-renew enabled</span>
                </label>
            </div>

            <div class="form-group">
                <label for="add_notes">Notes — Optional</label>
                <div class="input-shell">
                    <textarea id="add_notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-add">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-----EDIT DOMAIN MODAL-----}}
<div class="modal-overlay {{ $errors->any() && old('_modal') === 'edit' ? 'open' : '' }}" id="modal-edit">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Domain</h3>
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
                <label for="edit_domain_name">Domain Name</label>
                <div class="input-shell">
                    <input type="text" id="edit_domain_name" name="domain_name"
                           value="{{ old('domain_name') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_website_url">Website URL</label>
                <div class="input-shell">
                    <input type="text" id="edit_website_url" name="website_url"
                           value="{{ old('website_url') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_domain_provider">Provider</label>
                <div class="input-shell">
                    <input type="text" id="edit_domain_provider" name="domain_provider"
                           value="{{ old('domain_provider') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_expiry_date">Expiry Date</label>
                <div class="input-shell">
                    <input type="date" id="edit_expiry_date" name="expiry_date"
                           value="{{ old('expiry_date') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_cost">Cost (MYR) — Optional</label>
                <div class="input-shell">
                    <input type="number" id="edit_cost" name="cost" step="0.01" min="0"
                           value="{{ old('cost') }}">
                </div>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="edit_auto_renew" name="auto_renew" value="1"
                           {{ old('auto_renew') ? 'checked' : '' }}>
                    <span>Auto-renew enabled</span>
                </label>
            </div>

            <div class="form-group">
                <label for="edit_notes">Notes — Optional</label>
                <div class="input-shell">
                    <textarea id="edit_notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-edit">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-----CONFIRM DELETE MODAL-----}}
<div class="modal-overlay" id="modal-delete">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Delete Domain</h3>
            <button type="button" class="modal-close" data-close="modal-delete">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <p class="modal-text">
            Are you sure you want to delete <strong id="delete_domain_name">—</strong>?
            This cannot be undone.
        </p>

        <form method="POST" id="form-delete">
            @csrf
            @method('DELETE')

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-close="modal-delete">Cancel</button>
                <button type="submit" class="btn-danger-solid">Delete</button>
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
        openModal('modal-add');
    });

    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('view_domain_name').textContent = btn.dataset.domainName;

            const url = btn.dataset.websiteUrl;
            const href = /^https?:\/\//i.test(url) ? url : 'https://' + url;
            const linkEl = document.getElementById('view_website_url');
            linkEl.textContent = url;
            linkEl.href = href;

            document.getElementById('view_domain_provider').textContent = btn.dataset.domainProvider;
            document.getElementById('view_expiry_date').textContent     = btn.dataset.expiryDate;

            const days = parseInt(btn.dataset.daysRemaining);
            const status = btn.dataset.status;
            const badge = document.getElementById('view_days_badge');
            badge.className = 'days-badge days-' + status;
            if (days < 0) {
                badge.textContent = 'Expired ' + Math.abs(days) + ' days ago';
            } else if (days === 0) {
                badge.textContent = 'Today';
            } else {
                badge.textContent = days + ' days';
            }

            const cost = btn.dataset.cost;
            document.getElementById('view_cost').textContent = cost ? 'RM ' + parseFloat(cost).toFixed(2) : '—';
            document.getElementById('view_auto_renew').textContent = btn.dataset.autoRenew === '1' ? 'Yes' : 'No';
            document.getElementById('view_notes').textContent = btn.dataset.notes || '—';
            document.getElementById('view_created_by').textContent = btn.dataset.createdBy + ' (' + btn.dataset.createdByEmail + ')';
            document.getElementById('view_created_at').textContent = btn.dataset.createdAt;

            openModal('modal-view');
        });
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const nameField = document.getElementById('edit_domain_name');
            if (!nameField.value) {
                nameField.value = btn.dataset.domainName;
                document.getElementById('edit_website_url').value     = btn.dataset.websiteUrl;
                document.getElementById('edit_domain_provider').value = btn.dataset.domainProvider;
                document.getElementById('edit_expiry_date').value     = btn.dataset.expiryDate;
                document.getElementById('edit_cost').value            = btn.dataset.cost || '';
                document.getElementById('edit_auto_renew').checked    = btn.dataset.autoRenew === '1';
                document.getElementById('edit_notes').value           = btn.dataset.notes || '';
            }
            document.getElementById('form-edit').action = '/domains/' + btn.dataset.domainId;
            openModal('modal-edit');
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('delete_domain_name').textContent = btn.dataset.domainName;
            document.getElementById('form-delete').action = '/domains/' + btn.dataset.domainId;
            openModal('modal-delete');
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
            const match = window.location.pathname.match(/\/domains\/(\d+)/);
            if (match) {
                document.getElementById('form-edit').action = '/domains/' + match[1];
            }
        }
    });
</script>

</body>
</html>