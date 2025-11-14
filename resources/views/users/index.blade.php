<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Simulasi TKA</title>
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

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-header {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFB6B6, #FFA0A0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: #702637;
            cursor: pointer;
        }

        /* Content */
        .content {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
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
        }

        /* User Management Container */
        .user-management-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 24px 32px;
            border-bottom: 1px solid #F0F0F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .user-count {
            font-size: 14px;
            color: #999;
            font-weight: 500;
        }

        .card-actions {
            display: flex;
            gap: 12px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F5F5F7;
            padding: 10px 16px;
            border-radius: 10px;
            min-width: 250px;
        }

        .search-box .material-symbols-outlined {
            font-size: 20px;
            color: #999;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            color: #333;
        }

        .search-box input::placeholder {
            color: #999;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-filters {
            background: #F5F5F7;
            color: #333;
        }

        .btn-filters:hover {
            background: #E8E8EA;
        }

        .btn-primary {
            background: #1A1A1A;
            color: white;
        }

        .btn-primary:hover {
            background: #333;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #F0F0F0;
            padding: 0 32px;
        }

        .tab {
            padding: 16px 24px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab:hover {
            color: #333;
        }

        .tab.active {
            color: #702637;
            border-bottom-color: #702637;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #FAFAFA;
        }

        th {
            text-align: left;
            padding: 16px 32px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px 32px;
            border-top: 1px solid #F0F0F0;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-table {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name-table {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 13px;
            color: #999;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 6px;
        }

        .badge-admin {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-export {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .badge-import {
            background: #E0E7FF;
            color: #4338CA;
        }

        .date-text {
            font-size: 14px;
            color: #666;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #F5F5F7;
        }

        .action-btn .material-symbols-outlined {
            font-size: 20px;
            color: #666;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 24px 32px;
            border-top: 1px solid #F0F0F0;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            background: white;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-btn:hover {
            background: #F5F5F7;
        }

        .page-btn.active {
            background: #702637;
            color: white;
            border-color: #702637;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1A1A1A;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 2000;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            background: #10B981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-icon .material-symbols-outlined {
            font-size: 16px;
            color: white;
            font-variation-settings: 'FILL' 1;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .toast-actions {
            display: flex;
            gap: 12px;
        }

        .toast-link {
            color: #60A5FA;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 0;
        }

        .toast-close .material-symbols-outlined {
            font-size: 20px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #D1D5DB;
            border-radius: 4px;
            cursor: pointer;
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

            .search-box {
                min-width: auto;
            }

            .content {
                padding: 20px;
            }

            th, td {
                padding: 12px 16px;
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
                    <div class="logo-text">QLTS Geek</div>
                </div>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Menu</div>
                    <a href="/dashboard" class="menu-item">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="menu-item-text">Dashboard</span>
                    </a>
                    <a href="/users" class="menu-item active">
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
                        <div class="user-role">Admin</div>
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
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 4px;">QLTS Geek</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;">User management</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-avatar-header">FS</div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">User management</h1>
                    <p class="page-subtitle">Manage your team members and their account permissions here.</p>
                </div>

                <div class="user-management-card">
                    <div class="card-header">
                        <div class="card-title-section">
                            <h2 class="card-title">All users</h2>
                            <span class="user-count" id="userCount">0</span>
                        </div>
                        <div class="card-actions">
                            <div class="search-box">
                                <span class="material-symbols-outlined">search</span>
                                <input type="text" placeholder="Search" id="searchInput" onkeyup="searchUsers()">
                            </div>
                            <button class="btn btn-filters">
                                <span class="material-symbols-outlined">tune</span>
                                Filters
                            </button>
                            <button class="btn btn-primary" onclick="openAddUserModal()">
                                <span class="material-symbols-outlined">add</span>
                                Add user
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="tabs">
                        <div class="tab active" onclick="switchTab('admin')">Admin</div>
                        <div class="tab" onclick="switchTab('guru')">Guru</div>
                        <div class="tab" onclick="switchTab('siswa')">Siswa</div>
                    </div>

                    <!-- Admin Tab Content -->
                    <div id="admin-content" class="tab-content active">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="checkbox">
                                        </th>
                                        <th>User name</th>
                                        <th>Access</th>
                                        <th>Last active</th>
                                        <th>Date added</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($admins as $admin)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkbox">
                                        </td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-table" style="background: linear-gradient(135deg, #FFB6B6, #FFA0A0); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: #702637;">
                                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name-table">{{ $admin->name }}</div>
                                                    <div class="user-email">{{ $admin->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-admin">Admin</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $admin->updated_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $admin->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <button class="action-btn" onclick="showActionMenu({{ $admin->id }})">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                            Belum ada admin
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($admins->count() > 0)
                        <div class="pagination">
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <button class="page-btn">6</button>
                        </div>
                        @endif
                    </div>

                    <!-- Guru Tab Content -->
                    <div id="guru-content" class="tab-content">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="checkbox">
                                        </th>
                                        <th>User name</th>
                                        <th>Access</th>
                                        <th>Last active</th>
                                        <th>Date added</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($teachers as $teacher)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkbox">
                                        </td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-table" style="background: linear-gradient(135deg, #A78BFA, #8B5CF6); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: white;">
                                                    {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name-table">{{ $teacher->name }}</div>
                                                    <div class="user-email">{{ $teacher->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-export">Guru</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $teacher->updated_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $teacher->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <button class="action-btn" onclick="showActionMenu({{ $teacher->id }})">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                            Belum ada guru
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($teachers->count() > 0)
                        <div class="pagination">
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <button class="page-btn">6</button>
                        </div>
                        @endif
                    </div>

                    <!-- Siswa Tab Content -->
                    <div id="siswa-content" class="tab-content">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="checkbox">
                                        </th>
                                        <th>User name</th>
                                        <th>Access</th>
                                        <th>Last active</th>
                                        <th>Date added</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkbox">
                                        </td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-table" style="background: linear-gradient(135deg, #60A5FA, #3B82F6); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: white;">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name-table">{{ $student->name }}</div>
                                                    <div class="user-email">{{ $student->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-import">Siswa</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $student->updated_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $student->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <button class="action-btn" onclick="showActionMenu({{ $student->id }})">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                            Belum ada siswa
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($students->count() > 0)
                        <div class="pagination">
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <button class="page-btn">6</button>
                        </div>
                        @endif
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

        function switchTab(tab) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to selected tab
            event.target.classList.add('active');
            document.getElementById(tab + '-content').classList.add('active');

            // Update user count
            updateUserCount();
        }

        function updateUserCount() {
            const activeTab = document.querySelector('.tab.active').textContent.toLowerCase();
            const activeContent = document.querySelector('.tab-content.active');
            const rows = activeContent.querySelectorAll('tbody tr');
            const count = rows.length === 1 && rows[0].cells.length === 1 ? 0 : rows.length;
            document.getElementById('userCount').textContent = count;
        }

        function searchUsers() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const activeContent = document.querySelector('.tab-content.active');
            const rows = activeContent.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('.user-name-table')?.textContent.toLowerCase() || '';
                const email = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
                
                if (name.includes(input) || email.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function openAddUserModal() {
            alert('Fitur Add User akan segera tersedia');
        }

        function showActionMenu(userId) {
            alert('Action menu untuk user ID: ' + userId);
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

        // Initialize user count on page load
        window.addEventListener('DOMContentLoaded', updateUserCount);
    </script>
</body>
</html>
