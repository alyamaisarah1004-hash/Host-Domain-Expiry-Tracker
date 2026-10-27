<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Cal Studio Expiry Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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

        {{-----Topbar (bell &profile)-----}}
        @include('topbar')
        <main class="main">

            <header class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, {{ auth()->user()->user_name }}.</p>
            </header>

            <section class="stats-grid">

                <div class="stat-card">
                    <span class="stat-label">Total Domains</span>
                    <span class="stat-value">{{ $stats['total_domains'] }}</span>
                </div>

                <div class="stat-card">
                    <span class="stat-label">Total Hosting</span>
                    <span class="stat-value">{{ $stats['total_hosting'] }}</span>
                </div>

                <div class="stat-card warn">
                    <span class="stat-label">Expiring Soon (30 days)</span>
                    <span class="stat-value">{{ $stats['expiring_soon'] }}</span>
                </div>

                <div class="stat-card danger">
                    <span class="stat-label">Expired</span>
                    <span class="stat-value">{{ $stats['expired'] }}</span>
                </div>

            </section>

            <section class="panels">

                <div class="panel">
                    <h3>Expiring Soon</h3>

                    @if ($expiringSoon->isEmpty())
                        <p class="empty">Nothing expiring in the next 30 days.</p>
                    @else
                        <ul class="alert-list">
                            @foreach ($expiringSoon as $item)
                                <li class="alert-item">
                                    <div class="alert-info">
                                        <a href="{{ $item['url'] }}" class="alert-name">
                                            {{ $item['name'] }}
                                        </a>
                                        <span class="alert-meta">
                                            {{ $item['type'] }} · expires {{ $item['expiry_date']->format('d M Y') }}
                                        </span>
                                    </div>
                                    <span class="days-badge days-{{ $item['status'] }}">
                                        @if ($item['days_remaining'] === 0)
                                            Today
                                        @else
                                            {{ $item['days_remaining'] }} days
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="panel">
                    <h3>Already Expired</h3>

                    @if ($expired->isEmpty())
                        <p class="empty">Nothing expired.</p>
                    @else
                        <ul class="alert-list">
                            @foreach ($expired as $item)
                                <li class="alert-item">
                                    <div class="alert-info">
                                        <a href="{{ $item['url'] }}" class="alert-name">
                                            {{ $item['name'] }}
                                        </a>
                                        <span class="alert-meta">
                                            {{ $item['type'] }} · expired {{ $item['expiry_date']->format('d M Y') }}
                                        </span>
                                    </div>
                                    <span class="days-badge days-{{ $item['status'] }}">
                                        {{ abs($item['days_remaining']) }} days ago
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            </section>

        </main>

    </div>

</div>

</body>
</html>