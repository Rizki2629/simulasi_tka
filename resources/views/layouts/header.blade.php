<!-- Header -->
<header class="header">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <span class="material-symbols-outlined">menu</span>
        </button>
        @if(isset($showSearch) && $showSearch)
        <div class="search-bar">
            <span class="material-symbols-outlined">search</span>
            <input type="text" placeholder="Search for track, artist or album...">
        </div>
        @else
        <div>
            @if(isset($breadcrumb))
                <div style="font-size: 12px; color: #999; margin-bottom: 4px;">{{ $breadcrumb }}</div>
            @endif
            <div style="font-size: 16px; font-weight: 600; color: #333;">{{ $pageTitle ?? 'Dashboard' }}</div>
        </div>
        @endif
    </div>
    @php
        $showHeaderRight = (isset($showAvatar) && $showAvatar) || (isset($showSearch) && $showSearch);
        $isAuthenticated = auth()->check();
    @endphp
    @if($showHeaderRight || $isAuthenticated)
        <div class="header-right">
            @if(isset($showAvatar) && $showAvatar)
                <div class="user-avatar-header">{{ $avatarInitials ?? 'FS' }}</div>
            @elseif(isset($showSearch) && $showSearch)
                <div class="header-icon">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div class="header-icon">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="badge"></span>
                </div>
            @endif

            @auth
                <a href="#" class="header-icon" onclick="confirmAdminLogout(event)" aria-label="Logout">
                    <span class="material-symbols-outlined">logout</span>
                </a>

                <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth
        </div>
    @endif
</header>

@auth
    <script>
        function confirmAdminLogout(event) {
            event.preventDefault();

            if (confirm('Apakah Anda yakin ingin logout?')) {
                document.getElementById('admin-logout-form')?.submit();
            }
        }
    </script>
@endauth
