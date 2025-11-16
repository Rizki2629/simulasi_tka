<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Soal - Simulasi TKA</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: #702637;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #702637;
            font-weight: bold;
            font-size: 20px;
        }

        .logo-text {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.2;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            padding: 8px 20px;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }

        .menu-item-text {
            flex: 1;
            font-size: 14px;
        }

        .menu-item-arrow {
            transition: transform 0.3s ease;
        }

        .menu-item.expanded .menu-item-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.1);
        }

        .submenu.expanded {
            max-height: 500px;
        }

        .submenu-item {
            display: block;
            padding: 10px 20px 10px 52px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
            font-size: 13px;
        }

        .submenu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .submenu-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.08);
        }

        .submenu-item::before {
            content: '';
            position: absolute;
            left: 32px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: #702637;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: #702637;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            padding: 16px 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .menu-toggle:hover {
            background: #f5f5f5;
        }

        .content {
            flex: 1;
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #666;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #702637;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #5a1e2d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(112, 38, 55, 0.3);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filter-row {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-bottom: 8px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #702637;
            box-shadow: 0 0 0 3px rgba(112, 38, 55, 0.1);
        }

        .search-box {
            flex: 2;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 44px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #702637;
            box-shadow: 0 0 0 3px rgba(112, 38, 55, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .soal-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .soal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #702637;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }

        .card-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f0e6e9;
            color: #702637;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .card-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            color: #999;
            transition: all 0.2s ease;
        }

        .card-menu:hover {
            background: #f5f5f5;
            color: #333;
        }

        .card-kode {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-available {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        .flash-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            max-width: 500px;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .flash-notification.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .flash-notification.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .flash-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .flash-content .material-symbols-outlined {
            font-size: 24px;
        }

        .flash-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .flash-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .flash-close .material-symbols-outlined {
            font-size: 20px;
        }

        .card-mapel {
            font-size: 16px;
            color: #702637;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }

        .info-item .material-symbols-outlined {
            font-size: 18px;
            color: #999;
        }

        .card-footer {
            display: flex;
            gap: 8px;
        }

        .btn-card {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border: 1px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
        }

        .btn-card:hover {
            background: #f5f5f5;
            border-color: #702637;
            color: #702637;
        }

        .btn-card.btn-edit {
            color: #2563eb;
            border-color: #2563eb;
            background: #eff6ff;
        }

        .btn-card.btn-edit:hover {
            background: #2563eb;
            color: white;
        }

        .btn-card.btn-delete {
            color: #dc2626;
            border-color: #dc2626;
            background: #fef2f2;
        }

        .btn-card.btn-delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .empty-icon {
            font-size: 80px;
            color: #e0e0e0;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }

        .empty-text {
            font-size: 14px;
            color: #999;
            margin-bottom: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .content {
                padding: 20px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .filter-row {
                flex-direction: column;
            }

            .filter-item,
            .search-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <div class="logo-text">SIMULASI TKA - SDN GU 09</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="menu-section">
                    <div class="menu-section-title">Menu</div>
                    <a href="/dashboard" class="menu-item">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="menu-item-text">Dashboard</span>
                    </a>
                    <a href="/users" class="menu-item">
                        <span class="material-symbols-outlined">group</span>
                        <span class="menu-item-text">User Management</span>
                    </a>
                    <div class="menu-item expanded" onclick="toggleSubmenu(event)">
                        <span class="material-symbols-outlined">quiz</span>
                        <span class="menu-item-text">Simulasi TKA</span>
                        <span class="material-symbols-outlined menu-item-arrow" style="font-size: 18px;">expand_more</span>
                    </div>
                    <div class="submenu expanded">
                        <a href="/soal/create" class="submenu-item">
                            <span class="menu-item-text">Buat Soal</span>
                        </a>
                        <a href="/soal" class="submenu-item active">
                            <span class="menu-item-text">Daftar Soal</span>
                        </a>
                        <a href="/simulasi/generate" class="submenu-item">
                            <span class="menu-item-text">Generate Simulasi</span>
                        </a>
                        <a href="/simulasi/token" class="submenu-item">
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Flash Notification -->
            @if(session('success'))
            <div class="flash-notification success" id="flashNotification">
                <div class="flash-content">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button class="flash-close" onclick="closeFlash()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="flash-notification error" id="flashNotification">
                <div class="flash-content">
                    <span class="material-symbols-outlined">error</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button class="flash-close" onclick="closeFlash()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            @endif

            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 4px;">Simulasi TKA</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;">Daftar Soal</div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                @if(session('success'))
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #28a745; display: flex; align-items: center; gap: 12px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #dc3545; display: flex; align-items: center; gap: 12px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
                    <span>{{ session('error') }}</span>
                </div>
                @endif
                
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Daftar Soal</h1>
                        <p class="page-subtitle">Kelola dan lihat semua soal yang telah dibuat</p>
                    </div>
                    <a href="/soal/create" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        Buat Soal Baru
                    </a>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label class="filter-label">Mata Pelajaran</label>
                            <select class="filter-select" id="filterMapel">
                                <option value="">Semua Mata Pelajaran</option>
                                <option value="Matematika">Matematika</option>
                                <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                <option value="IPA">IPA</option>
                                <option value="IPS">IPS</option>
                                <option value="PPKN">PPKN</option>
                                <option value="Bahasa Inggris">Bahasa Inggris</option>
                            </select>
                        </div>
                        <div class="search-box">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input type="text" class="search-input" placeholder="Cari berdasarkan kode soal..." id="searchInput">
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="cards-grid" id="cardsGrid">
                    @forelse($soals as $soal)
                    <div class="soal-card" data-mapel="{{ $soal->mataPelajaran->nama ?? 'Umum' }}">
                        <div class="card-header">
                            <span class="card-badge">
                                <span class="material-symbols-outlined" style="font-size: 16px;">quiz</span>
                                {{ $soal->mataPelajaran->nama ?? 'Umum' }}
                            </span>
                            <button class="card-menu">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                        </div>
                        <div class="card-kode">
                            {{ $soal->kode_soal }}
                            @if($soal->simulasi_soal_count > 0)
                                <span class="status-badge status-active">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>
                                    Aktif
                                </span>
                            @else
                                <span class="status-badge status-available">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">radio_button_unchecked</span>
                                    Tersedia
                                </span>
                            @endif
                        </div>
                        <div class="card-mapel">{{ $soal->mataPelajaran->nama ?? 'Umum' }}</div>
                        <div class="card-info">
                            <div class="info-item">
                                <span class="material-symbols-outlined">help</span>
                                <span>{{ ucfirst(str_replace('_', ' ', $soal->jenis_soal)) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="material-symbols-outlined">calendar_today</span>
                                <span>Dibuat: {{ $soal->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="material-symbols-outlined">person</span>
                                <span>Pembuat: {{ $soal->creator->name ?? 'Admin' }}</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="/soal/{{ $soal->id }}" class="btn-card">
                                <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                Lihat
                            </a>
                            <a href="/soal/{{ $soal->id }}/edit" class="btn-card btn-edit">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                Edit
                            </a>
                            <button class="btn-card btn-delete" onclick="hapusSoal({{ $soal->id }})">
                                <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                Hapus
                            </button>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>

                <!-- Empty State -->
                <div class="empty-state" style="display: {{ $soals->isEmpty() ? 'flex' : 'none' }};" id="emptyState">
                    <span class="material-symbols-outlined empty-icon">folder_open</span>
                    <div class="empty-title">Belum Ada Soal</div>
                    <div class="empty-text">Mulai buat soal baru untuk ditampilkan di sini</div>
                    <a href="/soal/create" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        Buat Soal Pertama
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function toggleSubmenu(event) {
            const menuItem = event.currentTarget;
            const submenu = menuItem.nextElementSibling;
            
            menuItem.classList.toggle('expanded');
            submenu.classList.toggle('expanded');
        }

        function hapusSoal(soalId) {
            if (confirm('Apakah Anda yakin ingin menghapus soal ini? Data yang dihapus tidak dapat dikembalikan.')) {
                fetch(`/soal/${soalId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload page to update list
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Gagal menghapus soal: ' + error);
                });
            }
        }

        // Filter and Search Functionality
        const filterMapel = document.getElementById('filterMapel');
        const searchInput = document.getElementById('searchInput');
        const cardsGrid = document.getElementById('cardsGrid');
        const emptyState = document.getElementById('emptyState');

        function filterCards() {
            const selectedMapel = filterMapel.value.toLowerCase();
            const searchTerm = searchInput.value.toLowerCase();
            const cards = document.querySelectorAll('.soal-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const mapel = card.getAttribute('data-mapel').toLowerCase();
                const kode = card.querySelector('.card-kode').textContent.toLowerCase();
                
                const matchMapel = !selectedMapel || mapel.includes(selectedMapel);
                const matchSearch = !searchTerm || kode.includes(searchTerm);

                if (matchMapel && matchSearch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show empty state if no cards visible
            if (visibleCount === 0) {
                cardsGrid.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                cardsGrid.style.display = 'grid';
                emptyState.style.display = 'none';
            }
        }

        filterMapel.addEventListener('change', filterCards);
        searchInput.addEventListener('input', filterCards);

        // Flash notification auto close and manual close
        function closeFlash() {
            const flash = document.getElementById('flashNotification');
            if (flash) {
                flash.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    flash.remove();
                }, 300);
            }
        }

        // Auto close flash after 5 seconds
        const flashNotification = document.getElementById('flashNotification');
        if (flashNotification) {
            setTimeout(() => {
                closeFlash();
            }, 5000);
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
