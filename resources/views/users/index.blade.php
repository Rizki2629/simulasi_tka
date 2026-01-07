<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Simulasi TKA</title>
    @include('layouts.styles')
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
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="main-content">
            @include('layouts.header', [
                'pageTitle' => 'User management', 
                'breadcrumb' => 'Simulasi TKA',
                'showAvatar' => true,
                'avatarInitials' => 'MD'
            ])

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
                            <span class="user-count" id="userCount">{{ $totalStudents ?? 0 }}</span>
                        </div>
                        <div class="card-actions">
                            <div class="search-box">
                                <span class="material-symbols-outlined">search</span>
                                <input type="text" placeholder="Search" id="searchInput" onkeyup="searchUsers()">
                            </div>
                            <!-- Bulk Delete Button (Hidden by default) -->
                            <button class="btn btn-danger" id="bulkDeleteBtn" style="display: none; background: #ef4444; color: white;" onclick="confirmBulkDelete()">
                                <span class="material-symbols-outlined">delete</span>
                                Hapus (<span id="selectedCount">0</span>)
                            </button>

                            <!-- Filter Button & Dropdown -->
                            <div style="position: relative;">
                                <button class="btn btn-filters" onclick="toggleFilterMenu()">
                                    <span class="material-symbols-outlined">tune</span>
                                    Filters
                                </button>
                                <!-- Filter Menu -->
                                <div id="filterMenu" style="
                                    display: none;
                                    position: absolute;
                                    top: 100%;
                                    right: 0;
                                    background: white;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
                                    width: 200px;
                                    z-index: 50;
                                    padding: 8px 0;
                                    margin-top: 4px;
                                ">
                                    <div style="padding: 8px 16px; font-weight: 600; color: #374151; font-size: 12px; text-transform: uppercase;">
                                        Filter Rombel
                                    </div>
                                    <a href="{{ request()->fullUrlWithQuery(['class_filter' => null]) }}" style="display: block; padding: 8px 16px; color: #4b5563; text-decoration: none; font-size: 14px; {{ !request('class_filter') ? 'background: #f3f4f6;' : '' }}">
                                        Semua Kelas
                                    </a>
                                    @if(isset($classes))
                                        @foreach($classes as $class)
                                            <a href="{{ request()->fullUrlWithQuery(['class_filter' => $class]) }}" style="display: block; padding: 8px 16px; color: #4b5563; text-decoration: none; font-size: 14px; {{ request('class_filter') == $class ? 'background: #f3f4f6;' : '' }}">
                                                Kelas {{ $class }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <button class="btn btn-primary" onclick="openAddUserModal()">
                                <span class="material-symbols-outlined">add</span>
                                Add user
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="tabs">
                        <div class="tab {{ request('tab', 'admin') == 'admin' ? 'active' : '' }}" onclick="switchTab('admin')">Admin</div>
                        <div class="tab {{ request('tab') == 'guru' ? 'active' : '' }}" onclick="switchTab('guru')">Guru</div>
                        <div class="tab {{ request('tab') == 'siswa' ? 'active' : '' }}" onclick="switchTab('siswa')">Siswa</div>
                    </div>

                    <!-- Admin Tab Content -->
                    <div id="admin-content" class="tab-content {{ request('tab', 'admin') == 'admin' ? 'active' : '' }}">
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
                                            <button class="action-btn" onclick="showActionMenu({{ $admin->id }}, event)">
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
                        <div class="pagination-container" style="padding: 20px;">
                            {{ $admins->appends(array_merge(request()->query(), ['tab' => 'admin']))->links('vendor.pagination.custom') }}
                        </div>
                        @endif
                    </div>

                    <!-- Guru Tab Content -->
                    <div id="guru-content" class="tab-content {{ request('tab') == 'guru' ? 'active' : '' }}">
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
                                            <button class="action-btn" onclick="showActionMenu({{ $teacher->id }}, event)">
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
                        <div class="pagination-container" style="padding: 20px;">
                            {{ $teachers->appends(array_merge(request()->query(), ['tab' => 'guru']))->links('vendor.pagination.custom') }}
                        </div>
                        @endif
                    </div>

                    <!-- Siswa Tab Content -->
                    <div id="siswa-content" class="tab-content {{ request('tab') == 'siswa' ? 'active' : '' }}">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="checkbox" onclick="toggleSelectAll(this)">
                                        </th>
                                        <th>Nama Siswa</th>
                                        <th>NISN</th>
                                        <th>NIPD</th>
                                        <th>JK</th>
                                        <th>Tempat, Tanggal Lahir</th>
                                        <th>Rombongan Belajar</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkbox" value="{{ $student->id }}">
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
                                            <span class="date-text">{{ $student->nisn ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $student->nipd ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ ($student->jenis_kelamin == 'L' || $student->jenis_kelamin == 'Laki-Laki') ? 'badge-export' : 'badge-admin' }}">
                                                {{ $student->jenis_kelamin ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="date-text">
                                                @if($student->tempat_lahir && $student->tanggal_lahir)
                                                    {{ $student->tempat_lahir }}, {{ \Carbon\Carbon::parse($student->tanggal_lahir)->format('d M Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-import">{{ $student->rombongan_belajar ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <button class="action-btn" onclick="showActionMenu({{ $student->id }}, event)">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                            Belum ada siswa
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($students->count() > 0)
                        <div class="pagination-container" style="padding: 20px;">
                            {{ $students->appends(array_merge(request()->query(), ['tab' => 'siswa']))->links('vendor.pagination.custom') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>


    <!-- Edit User Modal -->
    <div id="editUserModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="padding: 24px; border-bottom: 1px solid #f0f0f0;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #1a1a1a;">Edit User</h3>
            </div>
            <form id="editUserForm" style="padding: 24px;">
                <input type="hidden" id="editUserId" name="user_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Nama Lengkap</label>
                    <input type="text" id="editName" name="name" required style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Email</label>
                    <input type="email" id="editEmail" name="email" required style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Role</label>
                    <select id="editRole" name="role" required style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                        <option value="admin">Admin</option>
                        <option value="guru">Guru</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>

                <div id="editStudentFields" style="display: none;">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">NISN</label>
                        <input type="text" id="editNisn" name="nisn" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Rombongan Belajar</label>
                        <select id="editRombel" name="rombongan_belajar" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                            <option value="">Pilih Kelas</option>
                            <option value="6A">6A</option>
                            <option value="6B">6B</option>
                            <option value="6C">6C</option>
                            <option value="6D">6D</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #374151;">Password Baru (Opsional)</label>
                    <input type="password" id="editPassword" name="password" placeholder="Kosongkan jika tidak ingin mengganti" style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeEditModal()" style="padding: 10px 20px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; color: #374151; font-size: 14px; font-weight: 500; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" style="padding: 10px 20px; border: none; border-radius: 8px; background: #1a1a1a; color: white; font-size: 14px; font-weight: 500; cursor: pointer;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('layouts.scripts')
    <script>
        function toggleSelectAll(source) {
            const table = source.closest('table');
            if (!table) return;
            const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function switchTab(tab) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to selected tab content
            document.getElementById(tab + '-content').classList.add('active');
            
            // Add active class to the clicked tab header
            // We use 'event.target' if available, otherwise find by text or index
            // But since this is onclick, event is available.
            // Check if event exists and is a DOM element
            if(event && event.target && event.target.classList) {
                event.target.classList.add('active');
            } else {
                // Fallback loops to match tab name if called programmatically
                // (Optional, simplified for now)
            }

            // Update URL to persist state on refresh
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            // Reset pagination to page 1 provided we are switching context? 
            // Better to keep existing logic, but maybe clean url params? 
            // If we switch tab, pagination for other tabs shouldn't affect URL but they are namespaced (admin_page, etc).
            // So just setting 'tab' is fine.
            window.history.pushState({}, '', url);

            // Update user count
            updateUserCount();
        }

        function updateUserCount() {
            // Count is now static from backend (totalStudents)
            // No need to update dynamically
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

        let currentActionMenu = null;

        function showActionMenu(userId, event) {
            event.stopPropagation();
            
            // Close any existing menu
            if (currentActionMenu) {
                currentActionMenu.remove();
                currentActionMenu = null;
            }

            // Create menu
            const menu = document.createElement('div');
            menu.className = 'action-menu';
            menu.style.cssText = `
                position: absolute;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                min-width: 150px;
                z-index: 1000;
                overflow: hidden;
            `;

            // Get button position
            const button = event.target.closest('button');
            const rect = button.getBoundingClientRect();
            menu.style.top = (rect.bottom + window.scrollY) + 'px';
            menu.style.left = (rect.left + window.scrollX - 100) + 'px';

            // Add menu items
            menu.innerHTML = `
                <div onclick="editUser(${userId})" style="padding: 12px 16px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: #6b7280;">edit</span>
                    <span style="color: #374151; font-size: 14px;">Edit</span>
                </div>
                <div onclick="deleteUser(${userId})" style="padding: 12px 16px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: #ef4444;">delete</span>
                    <span style="color: #ef4444; font-size: 14px;">Hapus</span>
                </div>
            `;

            document.body.appendChild(menu);
            currentActionMenu = menu;
        }

        function editUser(userId) {
            if (currentActionMenu) {
                currentActionMenu.remove();
                currentActionMenu = null;
            }

            // Fetch user data
            fetch(`/users/${userId}`)
                .then(response => response.json())
                .then(user => {
                    // Populate form
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('editName').value = user.name || '';
                    document.getElementById('editEmail').value = user.email || '';
                    document.getElementById('editRole').value = user.role || '';
                    
                    // Show/hide student fields based on role
                    const studentFields = document.getElementById('editStudentFields');
                    if (user.role === 'siswa') {
                        studentFields.style.display = 'block';
                        document.getElementById('editNisn').value = user.nisn || '';
                        document.getElementById('editRombel').value = user.rombongan_belajar || '';
                    } else {
                        studentFields.style.display = 'none';
                    }

                    // Show modal
                    const modal = document.getElementById('editUserModal');
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengambil data user.');
                });
        }

        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
            document.getElementById('editUserForm').reset();
        }

        // Handle role change to show/hide student fields
        document.getElementById('editRole').addEventListener('change', function() {
            const studentFields = document.getElementById('editStudentFields');
            if (this.value === 'siswa') {
                studentFields.style.display = 'block';
            } else {
                studentFields.style.display = 'none';
            }
        });

        // Handle form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const formData = new FormData(this);
            const data = {};
            
            formData.forEach((value, key) => {
                if (value) data[key] = value;
            });

            fetch(`/users/${userId}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    closeEditModal();
                    location.reload();
                } else {
                    alert(result.message || 'Gagal mengupdate user.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate user.');
            });
        });

        function deleteUser(userId) {
            if (currentActionMenu) {
                currentActionMenu.remove();
                currentActionMenu = null;
            }
            
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return;

            fetch(`/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus user.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
        }

        // Close action menu when clicking outside
        document.addEventListener('click', function(event) {
            if (currentActionMenu && !event.target.closest('.action-menu')) {
                currentActionMenu.remove();
                currentActionMenu = null;
            }
        });

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

        // Toggle Filter Menu
        function toggleFilterMenu() {
            const menu = document.getElementById('filterMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        // Close Filter Menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('filterMenu');
            const btn = document.querySelector('.btn-filters');
            if (menu && btn && !menu.contains(event.target) && !btn.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        // Checkbox Logic for Bulk Delete
        function toggleSelectAll(source) {
            const table = source.closest('table');
            const checkboxes = table.querySelectorAll('tbody .checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateBulkDeleteButton();
        }

        // Add event listener to all checkboxes to update count
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('checkbox')) {
                updateBulkDeleteButton();
            }
        });

        function updateBulkDeleteButton() {
            const activeTabContent = document.querySelector('.tab-content.active');
            if (!activeTabContent) return;

            const checkedBoxes = activeTabContent.querySelectorAll('tbody .checkbox:checked');
            const count = checkedBoxes.length;
            
            const btn = document.getElementById('bulkDeleteBtn');
            const countSpan = document.getElementById('selectedCount');
            
            if (btn && countSpan) {
                if (count > 0) {
                    btn.style.display = 'flex';
                    btn.style.alignItems = 'center';
                    btn.style.gap = '8px';
                    countSpan.textContent = count;
                } else {
                    btn.style.display = 'none';
                }
            }
        }

        function confirmBulkDelete() {
            if (!confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) return;

            const activeTabContent = document.querySelector('.tab-content.active');
            const checkedBoxes = activeTabContent.querySelectorAll('tbody .checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);

            if (ids.length === 0) return;

            fetch('{{ route("users.bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                } else {
                    alert('Gagal menghapus data.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
        }

        // Initialize user count on page load
        window.addEventListener('DOMContentLoaded', updateUserCount);
    </script>
</body>
</html>
