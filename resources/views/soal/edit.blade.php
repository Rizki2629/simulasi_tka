<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="paste-upload-url" content="{{ route('soal.upload.paste.image') }}">
    <title>Edit Soal TKA - Simulasi TKA</title>
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
            font-size: 16px;
            font-weight: 600;
            letter-spacing: -0.5px;
            line-height: 1.3;
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

        /* Content */
        .content {
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #702637;
            color: white;
        }

        .btn-primary:hover {
            background: #5a1e2c;
        }

        .btn-secondary {
            background: #F5F5F7;
            color: #333;
        }

        .btn-secondary:hover {
            background: #E8E8EA;
        }

        /* Soal Container */
        .soal-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .soal-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .soal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 24px;
        }

        .soal-number {
            font-size: 18px;
            font-weight: 600;
            color: #702637;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #EF4444;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .delete-btn:hover {
            background: #FEE2E2;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #333;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #702637;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #333;
            font-family: 'Roboto', sans-serif;
            resize: vertical;
            min-height: 100px;
            transition: all 0.2s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #702637;
        }

        .upload-container {
            margin-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .upload-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #F0F9FF;
            color: #0284C7;
            border: 1px solid #BAE6FD;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-btn:hover {
            background: #E0F2FE;
            border-color: #0284C7;
        }

        .upload-btn input[type="file"] {
            display: none;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #E5E7EB;
            object-fit: contain;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .image-preview:hover {
            transform: scale(1.05);
            border-color: #702637;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Image Zoom Modal */
        .image-zoom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.2s ease;
        }

        .image-zoom-modal.active {
            display: flex;
        }

        .image-zoom-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
            animation: zoomIn 0.3s ease;
        }

        .image-zoom-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .image-zoom-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { 
                transform: scale(0.5);
                opacity: 0;
            }
            to { 
                transform: scale(1);
                opacity: 1;
            }
        }

        .remove-image-btn {
            background: #FEE2E2;
            color: #EF4444;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .remove-image-btn:hover {
            background: #FEE2E2;
        }

        .pernyataan-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 16px;
        }

        .pernyataan-item {
            display: flex;
            gap: 12px;
            align-items: start;
        }

        .pernyataan-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #333;
            transition: all 0.2s ease;
        }

        .pernyataan-input:focus {
            outline: none;
            border-color: #702637;
        }

        .checkbox-group {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #702637;
        }

        .checkbox-item input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #702637;
        }

        .checkbox-item label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }

        .btn-add-pernyataan {
            background: #F0F9FF;
            color: #0284C7;
            border: 1px dashed #0284C7;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 12px;
        }

        .btn-add-pernyataan:hover {
            background: #E0F2FE;
        }

        .pilihan-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }

        .pilihan-item {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .pilihan-label {
            width: 32px;
            height: 32px;
            background: #F3F4F6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6B7280;
            font-size: 14px;
        }

        .pilihan-input {
            flex: 1;
            padding: 10px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #333;
            transition: all 0.2s ease;
        }

        .pilihan-input:focus {
            outline: none;
            border-color: #702637;
        }

        .radio-item {
            display: flex;
            align-items: center;
        }

        .radio-item input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #702637;
        }

        .add-soal-btn {
            width: 100%;
            padding: 16px;
            background: white;
            border: 2px dashed #D1D5DB;
            border-radius: 16px;
            color: #6B7280;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }


        .add-soal-btn:hover {
            border-color: #702637;
            color: #702637;
            background: #FFF5F7;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #F3F4F6;
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

            .soal-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="edit-mode">
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
                        <a href="/soal/create" class="submenu-item active">
                            <span class="menu-item-text">Buat Soal</span>
                        </a>
                        <a href="/soal" class="submenu-item">
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
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 4px;">Simulasi TKA</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;">Edit Soal</div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Form Informasi Soal (Initial Form) - Hidden for edit -->
                <div id="formInfoSoal" class="page-header" style="margin-bottom: 24px; display: none;">
                    <div style="width: 100%;">
                        <h1 class="page-title">Edit Soal</h1>
                        <p class="page-subtitle">Pilih mata pelajaran untuk memulai</p>
                        
                        <div style="background: white; padding: 24px; border-radius: 12px; margin-top: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Mata Pelajaran</label>
                                <select id="mataPelajaran" class="form-select" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <option value="Matematika">Matematika</option>
                                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                    <option value="IPA">IPA (Ilmu Pengetahuan Alam)</option>
                                    <option value="IPS">IPS (Ilmu Pengetahuan Sosial)</option>
                                    <option value="PPKN">PPKN (Pendidikan Pancasila dan Kewarganegaraan)</option>
                                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                                </select>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="mulaiMembuatSoal()" style="width: 100%;">
                                <span class="material-symbols-outlined">arrow_forward</span>
                                Lanjutkan Membuat Soal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Soal (Shown for edit) -->
                <div id="formSoalContainer" style="display: block;">
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Edit Soal</h1>
                            <p class="page-subtitle" id="infoMataPelajaran"></p>
                        </div>
                    </div>

                    <!-- Paste Image Hint -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                        <span class="material-symbols-outlined" style="font-size: 24px;">info</span>
                        <div style="flex: 1;">
                            <strong style="display: block; margin-bottom: 4px;">💡 Tips: Paste Gambar Langsung!</strong>
                            <span style="font-size: 14px; opacity: 0.95;">Klik pada area pertanyaan atau pilihan jawaban, lalu tekan <kbd style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-family: monospace;">Ctrl+V</kbd> untuk paste gambar dari clipboard Anda. Tidak perlu klik tombol upload!</span>
                        </div>
                    </div>

                    <form id="formSoal" method="POST" action="/soal/{{ $soal->id }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @if(session('success'))
                        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                            {{ session('success') }}
                        </div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                            {{ session('error') }}
                        </div>
                        @endif
                        @if($errors->any())
                        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <input type="hidden" id="inputMataPelajaran" name="mata_pelajaran">
                        <input type="hidden" id="inputKodeSoal" name="kode_soal">
                        
                        <div class="soal-list" id="soalList">
                            <!-- Soal akan ditambahkan di sini -->
                        </div>

                        <button type="button" class="add-soal-btn" onclick="tambahSoal()">
                            <span class="material-symbols-outlined">add_circle</span>
                            Tambah Soal
                        </button>

                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary" onclick="kembaliKeInfoSoal()">
                                Kembali
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="debugDataAttributes()" style="background: #6c757d;">
                                🔍 Debug Data Attributes
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span class="material-symbols-outlined">save</span>
                                Update Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        let soalCounter = 0;
        let mataPelajaranData = '{{ $soal->mataPelajaran->nama }}';
        
        // Pre-fill data for edit
        const existingSoalData = {
            id: {{ $soal->id }},
            kode_soal: '{{ $soal->kode_soal }}',
            mata_pelajaran: '{{ $soal->mataPelajaran->nama }}',
            jenis_soal: '{{ $soal->jenis_soal }}',
            pertanyaan: {!! json_encode($soal->pertanyaan) !!},
            gambar_pertanyaan: '{{ $soal->gambar_pertanyaan }}',
            pembahasan: {!! json_encode($soal->pembahasan) !!},
            gambar_pembahasan: '{{ $soal->gambar_pembahasan }}',
            pilihan_jawaban: @json($soal->pilihanJawaban),
            sub_soal: @json($soal->subSoal)
        };

        // Initialize edit mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set hidden inputs
            document.getElementById('inputMataPelajaran').value = existingSoalData.mata_pelajaran;
            document.getElementById('inputKodeSoal').value = existingSoalData.kode_soal;
            
            // Update info display
            document.getElementById('infoMataPelajaran').textContent = `${existingSoalData.mata_pelajaran} (Kode: ${existingSoalData.kode_soal})`;
            
            // Load existing soal
            loadExistingSoal();
        });

        // Function to load existing soal for edit
        function loadExistingSoal() {
            soalCounter = 0;
            document.getElementById('soalList').innerHTML = '';
            
            // Load semua sub-soal
            const subSoalList = existingSoalData.sub_soal || [];
            
            // Check if this is a simple question (pilihan_ganda, isian, uraian without sub_soal)
            if (subSoalList.length === 0 && existingSoalData.jenis_soal) {
                // Load as single question form
                tambahSoal();
                
                setTimeout(() => {
                    const soalId = 1;
                    const jenisSoalSelect = document.querySelector(`[name="jenis_soal_${soalId}"]`);
                    if (jenisSoalSelect) {
                        jenisSoalSelect.value = existingSoalData.jenis_soal;
                        ubahJenisSoal(soalId, existingSoalData.jenis_soal);
                        
                        setTimeout(() => {
                            // Fill pertanyaan
                            const pertanyaanInput = document.querySelector(`[name="pertanyaan_${soalId}"]`);
                            if (pertanyaanInput) {
                                pertanyaanInput.value = existingSoalData.pertanyaan || '';
                            }

                            // Show existing question image if available
                            if (existingSoalData.gambar_pertanyaan) {
                                console.log('Attempting to show existing image:', existingSoalData.gambar_pertanyaan);
                                setTimeout(() => {
                                    showExistingImage(`preview-soal-${soalId}`, existingSoalData.gambar_pertanyaan);
                                }, 150);
                            }
                            
                            // Fill pembahasan
                            const pembahasanInput = document.querySelector(`[name="pembahasan_${soalId}"]`);
                            if (pembahasanInput) {
                                pembahasanInput.value = existingSoalData.pembahasan || '';
                            }
                            
                            // Fill pilihan jawaban untuk pilihan ganda
                            if (existingSoalData.jenis_soal === 'pilihan_ganda') {
                                (existingSoalData.pilihan_jawaban || []).forEach((pilihan) => {
                                    const label = pilihan.label.toLowerCase();
                                    const pilihanInput = document.querySelector(`[name="pilihan_${soalId}_${label}"]`);
                                    if (pilihanInput) {
                                        pilihanInput.value = pilihan.teks_jawaban;
                                    }

                                    // Show existing answer image if available
                                    if (pilihan.gambar_jawaban) {
                                        console.log('Attempting to show existing answer image:', label, pilihan.gambar_jawaban);
                                        setTimeout(() => {
                                            showExistingImage(`preview-${label}-${soalId}`, pilihan.gambar_jawaban);
                                        }, 150);
                                    }
                                    
                                    // Set correct answer radio
                                    if (pilihan.is_benar) {
                                        const jawabanRadio = document.querySelector(`[name="kunci_jawaban_${soalId}"][value="${label.toUpperCase()}"]`);
                                        if (jawabanRadio) {
                                            jawabanRadio.checked = true;
                                        }
                                    }
                                });
                            }
                        }, 100);
                    }
                }, 100);
                return;
            }
            
            if (subSoalList.length === 0) {
                // Jika belum ada sub-soal dan bukan single question, tambahkan 1 form kosong
                tambahSoal();
                return;
            }
            
            // Load setiap sub-soal
            subSoalList.forEach((subSoal, index) => {
                tambahSoal();
                
                setTimeout(() => {
                    const soalId = index + 1;
                    const jenisSoalSelect = document.querySelector(`[name="jenis_soal_${soalId}"]`);
                    if (jenisSoalSelect) {
                        jenisSoalSelect.value = subSoal.jenis_soal;
                        ubahJenisSoal(soalId, subSoal.jenis_soal);
                        
                        setTimeout(() => {
                            // Fill pertanyaan
                            const pertanyaanInput = document.querySelector(`[name="pertanyaan_${soalId}"]`);
                            if (pertanyaanInput) {
                                pertanyaanInput.value = subSoal.pertanyaan;
                            }

                            // Show existing question image if available
                            if (subSoal.gambar_pertanyaan) {
                                console.log('SubSoal - Attempting to show existing image:', subSoal.gambar_pertanyaan);
                                setTimeout(() => {
                                    showExistingImage(`preview-soal-${soalId}`, subSoal.gambar_pertanyaan);
                                }, 150);
                            }
                            
                            // Fill pembahasan
                            const pembahasanInput = document.querySelector(`[name="pembahasan_${soalId}"]`);
                            if (pembahasanInput) {
                                pembahasanInput.value = subSoal.pembahasan || '';
                            }
                            
                            // Fill pilihan jawaban sesuai jenis soal
                            if (subSoal.jenis_soal === 'pilihan_ganda') {
                                (subSoal.pilihan_jawaban || []).forEach((pilihan) => {
                                    const label = pilihan.label.toLowerCase();
                                    const pilihanInput = document.querySelector(`[name="pilihan_${soalId}_${label}"]`);
                                    if (pilihanInput) {
                                        pilihanInput.value = pilihan.teks_jawaban;
                                    }

                                    if (pilihan.gambar_jawaban) {
                                        console.log('SubSoal PG - Attempting to show answer image:', label, pilihan.gambar_jawaban);
                                        setTimeout(() => {
                                            showExistingImage(`preview-${label}-${soalId}`, pilihan.gambar_jawaban);
                                        }, 150);
                                    }
                                });

                                const jawabanRadio = document.querySelector(`[name="kunci_jawaban_${soalId}"][value="${subSoal.jawaban_benar}"]`);
                                if (jawabanRadio) {
                                    jawabanRadio.checked = true;
                                }
                            } else if (subSoal.jenis_soal === 'mcma') {
                                populateMcmaData(soalId, subSoal.pilihan_jawaban || []);
                            } else if (subSoal.jenis_soal === 'benar_salah') {
                                populateBenarSalahData(soalId, subSoal.pilihan_jawaban || []);
                            } else if (subSoal.jenis_soal === 'isian' || subSoal.jenis_soal === 'uraian') {
                                const kunciInput = document.querySelector(`[name="kunci_jawaban_${soalId}"]`);
                                if (kunciInput) {
                                    kunciInput.value = subSoal.kunci_jawaban || '';
                                }
                            }
                        }, 300);
                    }
                }, 100 * (index + 1));
            });
        }

        // Function to generate kode soal
        function generateKodeSoal(mataPelajaran) {
            const prefix = {
                'Matematika': 'MTK',
                'Bahasa Indonesia': 'BIN',
                'IPA': 'IPA',
                'IPS': 'IPS',
                'PPKN': 'PKN',
                'Bahasa Inggris': 'ING'
            };
            
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const kodeSoal = `${prefix[mataPelajaran]}-${year}${month}${day}-${hours}${minutes}${seconds}`;
            return kodeSoal;
        }

        // Function to start creating questions
        function mulaiMembuatSoal() {
            const mataPelajaran = document.getElementById('mataPelajaran').value;
            
            if (!mataPelajaran) {
                alert('Silakan pilih mata pelajaran terlebih dahulu!');
                return;
            }
            
            // Save data
            mataPelajaranData = mataPelajaran;
            
            // Generate kode soal
            const kodeSoal = generateKodeSoal(mataPelajaran);
            
            // Update hidden inputs
            document.getElementById('inputMataPelajaran').value = mataPelajaran;
            document.getElementById('inputKodeSoal').value = kodeSoal;
            
            // Update info display
            document.getElementById('infoMataPelajaran').textContent = `${mataPelajaran} (Kode: ${kodeSoal})`;
            
            // Hide info form, show soal form
            document.getElementById('formInfoSoal').style.display = 'none';
            document.getElementById('formSoalContainer').style.display = 'block';
        }

        // Function to go back to info form
        function kembaliKeInfoSoal() {
            if (confirm('Kembali ke form informasi akan menghapus semua soal yang sudah dibuat. Lanjutkan?')) {
                document.getElementById('formInfoSoal').style.display = 'block';
                document.getElementById('formSoalContainer').style.display = 'none';
                
                // Reset soal list
                document.getElementById('soalList').innerHTML = '';
                soalCounter = 0;
            }
        }

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

        function tambahSoal() {
            soalCounter++;
            const soalList = document.getElementById('soalList');
            
            const soalCard = document.createElement('div');
            soalCard.className = 'soal-card';
            soalCard.id = `soal-${soalCounter}`;
            soalCard.innerHTML = `
                <div class="soal-header">
                    <div class="soal-number">Soal nomor ${soalCounter}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Soal</label>
                    <select class="form-select" name="jenis_soal_${soalCounter}" onchange="ubahJenisSoal(${soalCounter}, this.value)" required>
                        <option value="">-- Pilih Jenis Soal --</option>
                        <option value="pilihan_ganda">Pilihan Ganda</option>
                        <option value="benar_salah">Pilihan Ganda Kompleks (Benar/Salah)</option>
                        <option value="mcma">Multiple Choice Multiple Answer (MCMA)</option>
                        <option value="isian">Isian Singkat</option>
                        <option value="uraian">Uraian</option>
                    </select>
                </div>

                <div id="soal-content-${soalCounter}">
                    <!-- Konten soal akan muncul di sini setelah memilih jenis -->
                </div>
            `;
            
            soalList.appendChild(soalCard);
        }

        function ubahJenisSoal(soalId, jenisSoal) {
            const contentDiv = document.getElementById(`soal-content-${soalId}`);
            
            if (jenisSoal === 'pilihan_ganda') {
                contentDiv.innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-textarea" name="pertanyaan_${soalId}" placeholder="Masukkan pertanyaan soal..." required></textarea>
                        <div class="upload-container">
                            <label class="upload-btn">
                                <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                Upload Gambar Soal
                                <input type="file" name="gambar_soal_${soalId}" accept="image/*" onchange="previewImage(this, 'preview-soal-${soalId}')">
                            </label>
                            <div id="preview-soal-${soalId}" class="upload-wrapper" style="display: none;">
                                <img class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage('preview-soal-${soalId}', this)">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilihan Jawaban</label>
                        <div class="pilihan-list">
                            <div class="pilihan-item" style="display: block; margin-bottom: 16px;">
                                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                                    <div class="pilihan-label">A</div>
                                    <input type="text" class="pilihan-input" name="pilihan_${soalId}_a" placeholder="Pilihan A" required>
                                    <div class="radio-item">
                                        <input type="radio" name="kunci_jawaban_${soalId}" value="A" required>
                                    </div>
                                </div>
                                <div class="upload-container" style="margin-left: 44px;">
                                    <label class="upload-btn">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                        Upload Gambar
                                        <input type="file" name="gambar_pilihan_${soalId}_a" accept="image/*" onchange="previewImage(this, 'preview-a-${soalId}')">
                                    </label>
                                    <div id="preview-a-${soalId}" class="upload-wrapper" style="display: none;">
                                        <img class="image-preview" src="" alt="Preview">
                                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-a-${soalId}', this)">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <div class="pilihan-item" style="display: block; margin-bottom: 16px;">
                                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                                    <div class="pilihan-label">B</div>
                                    <input type="text" class="pilihan-input" name="pilihan_${soalId}_b" placeholder="Pilihan B" required>
                                    <div class="radio-item">
                                        <input type="radio" name="kunci_jawaban_${soalId}" value="B" required>
                                    </div>
                                </div>
                                <div class="upload-container" style="margin-left: 44px;">
                                    <label class="upload-btn">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                        Upload Gambar
                                        <input type="file" name="gambar_pilihan_${soalId}_b" accept="image/*" onchange="previewImage(this, 'preview-b-${soalId}')">
                                    </label>
                                    <div id="preview-b-${soalId}" class="upload-wrapper" style="display: none;">
                                        <img class="image-preview" src="" alt="Preview">
                                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-b-${soalId}', this)">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <div class="pilihan-item" style="display: block; margin-bottom: 16px;">
                                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                                    <div class="pilihan-label">C</div>
                                    <input type="text" class="pilihan-input" name="pilihan_${soalId}_c" placeholder="Pilihan C" required>
                                    <div class="radio-item">
                                        <input type="radio" name="kunci_jawaban_${soalId}" value="C" required>
                                    </div>
                                </div>
                                <div class="upload-container" style="margin-left: 44px;">
                                    <label class="upload-btn">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                        Upload Gambar
                                        <input type="file" name="gambar_pilihan_${soalId}_c" accept="image/*" onchange="previewImage(this, 'preview-c-${soalId}')">
                                    </label>
                                    <div id="preview-c-${soalId}" class="upload-wrapper" style="display: none;">
                                        <img class="image-preview" src="" alt="Preview">
                                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-c-${soalId}', this)">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <div class="pilihan-item" style="display: block; margin-bottom: 16px;">
                                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                                    <div class="pilihan-label">D</div>
                                    <input type="text" class="pilihan-input" name="pilihan_${soalId}_d" placeholder="Pilihan D" required>
                                    <div class="radio-item">
                                        <input type="radio" name="kunci_jawaban_${soalId}" value="D" required>
                                    </div>
                                </div>
                                <div class="upload-container" style="margin-left: 44px;">
                                    <label class="upload-btn">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                        Upload Gambar
                                        <input type="file" name="gambar_pilihan_${soalId}_d" accept="image/*" onchange="previewImage(this, 'preview-d-${soalId}')">
                                    </label>
                                    <div id="preview-d-${soalId}" class="upload-wrapper" style="display: none;">
                                        <img class="image-preview" src="" alt="Preview">
                                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-d-${soalId}', this)">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">* Pilih radio button untuk menandai kunci jawaban</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pembahasan (Opsional)</label>
                        <textarea class="form-textarea" name="pembahasan_${soalId}" placeholder="Masukkan pembahasan soal..." style="min-height: 100px;"></textarea>
                    </div>
                `;
            } else if (jenisSoal === 'benar_salah') {
                contentDiv.innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Pertanyaan Utama</label>
                        <textarea class="form-textarea" name="pertanyaan_${soalId}" placeholder="Masukkan pertanyaan utama soal..." required></textarea>
                        <div class="upload-container">
                            <label class="upload-btn">
                                <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                Upload Gambar Soal
                                <input type="file" name="gambar_soal_${soalId}" accept="image/*" onchange="previewImage(this, 'preview-soal-${soalId}')">
                            </label>
                            <div id="preview-soal-${soalId}" class="upload-wrapper" style="display: none;">
                                <img class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage('preview-soal-${soalId}', this)">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pernyataan</label>
                        <div class="pernyataan-list" id="pernyataan-list-${soalId}">
                            <div class="pernyataan-item">
                                <input type="text" class="pernyataan-input" name="pernyataan_${soalId}[]" placeholder="Pernyataan 1" required>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="radio" id="benar_${soalId}_1" name="kunci_${soalId}_1" value="benar" required>
                                        <label for="benar_${soalId}_1">Benar</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="radio" id="salah_${soalId}_1" name="kunci_${soalId}_1" value="salah" required>
                                        <label for="salah_${soalId}_1">Salah</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add-pernyataan" onclick="tambahPernyataan(${soalId})">
                            <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                            Tambah Pernyataan
                        </button>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">* Pilih Benar atau Salah untuk menandai kunci jawaban setiap pernyataan</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pembahasan (Opsional)</label>
                        <textarea class="form-textarea" name="pembahasan_${soalId}" placeholder="Masukkan pembahasan soal..." style="min-height: 100px;"></textarea>
                    </div>
                `;
            } else if (jenisSoal === 'mcma') {
                contentDiv.innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Pertanyaan Utama</label>
                        <textarea class="form-textarea" name="pertanyaan_${soalId}" placeholder="Masukkan pertanyaan utama soal..." required></textarea>
                        <div class="upload-container">
                            <label class="upload-btn">
                                <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                Upload Gambar Soal
                                <input type="file" name="gambar_soal_${soalId}" accept="image/*" onchange="previewImage(this, 'preview-soal-${soalId}')">
                            </label>
                            <div id="preview-soal-${soalId}" class="upload-wrapper" style="display: none;">
                                <img class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage('preview-soal-${soalId}', this)">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pernyataan (Tentukan Benar atau Salah)</label>
                        <div class="pernyataan-list" id="pernyataan-list-${soalId}">
                            <div class="pernyataan-item">
                                <input type="text" class="pernyataan-input" name="pernyataan_${soalId}[]" placeholder="Pernyataan 1" required>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="benar_${soalId}_1" name="kunci_${soalId}_1_benar" value="benar">
                                        <label for="benar_${soalId}_1">Benar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add-pernyataan" onclick="tambahPernyataanMCMA(${soalId})">
                            <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                            Tambah Pernyataan
                        </button>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">* Centang Benar untuk setiap pernyataan sebagai kunci jawaban</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pembahasan (Opsional)</label>
                        <textarea class="form-textarea" name="pembahasan_${soalId}" placeholder="Masukkan pembahasan soal..." style="min-height: 100px;"></textarea>
                    </div>
                `;
            } else if (jenisSoal === 'isian') {
                contentDiv.innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-textarea" name="pertanyaan_${soalId}" placeholder="Masukkan pertanyaan soal..." required></textarea>
                        <div class="upload-container">
                            <label class="upload-btn">
                                <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                Upload Gambar Soal
                                <input type="file" name="gambar_soal_${soalId}" accept="image/*" onchange="previewImage(this, 'preview-soal-${soalId}')">
                            </label>
                            <div id="preview-soal-${soalId}" class="upload-wrapper" style="display: none;">
                                <img class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage('preview-soal-${soalId}', this)">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kunci Jawaban</label>
                        <input type="text" class="pernyataan-input" name="kunci_jawaban_${soalId}" placeholder="Masukkan kunci jawaban singkat..." required>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">* Isi dengan jawaban singkat yang tepat</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pembahasan (Opsional)</label>
                        <textarea class="form-textarea" name="pembahasan_${soalId}" placeholder="Masukkan pembahasan soal..." style="min-height: 100px;"></textarea>
                    </div>
                `;
            } else if (jenisSoal === 'uraian') {
                contentDiv.innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-textarea" name="pertanyaan_${soalId}" placeholder="Masukkan pertanyaan soal..." required></textarea>
                        <div class="upload-container">
                            <label class="upload-btn">
                                <span class="material-symbols-outlined" style="font-size: 18px;">image</span>
                                Upload Gambar Soal
                                <input type="file" name="gambar_soal_${soalId}" accept="image/*" onchange="previewImage(this, 'preview-soal-${soalId}')">
                            </label>
                            <div id="preview-soal-${soalId}" class="upload-wrapper" style="display: none;">
                                <img class="image-preview" src="" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage('preview-soal-${soalId}', this)">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kunci Jawaban / Rubrik Penilaian</label>
                        <textarea class="form-textarea" name="kunci_jawaban_${soalId}" placeholder="Masukkan kunci jawaban atau rubrik penilaian..." required style="min-height: 150px;"></textarea>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">* Isi dengan kunci jawaban atau panduan penilaian untuk jawaban uraian</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pembahasan (Opsional)</label>
                        <textarea class="form-textarea" name="pembahasan_${soalId}" placeholder="Masukkan pembahasan soal..." style="min-height: 100px;"></textarea>
                    </div>
                `;
            }
        }

        function populateBenarSalahData(soalId, pilihanList) {
            const pernyataanList = document.getElementById(`pernyataan-list-${soalId}`);
            if (!pernyataanList) {
                return;
            }

            if (pernyataanList.children.length === 0) {
                tambahPernyataan(soalId);
            }

            while (pernyataanList.children.length < pilihanList.length) {
                tambahPernyataan(soalId);
            }

            const items = pernyataanList.querySelectorAll('.pernyataan-item');
            pilihanList.forEach((pilihan, index) => {
                const item = items[index];
                if (!item) {
                    return;
                }

                const input = item.querySelector('.pernyataan-input');
                if (input) {
                    input.value = pilihan.teks_jawaban || '';
                }

                const radios = item.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.checked = radio.value === (pilihan.is_benar ? 'benar' : 'salah');
                });

                // Show existing pernyataan image if available
                if (pilihan.gambar_jawaban) {
                    const pernyataanNumber = index + 1;
                    console.log('BenarSalah - Attempting to show pernyataan image:', pernyataanNumber, pilihan.gambar_jawaban);
                    setTimeout(() => {
                        showExistingImage(`preview-pernyataan-${soalId}-${pernyataanNumber}`, pilihan.gambar_jawaban);
                    }, 200 + (index * 50));
                }
            });

            if (pilihanList.length > 0 && items.length > pilihanList.length) {
                for (let i = pilihanList.length; i < items.length; i++) {
                    items[i].remove();
                }
            }
        }

        function populateMcmaData(soalId, pilihanList) {
            const pernyataanList = document.getElementById(`pernyataan-list-${soalId}`);
            if (!pernyataanList) {
                return;
            }

            if (pernyataanList.children.length === 0) {
                tambahPernyataanMCMA(soalId);
            }

            while (pernyataanList.children.length < pilihanList.length) {
                tambahPernyataanMCMA(soalId);
            }

            const items = pernyataanList.querySelectorAll('.pernyataan-item');
            pilihanList.forEach((pilihan, index) => {
                const item = items[index];
                if (!item) {
                    return;
                }

                const input = item.querySelector('.pernyataan-input');
                if (input) {
                    input.value = pilihan.teks_jawaban || '';
                }

                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !!pilihan.is_benar;
                }

                // Show existing pernyataan image if available
                if (pilihan.gambar_jawaban) {
                    const pernyataanNumber = index + 1;
                    console.log('MCMA - Attempting to show pernyataan image:', pernyataanNumber, pilihan.gambar_jawaban);
                    setTimeout(() => {
                        showExistingImage(`preview-pernyataan-${soalId}-${pernyataanNumber}`, pilihan.gambar_jawaban);
                    }, 200 + (index * 50));
                }
            });

            if (pilihanList.length > 0 && items.length > pilihanList.length) {
                for (let i = pilihanList.length; i < items.length; i++) {
                    items[i].remove();
                }
            }
        }

        function tambahPernyataan(soalId) {
            const pernyataanList = document.getElementById(`pernyataan-list-${soalId}`);
            const pernyataanCount = pernyataanList.children.length + 1;
            
            const pernyataanItem = document.createElement('div');
            pernyataanItem.className = 'pernyataan-item';
            pernyataanItem.innerHTML = `
                <input type="text" class="pernyataan-input" name="pernyataan_${soalId}[]" placeholder="Pernyataan ${pernyataanCount}" required>
                <div class="upload-container" style="margin: 8px 0;">
                    <label class="upload-btn" style="font-size: 12px; padding: 6px 12px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">image</span>
                        Upload Gambar Pernyataan
                        <input type="file" name="gambar_pernyataan_${soalId}_${pernyataanCount}" accept="image/*" onchange="previewImage(this, 'preview-pernyataan-${soalId}-${pernyataanCount}')">
                    </label>
                    <div id="preview-pernyataan-${soalId}-${pernyataanCount}" class="upload-wrapper" style="display: none;">
                        <img class="image-preview" src="" alt="Preview">
                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-pernyataan-${soalId}-${pernyataanCount}', this)">Hapus</button>
                    </div>
                </div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="radio" id="benar_${soalId}_${pernyataanCount}" name="kunci_${soalId}_${pernyataanCount}" value="benar" required>
                        <label for="benar_${soalId}_${pernyataanCount}">Benar</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="radio" id="salah_${soalId}_${pernyataanCount}" name="kunci_${soalId}_${pernyataanCount}" value="salah" required>
                        <label for="salah_${soalId}_${pernyataanCount}">Salah</label>
                    </div>
                </div>
            `;
            
            pernyataanList.appendChild(pernyataanItem);
        }

        function tambahPernyataanMCMA(soalId) {
            const pernyataanList = document.getElementById(`pernyataan-list-${soalId}`);
            const pernyataanCount = pernyataanList.children.length + 1;
            
            const pernyataanItem = document.createElement('div');
            pernyataanItem.className = 'pernyataan-item';
            pernyataanItem.innerHTML = `
                <input type="text" class="pernyataan-input" name="pernyataan_${soalId}[]" placeholder="Pernyataan ${pernyataanCount}" required>
                <div class="upload-container" style="margin: 8px 0;">
                    <label class="upload-btn" style="font-size: 12px; padding: 6px 12px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">image</span>
                        Upload Gambar Pernyataan
                        <input type="file" name="gambar_pernyataan_${soalId}_${pernyataanCount}" accept="image/*" onchange="previewImage(this, 'preview-pernyataan-${soalId}-${pernyataanCount}')">
                    </label>
                    <div id="preview-pernyataan-${soalId}-${pernyataanCount}" class="upload-wrapper" style="display: none;">
                        <img class="image-preview" src="" alt="Preview">
                        <button type="button" class="remove-image-btn" onclick="removeImage('preview-pernyataan-${soalId}-${pernyataanCount}', this)">Hapus</button>
                    </div>
                </div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="benar_${soalId}_${pernyataanCount}" name="kunci_${soalId}_${pernyataanCount}_benar" value="benar">
                        <label for="benar_${soalId}_${pernyataanCount}">Benar</label>
                    </div>
                </div>
            `;
            
            pernyataanList.appendChild(pernyataanItem);
        }

        function hapusSoal(soalId) {
            if (confirm('Apakah Anda yakin ingin menghapus soal ini?')) {
                const soalCard = document.getElementById(`soal-${soalId}`);
                soalCard.remove();
                
                // Update nomor soal
                updateNomorSoal();
            }
        }

        function updateNomorSoal() {
            const soalCards = document.querySelectorAll('.soal-card');
            soalCards.forEach((card, index) => {
                const soalNumber = card.querySelector('.soal-number');
                soalNumber.textContent = `Soal nomor ${index + 1}`;
            });
        }

        // Tambah soal pertama otomatis saat halaman dimuat
        window.addEventListener('DOMContentLoaded', function() {
            if (!document.body.classList.contains('edit-mode')) {
                tambahSoal();
            }
        });

        // Form validation and submit handler
        let formSubmitting = false;
        
        document.getElementById('formSoal').addEventListener('submit', function(e) {
            // Prevent default form submission
            e.preventDefault();
            
            // Prevent double submission
            if (formSubmitting) {
                console.log('Form already submitting...');
                return false;
            }
            
            const soalCards = document.querySelectorAll('.soal-card');
            if (soalCards.length === 0) {
                alert('Minimal harus ada 1 soal!');
                return false;
            }
            
            // Validasi setiap soal sudah memilih jenis
            let valid = true;
            soalCards.forEach((card, index) => {
                const select = card.querySelector('select[name^="jenis_soal"]');
                if (!select.value) {
                    alert(`Soal nomor ${index + 1} belum memilih jenis soal!`);
                    valid = false;
                    return;
                }
            });
            
            if (!valid) {
                return false;
            }
            
            try {
                formSubmitting = true;
                console.log('=== FORM SUBMIT - NEW APPROACH ===');
                
                // NEW APPROACH: Extract image paths from data attributes and create hidden inputs
                // Find all preview containers with data-image-path
                const previewContainers = this.querySelectorAll('[data-image-path][data-input-name]');
                console.log(`Found ${previewContainers.length} preview containers with image data`);
                
                // Create hidden inputs for each image
                previewContainers.forEach(preview => {
                    const imagePath = preview.getAttribute('data-image-path');
                    const inputName = preview.getAttribute('data-input-name');
                    
                    if (imagePath && inputName) {
                        console.log(`Processing: ${inputName}`);
                        
                        // Check if file input exists with same name
                        let fileInput = this.querySelector(`input[type="file"][name="${inputName}"]`);
                        
                        if (fileInput) {
                            // If file input has file selected, skip (use file upload)
                            if (fileInput.files && fileInput.files.length > 0) {
                                console.log(`⚠ Skipping ${inputName} - file input has file selected`);
                                return;
                            }
                            
                            // No file selected, disable file input and use paste image
                            console.log(`  Disabling file input, using paste image for: ${inputName}`);
                            fileInput.disabled = true;
                        }
                        
                        // Check if hidden input already exists
                        let hiddenInput = this.querySelector(`input[type="hidden"][name="${inputName}"]`);
                        
                        if (!hiddenInput) {
                            // Create new hidden input
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = inputName;
                            this.appendChild(hiddenInput);
                            console.log(`✓ Created hidden input: ${inputName}`);
                        }
                        
                        hiddenInput.value = imagePath;
                        console.log(`  ${inputName} = ${imagePath}`);
                    }
                });
                
                // Verify all hidden inputs
                console.log('=== VERIFICATION ===');
                const allGambarInputs = this.querySelectorAll('input[type="hidden"][name*="gambar"]');
                console.log(`Total hidden gambar inputs in form: ${allGambarInputs.length}`);
                allGambarInputs.forEach(input => {
                    console.log(`  ${input.name} = ${input.value}`);
                });
                
                // Submit form using native submit (bypass event listener)
                console.log('Submitting form...');
                HTMLFormElement.prototype.submit.call(this);
                
            } catch (error) {
                formSubmitting = false;
                console.error('Form submit error:', error);
                alert('Terjadi kesalahan: ' + error.message);
                return false;
            }
        });

        // Debug function to check data attributes
        function debugDataAttributes() {
            console.log('=== DEBUG DATA ATTRIBUTES ===');
            const form = document.getElementById('formSoal');
            const allElements = form.querySelectorAll('[data-image-path]');
            
            console.log(`Found ${allElements.length} elements with data-image-path`);
            
            allElements.forEach((el, index) => {
                console.log(`\n[${index + 1}] Element:`, el.tagName, el.id || el.className);
                console.log('  data-image-path:', el.getAttribute('data-image-path'));
                console.log('  data-input-name:', el.getAttribute('data-input-name'));
                console.log('  Display:', el.style.display);
            });
            
            if (allElements.length === 0) {
                alert('⚠ Tidak ada data attributes ditemukan!\nPastikan Anda sudah paste gambar terlebih dahulu.');
            } else {
                alert(`✓ Found ${allElements.length} elements with image data.\nCheck console for details.`);
            }
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

        function showExistingImage(previewId, imagePath) {
            if (!imagePath) {
                console.log('No image path provided for:', previewId);
                return;
            }
            
            const previewWrapper = document.getElementById(previewId);
            if (!previewWrapper) {
                console.warn('❌ Preview wrapper not found:', previewId);
                return;
            }
            
            const previewImg = previewWrapper.querySelector('.image-preview');
            if (!previewImg) {
                console.warn('❌ Preview img not found in:', previewId);
                return;
            }
            
            previewImg.src = `/storage/${imagePath}`;
            previewWrapper.style.display = 'block';
            
            // NEW APPROACH: Use data attributes instead of hidden inputs
            // Get proper name from preview ID
            const parts = previewId.replace('preview-', '').split('-');
            let finalName = '';
            
            if (parts[0] === 'soal') {
                finalName = 'gambar_soal_' + parts[1];
            } else if (parts[0] === 'pembahasan') {
                finalName = 'gambar_pembahasan_' + parts[1];
            } else if (parts[0] === 'pernyataan') {
                finalName = 'gambar_pernyataan_' + parts[1] + '_' + parts[2];
            } else {
                finalName = 'gambar_pilihan_' + parts[1] + '_' + parts[0];
            }
            
            // Store data in preview wrapper attributes
            previewWrapper.setAttribute('data-image-path', imagePath);
            previewWrapper.setAttribute('data-input-name', finalName);
            
            console.log('✓ Existing image data stored:', {
                previewId: previewId,
                inputName: finalName,
                path: imagePath,
                elementFound: true
            });
        }

        // Image preview function
        function previewImage(input, previewId) {
            const previewWrapper = document.getElementById(previewId);
            const previewImg = previewWrapper.querySelector('.image-preview');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar!');
                    input.value = '';
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran gambar maksimal 5MB!');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewWrapper.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        }

        // Remove image function
        function removeImage(previewId, button) {
            const previewWrapper = document.getElementById(previewId);
            const previewImg = previewWrapper.querySelector('.image-preview');
            const uploadBtn = previewWrapper.previousElementSibling;
            const fileInput = uploadBtn.querySelector('input[type="file"]');
            
            // Reset file input
            fileInput.value = '';
            
            // Reset preview
            previewImg.src = '';
            previewWrapper.style.display = 'none';
        }
    </script>
    
    <!-- Image Zoom Modal -->
    <div id="imageZoomModal" class="image-zoom-modal" onclick="closeImageZoom()">
        <span class="image-zoom-close">&times;</span>
        <img id="zoomedImage" class="image-zoom-content" src="" alt="Zoomed Image">
    </div>

    <script>
        // Image Zoom Functionality
        function openImageZoom(imageSrc) {
            const modal = document.getElementById('imageZoomModal');
            const zoomedImg = document.getElementById('zoomedImage');
            
            zoomedImg.src = imageSrc;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImageZoom() {
            const modal = document.getElementById('imageZoomModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Add click event to all image previews
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('image-preview') && e.target.src) {
                openImageZoom(e.target.src);
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageZoom();
            }
        });
    </script>
    
    <!-- Paste Image Upload Script -->
    <script src="{{ asset('js/paste-image-upload.js') }}"></script>
</body>
</html>
