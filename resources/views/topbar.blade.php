{{-----TOPBAR (Bell & Profile icon)-----}}
<div class="topbar">

    <div class="topbar-right">

        {{-----BELL-----}}
        <div class="bell-wrapper">
            <button type="button" class="bell-btn" id="bell-btn" aria-label="Reminders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                @if (isset($unreadCount) && $unreadCount > 0)
                    <span class="bell-dot"></span>
                @endif
            </button>

            {{-- Dropdown --}}
            <div class="bell-dropdown" id="bell-dropdown">

                <div class="bell-dropdown-header">
                    Reminders
                </div>

                @if (isset($bellItems) && count($bellItems) > 0)
                    <ul class="bell-list">
                        @foreach ($bellItems as $item)
                            <li>
                                <a href="{{ $item['url'] }}"
                                   class="bell-item {{ !empty($item['read']) ? 'read' : '' }}">
                                    <div class="bell-item-info">
                                        <span class="bell-item-name">{{ $item['name'] }}</span>
                                        <span class="bell-item-meta">
                                            {{ $item['type'] }} · {{ $item['expiry_date']->format('d M Y') }}
                                        </span>
                                    </div>
                                    <span class="days-badge days-{{ $item['status'] }}">
                                        @if ($item['days_remaining'] < 0)
                                            Expired
                                        @elseif ($item['days_remaining'] === 0)
                                            Today
                                        @else
                                            {{ $item['days_remaining'] }}d
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="bell-empty">
                        No reminder
                    </div>
                @endif

            </div>
        </div>

        {{------PROFILE-----}}
        <a href="{{ route('profile.show') }}" class="avatar" title="My Profile">
            {{ strtoupper(substr(auth()->user()->user_name, 0, 1)) }}
        </a>

    </div>

</div>

<script>
    // ---------- Bell dropdown toggle ----------
    (function () {
        const btn      = document.getElementById('bell-btn');
        const dropdown = document.getElementById('bell-dropdown');

        if (!btn || !dropdown) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdown.classList.remove('open');
            }
        });
    })();
</script>