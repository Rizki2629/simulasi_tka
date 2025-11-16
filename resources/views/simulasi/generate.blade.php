<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Simulasi - Simulasi TKA</title>
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

        /* Form Styles */
        .form-card {
            background: white;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-select,
        .form-input,
        .form-input[type="datetime-local"],
        .form-input[type="number"],
        textarea.form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .form-select:focus,
        .form-input:focus,
        textarea.form-input:focus {
            outline: none;
            border-color: #702637;
            box-shadow: 0 0 0 3px rgba(112, 38, 55, 0.1);
        }

        /* Checkbox List */
        .checkbox-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 6px;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .checkbox-item:hover {
            background: #f5f5f5;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            cursor: pointer;
            accent-color: #702637;
        }

        .checkbox-label {
            flex: 1;
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }

        .checkbox-meta {
            font-size: 12px;
            color: #999;
        }

        /* Select All */
        .select-all-container {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .select-all-item {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #702637;
        }

        .select-all-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            cursor: pointer;
            accent-color: #702637;
        }

        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: #702637;
            color: white;
        }

        .btn-primary:hover {
            background: #5a1e2d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(112, 38, 55, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
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

            .form-card {
                padding: 20px;
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
                        <a href="/soal" class="submenu-item">
                            <span class="menu-item-text">Daftar Soal</span>
                        </a>
                        <a href="/simulasi/generate" class="submenu-item active">
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
                        <div style="font-size: 16px; font-weight: 600; color: #333;">Generate Simulasi</div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Generate Simulasi</h1>
                    <p class="page-subtitle">Buat sesi simulasi baru dengan memilih soal dan peserta didik</p>
                </div>

                @if(session('error'))
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <span class="material-symbols-outlined">error</span>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <span class="material-symbols-outlined">error</span>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form id="formSimulasi" method="POST" action="/simulasi/generate">
                    @csrf
                    <div class="form-card">
                        <!-- Pilih Soal -->
                        <div class="form-section">
                            <h3 class="section-title">Pilih Soal</h3>
                            <div class="alert alert-info">
                                <span class="material-symbols-outlined">info</span>
                                <span>Pilih satu paket soal yang akan digunakan untuk simulasi</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Paket Soal</label>
                                <select class="form-select" name="mata_pelajaran_id" required>
                                    <option value="">-- Pilih Paket Soal --</option>
                                    @forelse($paketSoal as $paket)
                                        <option value="{{ $paket['id'] }}">{{ $paket['label'] }}</option>
                                    @empty
                                        <option value="" disabled>Belum ada soal tersedia</option>
                                    @endforelse
                                </select>
                                @if($paketSoal->isEmpty())
                                <small class="text-muted" style="display: block; margin-top: 8px; color: #EF4444;">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">warning</span>
                                    Belum ada soal yang dibuat. Silakan buat soal terlebih dahulu.
                                </small>
                                @endif
                            </div>
                        </div>

                        <!-- Pilih Peserta -->
                        <div class="form-section">
                            <h3 class="section-title">Pilih Peserta Didik</h3>
                            <div class="alert alert-info">
                                <span class="material-symbols-outlined">info</span>
                                <span>Pilih siswa yang akan mengikuti simulasi ini</span>
                            </div>
                            
                            <div class="select-all-container">
                                <label class="select-all-item">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    <span>Pilih Semua Siswa</span>
                                </label>
                            </div>

                            <div class="checkbox-list">
                                @foreach($students as $student)
                                <label class="checkbox-item">
                                    <input type="checkbox" class="student-checkbox" name="peserta[]" value="{{ $student->id }}">
                                    <span class="checkbox-label">
                                        <div>{{ $student->name }}</div>
                                        <div class="checkbox-meta">NISN: {{ $student->nisn }} • Rombel: {{ $student->rombongan_belajar }}</div>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Pengaturan Simulasi -->
                        <div class="form-section">
                            <h3 class="section-title">Pengaturan Simulasi</h3>
                            <div class="form-group">
                                <label class="form-label required">Nama Simulasi</label>
                                <input type="text" class="form-input" name="nama_simulasi" placeholder="Contoh: Simulasi UTS Matematika" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-input" name="deskripsi" placeholder="Contoh: Simulasi UTS Semester Ganjil 2025" rows="3"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label required">Waktu Mulai</label>
                                    <input type="datetime-local" class="form-input" name="waktu_mulai" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Waktu Selesai</label>
                                    <input type="datetime-local" class="form-input" name="waktu_selesai" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Durasi Pengerjaan (Menit)</label>
                                <input type="number" class="form-input" name="durasi_menit" placeholder="Contoh: 90" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <span class="material-symbols-outlined">close</span>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined">play_circle</span>
                            Generate Simulasi
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

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.student-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        // Update select all when individual checkboxes change
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.student-checkbox');
                const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
                
                selectAll.checked = checkedCount === checkboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            });
        });

        // Form validation
        document.getElementById('formSimulasi').addEventListener('submit', function(e) {
            const checkedStudents = document.querySelectorAll('.student-checkbox:checked').length;
            
            if (checkedStudents === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 peserta didik!');
                return false;
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
    </script>
</body>
</html>
