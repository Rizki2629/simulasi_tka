<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <span class="material-symbols-outlined">school</span>
            </div>
            <div class="logo-text">SIMULASI TKA - SDN GU 09</div>
        </div>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-section-title">Menu</div>
            <a href="/dashboard" class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="menu-item-text">Dashboard</span>
            </a>
            <a href="/users" class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                <span class="material-symbols-outlined">group</span>
                <span class="menu-item-text">User Management</span>
            </a>
            <div class="menu-item {{ request()->is('soal*') || request()->is('simulasi*') ? 'expanded' : '' }}" onclick="toggleSubmenu(event)">
                <span class="material-symbols-outlined">quiz</span>
                <span class="menu-item-text">Simulasi TKA</span>
                <span class="material-symbols-outlined menu-item-arrow" style="font-size: 18px;">expand_more</span>
            </div>
            <div class="submenu {{ request()->is('soal*') || request()->is('simulasi*') ? 'expanded' : '' }}">
                <a href="/soal/create" class="submenu-item {{ request()->is('soal/create') ? 'active' : '' }}">
                    <span class="menu-item-text">Buat Soal</span>
                </a>
                <a href="/soal" class="submenu-item {{ request()->is('soal') && !request()->is('soal/create') ? 'active' : '' }}">
                    <span class="menu-item-text">Daftar Soal</span>
                </a>
                <a href="/simulasi/generate" class="submenu-item {{ request()->is('simulasi/generate') ? 'active' : '' }}">
                    <span class="menu-item-text">Generate Simulasi</span>
                </a>
                <a href="/simulasi/token" class="submenu-item {{ request()->is('simulasi/token') ? 'active' : '' }}">
                    <span class="menu-item-text">Generate Token</span>
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Help</div>
            <a href="#" class="menu-item">
                <span class="material-symbols-outlined">settings</span>
                <span class="menu-item-text">Setting</span>
            </a>
            <a href="#" class="menu-item">
                <span class="material-symbols-outlined">help</span>
                <span class="menu-item-text">Support</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">MD</div>
            <div class="user-info">
                <div class="user-name">M A S - D I O</div>
                <div class="user-role">Admin</div>
            </div>
            <span class="material-symbols-outlined" style="font-size: 20px; color: rgba(255,255,255,0.6);">expand_more</span>
        </div>
    </div>
</aside>
