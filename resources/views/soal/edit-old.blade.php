<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin-bottom: 12px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .menu-item .material-symbols-outlined {
            font-size: 22px;
            margin-right: 12px;
        }

        .menu-item-text {
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item-arrow {
            margin-left: auto;
            transition: transform 0.2s ease;
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
            max-height: 300px;
        }

        .submenu-item {
            display: block;
            padding: 10px 20px 10px 54px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .submenu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .submenu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
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
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: white;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
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
            background: #F3F4F6;
        }

        .content {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6B7280;
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
            text-decoration: none;
        }

        .btn-primary {
            background: #702637;
            color: white;
        }

        .btn-primary:hover {
            background: #5A1E2D;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(112, 38, 55, 0.3);
        }

        .btn-secondary {
            background: #F3F4F6;
            color: #6B7280;
        }

        .btn-secondary:hover {
            background: #E5E7EB;
        }

        .btn-tambah-soal {
            padding: 14px 40px;
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }

        .btn-tambah-soal:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-label .required {
            color: #EF4444;
        }

        .form-select, .form-input, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #333;
            transition: all 0.2s ease;
            font-family: 'Roboto', sans-serif;
        }

        .form-select:focus, .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #702637;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .soal-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .soal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #F3F4F6;
        }

        .soal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .soal-badge {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-pg {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .badge-bs {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-mcma {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-isian {
            background: #E0E7FF;
            color: #3730A3;
        }

        .badge-uraian {
            background: #FCE7F3;
            color: #831843;
        }

        .image-preview-wrapper {
            margin-top: 12px;
            position: relative;
        }

        .image-preview {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #E5E7EB;
        }

        .image-remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #EF4444;
            color: white;
            border: none;
            padding: 6px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pilihan-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
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

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #F3F4F6;
        }

        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
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
                @if(session('success'))
                <div class="alert alert-success">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <div>
                        <strong>Terjadi kesalahan:</strong>
                        <ul style="margin: 8px 0 0 20px;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Edit Soal</h1>
                        <p class="page-subtitle">Perbarui informasi soal di bawah ini</p>
                    </div>
                </div>

                <form action="/soal/{{ $soal->id }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="soal-card">
                        <div class="soal-header">
                            <div class="soal-title">
                                <span class="material-symbols-outlined" style="color: #702637;">edit_note</span>
                                Edit Soal
                            </div>
                            <span class="soal-badge badge-{{ $soal->jenis_soal == 'pilihan_ganda' ? 'pg' : ($soal->jenis_soal == 'benar_salah' ? 'bs' : ($soal->jenis_soal == 'mcma' ? 'mcma' : ($soal->jenis_soal == 'isian' ? 'isian' : 'uraian'))) }}">
                                {{ $soal->jenis_soal == 'pilihan_ganda' ? 'Pilihan Ganda' : ($soal->jenis_soal == 'benar_salah' ? 'Benar/Salah' : ($soal->jenis_soal == 'mcma' ? 'Multiple Choice Multiple Answer' : ($soal->jenis_soal == 'isian' ? 'Isian' : 'Uraian'))) }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mata Pelajaran <span class="required">*</span></label>
                            <select name="mata_pelajaran_id" class="form-select" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mataPelajaran as $mp)
                                <option value="{{ $mp->id }}" {{ $soal->mata_pelajaran_id == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jenis Soal <span class="required">*</span></label>
                            <select name="jenis_soal" class="form-select" id="jenisSoal" required onchange="togglePilihan()">
                                <option value="">-- Pilih Jenis Soal --</option>
                                <option value="pilihan_ganda" {{ $soal->jenis_soal == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                                <option value="benar_salah" {{ $soal->jenis_soal == 'benar_salah' ? 'selected' : '' }}>Benar/Salah</option>
                                <option value="mcma" {{ $soal->jenis_soal == 'mcma' ? 'selected' : '' }}>Multiple Choice Multiple Answer</option>
                                <option value="isian" {{ $soal->jenis_soal == 'isian' ? 'selected' : '' }}>Isian</option>
                                <option value="uraian" {{ $soal->jenis_soal == 'uraian' ? 'selected' : '' }}>Uraian</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pertanyaan <span class="required">*</span></label>
                            <textarea name="pertanyaan" class="form-textarea" required>{{ $soal->pertanyaan }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Gambar Pertanyaan (opsional)</label>
                            <input type="file" name="gambar_pertanyaan" class="form-input" accept="image/*" onchange="previewImage(event, 'preview-pertanyaan')">
                            @if($soal->gambar_pertanyaan)
                            <div class="image-preview-wrapper" id="existing-image">
                                <img src="{{ asset('storage/' . $soal->gambar_pertanyaan) }}" alt="Gambar Pertanyaan" class="image-preview">
                                <button type="button" class="image-remove-btn" onclick="removeExistingImage()">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                </button>
                            </div>
                            @endif
                            <div class="image-preview-wrapper" id="preview-pertanyaan" style="display: none;">
                                <img src="" alt="Preview" class="image-preview">
                            </div>
                        </div>

                        <!-- Pilihan Jawaban Section -->
                        <div id="pilihanSection" style="{{ in_array($soal->jenis_soal, ['pilihan_ganda', 'benar_salah', 'mcma']) ? '' : 'display: none;' }}">
                            <div class="form-group">
                                <label class="form-label">Pilihan Jawaban <span class="required">*</span></label>
                                
                                @if($soal->jenis_soal == 'pilihan_ganda')
                                    @php
                                        $pilihan = $soal->pilihanJawaban;
                                    @endphp
                                    @foreach(['A', 'B', 'C', 'D'] as $index => $label)
                                        @php
                                            $pil = $pilihan->where('label', $label)->first();
                                        @endphp
                                        <div class="pilihan-wrapper">
                                            <div class="pilihan-label">{{ $label }}</div>
                                            <div style="flex: 1;">
                                                <input type="text" 
                                                       name="pilihan_{{ strtolower($label) }}" 
                                                       class="pilihan-input" 
                                                       placeholder="Masukkan pilihan {{ $label }}"
                                                       value="{{ $pil ? $pil->teks_jawaban : '' }}"
                                                       required>
                                                <input type="file" 
                                                       name="gambar_pilihan_{{ strtolower($label) }}" 
                                                       class="form-input" 
                                                       accept="image/*" 
                                                       onchange="previewImage(event, 'preview-pilihan-{{ strtolower($label) }}')"
                                                       style="margin-top: 8px; font-size: 12px;">
                                                @if($pil && $pil->gambar_jawaban)
                                                <div class="image-preview-wrapper" id="existing-pilihan-{{ strtolower($label) }}">
                                                    <img src="{{ asset('storage/' . $pil->gambar_jawaban) }}" alt="Gambar Pilihan {{ $label }}" class="image-preview" style="max-width: 200px; max-height: 150px;">
                                                    <button type="button" class="image-remove-btn" onclick="removeExistingPilihanImage('{{ strtolower($label) }}')">
                                                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                                    </button>
                                                </div>
                                                @endif
                                                <div class="image-preview-wrapper" id="preview-pilihan-{{ strtolower($label) }}" style="display: none;">
                                                    <img src="" alt="Preview" class="image-preview" style="max-width: 200px; max-height: 150px;">
                                                </div>
                                            </div>
                                            <div class="radio-item">
                                                <input type="radio" 
                                                       name="jawaban_benar" 
                                                       value="{{ $label }}"
                                                       {{ $pil && $pil->is_benar ? 'checked' : '' }}
                                                       required>
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif($soal->jenis_soal == 'benar_salah')
                                    @php
                                        $pilihan = $soal->pilihanJawaban;
                                        $benar = $pilihan->where('label', 'Benar')->first();
                                        $salah = $pilihan->where('label', 'Salah')->first();
                                    @endphp
                                    <div class="pilihan-wrapper">
                                        <div class="pilihan-label">B</div>
                                        <input type="text" 
                                               name="pilihan_benar" 
                                               class="pilihan-input" 
                                               value="Benar" 
                                               readonly>
                                        <div class="radio-item">
                                            <input type="radio" 
                                                   name="jawaban_benar" 
                                                   value="Benar"
                                                   {{ $benar && $benar->is_benar ? 'checked' : '' }}
                                                   required>
                                        </div>
                                    </div>
                                    <div class="pilihan-wrapper">
                                        <div class="pilihan-label">S</div>
                                        <input type="text" 
                                               name="pilihan_salah" 
                                               class="pilihan-input" 
                                               value="Salah" 
                                               readonly>
                                        <div class="radio-item">
                                            <input type="radio" 
                                                   name="jawaban_benar" 
                                                   value="Salah"
                                                   {{ $salah && $salah->is_benar ? 'checked' : '' }}
                                                   required>
                                        </div>
                                    </div>
                                @elseif($soal->jenis_soal == 'mcma')
                                    @php
                                        $pilihan = $soal->pilihanJawaban;
                                    @endphp
                                    @foreach(['A', 'B', 'C', 'D'] as $index => $label)
                                        @php
                                            $pil = $pilihan->where('label', $label)->first();
                                        @endphp
                                        <div class="pilihan-wrapper">
                                            <div class="pilihan-label">{{ $label }}</div>
                                            <div style="flex: 1;">
                                                <input type="text" 
                                                       name="pilihan_{{ strtolower($label) }}" 
                                                       class="pilihan-input" 
                                                       placeholder="Masukkan pilihan {{ $label }}"
                                                       value="{{ $pil ? $pil->teks_jawaban : '' }}"
                                                       required>
                                                <input type="file" 
                                                       name="gambar_pilihan_{{ strtolower($label) }}" 
                                                       class="form-input" 
                                                       accept="image/*" 
                                                       onchange="previewImage(event, 'preview-pilihan-mcma-{{ strtolower($label) }}')"
                                                       style="margin-top: 8px; font-size: 12px;">
                                                @if($pil && $pil->gambar_jawaban)
                                                <div class="image-preview-wrapper" id="existing-pilihan-mcma-{{ strtolower($label) }}">
                                                    <img src="{{ asset('storage/' . $pil->gambar_jawaban) }}" alt="Gambar Pilihan {{ $label }}" class="image-preview" style="max-width: 200px; max-height: 150px;">
                                                    <button type="button" class="image-remove-btn" onclick="removeExistingPilihanMcmaImage('{{ strtolower($label) }}')">
                                                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                                    </button>
                                                </div>
                                                @endif
                                                <div class="image-preview-wrapper" id="preview-pilihan-mcma-{{ strtolower($label) }}" style="display: none;">
                                                    <img src="" alt="Preview" class="image-preview" style="max-width: 200px; max-height: 150px;">
                                                </div>
                                            </div>
                                            <div class="radio-item">
                                                <input type="checkbox" 
                                                       name="jawaban_benar_mcma[]" 
                                                       value="{{ $label }}"
                                                       {{ $pil && $pil->is_benar ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <!-- Tambah Soal Button -->
                            <div style="margin-top: 24px; text-align: center;">
                                <button type="button" class="btn-tambah-soal" onclick="alert('Fitur tambah soal sedang dalam pengembangan')">
                                    <span class="material-symbols-outlined" style="font-size: 24px;">add</span>
                                    TAMBAH SOAL
                                </button>
                            </div>
                        </div>

                        <!-- Kunci Jawaban for Isian/Uraian -->
                        <div id="kunciJawabanSection" style="{{ in_array($soal->jenis_soal, ['isian', 'uraian']) ? '' : 'display: none;' }}">
                            <div class="form-group">
                                <label class="form-label">Kunci Jawaban <span class="required">*</span></label>
                                <textarea name="kunci_jawaban" class="form-textarea" placeholder="Masukkan kunci jawaban">{{ $soal->kunci_jawaban }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="/soal" class="btn btn-secondary">
                            <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                            Update Soal
                        </button>
                    </div>
                </form>
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

        function togglePilihan() {
            const jenisSoal = document.getElementById('jenisSoal').value;
            const pilihanSection = document.getElementById('pilihanSection');
            const kunciJawabanSection = document.getElementById('kunciJawabanSection');

            if (jenisSoal === 'pilihan_ganda' || jenisSoal === 'benar_salah' || jenisSoal === 'mcma') {
                pilihanSection.style.display = 'block';
                kunciJawabanSection.style.display = 'none';
            } else if (jenisSoal === 'isian' || jenisSoal === 'uraian') {
                pilihanSection.style.display = 'none';
                kunciJawabanSection.style.display = 'block';
            } else {
                pilihanSection.style.display = 'none';
                kunciJawabanSection.style.display = 'none';
            }
        }

        function previewImage(event, previewId) {
            const preview = document.getElementById(previewId);
            const img = preview.querySelector('img');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        function removeExistingImage() {
            if (confirm('Hapus gambar pertanyaan?')) {
                document.getElementById('existing-image').style.display = 'none';
            }
        }

        function removeExistingPilihanImage(label) {
            if (confirm('Hapus gambar pilihan ' + label.toUpperCase() + '?')) {
                document.getElementById('existing-pilihan-' + label).style.display = 'none';
            }
        }

        function removeExistingPilihanMcmaImage(label) {
            if (confirm('Hapus gambar pilihan ' + label.toUpperCase() + '?')) {
                document.getElementById('existing-pilihan-mcma-' + label).style.display = 'none';
            }
        }
    </script>
</body>
</html>
