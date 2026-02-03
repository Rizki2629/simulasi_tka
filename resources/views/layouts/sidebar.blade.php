<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <span class="material-symbols-outlined">school</span>
            </div>
            <div class="logo-text">
                <span style="letter-spacing: 0.15em;">SIMULASI TKA</span><br>
                <span style="font-size: 13px;">SDN GROGOL UTARA 09</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-section-title">Menu</div>

            @php
                $isSimulasiTkaExpanded = request()->is('soal*')
                    || request()->is('simulasi/generate*')
                    || request()->is('simulasi/generated-active*')
                    || request()->is('simulasi/token*');
            @endphp

            <a href="/dashboard" class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="menu-item-text">Dashboard</span>
            </a>
            <a href="/users" class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                <span class="material-symbols-outlined">group</span>
                <span class="menu-item-text">User Management</span>
            </a>
            <a href="/rekap-nilai" class="menu-item {{ request()->is('rekap-nilai*') ? 'active' : '' }}">
                <span class="material-symbols-outlined">grading</span>
                <span class="menu-item-text">Rekap Nilai</span>
            </a>
            <a href="/simulasi/exam-list" class="menu-item {{ request()->is('simulasi/exam-list') || request()->is('simulasi/*/student-status') ? 'active' : '' }}">
                <span class="material-symbols-outlined">monitor_heart</span>
                <span class="menu-item-text">Monitor Siswa</span>
            </a>
            <div class="menu-item {{ $isSimulasiTkaExpanded ? 'expanded' : '' }}" onclick="toggleSubmenu(event)">
                <span class="material-symbols-outlined">menu_book</span>
                <span class="menu-item-text">Simulasi TKA</span>
                <span class="material-symbols-outlined menu-item-arrow" style="font-size: 18px;">expand_more</span>
            </div>
            <div class="submenu {{ $isSimulasiTkaExpanded ? 'expanded' : '' }}">
                <a href="/soal/create" class="submenu-item {{ request()->is('soal/create') ? 'active' : '' }}">
                    <span class="menu-item-text">Buat Soal</span>
                </a>
                <a href="/soal" class="submenu-item {{ request()->is('soal') && !request()->is('soal/create') ? 'active' : '' }}">
                    <span class="menu-item-text">Daftar Soal</span>
                </a>
                <a href="/simulasi/generate" class="submenu-item {{ request()->is('simulasi/generate') ? 'active' : '' }}">
                    <span class="menu-item-text">Generate Simulasi</span>
                </a>
                <a href="/simulasi/generated-active" class="submenu-item {{ request()->is('simulasi/generated-active') ? 'active' : '' }}">
                    <span class="menu-item-text">Daftar Simulasi Aktif</span>
                </a>
                <a href="/simulasi/token" class="submenu-item {{ request()->is('simulasi/token') ? 'active' : '' }}">
                    <span class="menu-item-text">Generate Token</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            @php
                $user = auth()->user();
                $nameParts = explode(' ', $user->name);
                $initials = '';
                foreach($nameParts as $part) {
                    if(!empty($part)) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                }
                $initials = substr($initials, 0, 2);
            @endphp
            <div class="user-avatar">{{ $initials }}</div>
            <div class="user-info">
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-role">{{ ucfirst($user->role ?? 'User') }}</div>
            </div>
        </div>
    </div>
</aside>
