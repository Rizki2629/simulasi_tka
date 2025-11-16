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
    @if(isset($showAvatar) && $showAvatar)
    <div class="header-right">
        <div class="user-avatar-header">{{ $avatarInitials ?? 'FS' }}</div>
    </div>
    @elseif(isset($showSearch) && $showSearch)
    <div class="header-right">
        <div class="header-icon">
            <span class="material-symbols-outlined">person</span>
        </div>
        <div class="header-icon">
            <span class="material-symbols-outlined">notifications</span>
            <span class="badge"></span>
        </div>
    </div>
    @endif
</header>
