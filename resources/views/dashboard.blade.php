<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Simulasi TKA</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #F5F5F7;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: #702637;
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon .material-symbols-outlined {
            font-size: 24px;
            color: white;
            font-variation-settings: 'FILL' 1, 'wght' 500;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            padding: 0 20px;
            margin-bottom: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: white;
        }

        .menu-item .material-symbols-outlined {
            font-size: 20px;
            font-variation-settings: 'FILL' 0, 'wght' 400;
        }

        .menu-item.active .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500;
        }

        .menu-item-text {
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .menu-item.expanded .menu-item-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.expanded {
            max-height: 200px;
        }

        .submenu-item {
            display: flex;
            align-items: center;
            gap: 12px;
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
            background: linear-gradient(135deg, #FFB6B6, #FFA0A0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            color: #702637;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: all 0.3s ease;
        }

        /* Header */
        .header {
            background: white;
            padding: 20px 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            transition: all 0.2s ease;
        }

        .menu-toggle:hover {
            background: #F5F5F7;
        }

        .menu-toggle .material-symbols-outlined {
            font-size: 24px;
            color: #333;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F5F5F7;
            padding: 10px 16px;
            border-radius: 12px;
            min-width: 300px;
        }

        .search-bar .material-symbols-outlined {
            font-size: 20px;
            color: #999;
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            color: #333;
        }

        .search-bar input::placeholder {
            color: #999;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F5F5F7;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .header-icon:hover {
            background: #E8E8EA;
        }

        .header-icon .material-symbols-outlined {
            font-size: 20px;
            color: #666;
        }

        .header-icon .badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #702637;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Content Area */
        .content {
            padding: 32px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #999;
            margin-bottom: 32px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.primary {
            background: rgba(112, 38, 55, 0.1);
        }

        .stat-icon.primary .material-symbols-outlined {
            color: #702637;
            font-size: 24px;
            font-variation-settings: 'FILL' 1;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: #999;
            font-weight: 500;
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

            .search-bar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
                    <a href="/dashboard" class="menu-item active">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="menu-item-text">Dashboard</span>
                    </a>
                    <a href="/users" class="menu-item">
                        <span class="material-symbols-outlined">group</span>
                        <span class="menu-item-text">User Management</span>
                    </a>
                    <div class="menu-item" onclick="toggleSubmenu(event)">
                        <span class="material-symbols-outlined">quiz</span>
                        <span class="menu-item-text">TKA</span>
                        <span class="material-symbols-outlined menu-item-arrow" style="font-size: 18px;">expand_more</span>
                    </div>
                    <div class="submenu">
                        <a href="/soal/create" class="submenu-item">
                            <span class="menu-item-text">Buat Soal</span>
                        </a>
                        <a href="/soal" class="submenu-item">
                            <span class="menu-item-text">List Soal</span>
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
                    <div class="user-avatar">SP</div>
                    <div class="user-info">
                        <div class="user-name">Sanket Pal</div>
                        <div class="user-role">Student</div>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 20px; color: rgba(255,255,255,0.6);">expand_more</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="search-bar">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" placeholder="Search for track, artist or album...">
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-icon">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="header-icon">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="badge"></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Selamat datang di dashboard Simulasi TKA</p>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Total Soal</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">quiz</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Soal Terjawab</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">task_alt</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0%</div>
                                <div class="stat-label">Progress</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">trending_up</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Nilai Rata-rata</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">grade</span>
                            </div>
                        </div>
                    </div>
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
            
            // Toggle expanded class
            menuItem.classList.toggle('expanded');
            submenu.classList.toggle('expanded');
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
