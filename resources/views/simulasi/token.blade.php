<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Token - Simulasi TKA</title>
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
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            margin-bottom: 32px;
            text-align: center;
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

        /* Token Display Card */
        .token-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 24px;
        }

        .token-display {
            background: linear-gradient(135deg, #702637 0%, #a83a52 100%);
            padding: 32px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .token-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .token-value {
            font-size: 48px;
            font-weight: 700;
            color: white;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin-bottom: 8px;
        }

        .token-timer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        .timer-warning {
            color: #fbbf24;
        }

        .timer-expired {
            color: #ef4444;
        }

        /* Info Section */
        .info-section {
            background: #f9fafb;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .info-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-list {
            list-style: none;
            padding: 0;
        }

        .info-list li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: start;
            gap: 8px;
        }

        .info-list li::before {
            content: '•';
            color: #702637;
            font-weight: bold;
            font-size: 20px;
            line-height: 1;
        }

        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
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
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(112, 38, 55, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #702637;
            border: 2px solid #702637;
        }

        .btn-secondary:hover {
            background: #f9f9f9;
        }

        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
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

            .token-card {
                padding: 24px;
            }

            .token-value {
                font-size: 32px;
                letter-spacing: 4px;
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
                        <a href="/simulasi/generate" class="submenu-item">
                            <span class="menu-item-text">Generate Simulasi</span>
                        </a>
                        <a href="/simulasi/token" class="submenu-item active">
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
                        <div style="font-size: 16px; font-weight: 600; color: #333;">Generate Token</div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Token Akses Simulasi</h1>
                    <p class="page-subtitle">Token untuk verifikasi sesi siswa</p>
                </div>

                <div class="alert alert-info">
                    <span class="material-symbols-outlined">info</span>
                    <span>Token ini digunakan siswa untuk memverifikasi sesi mereka saat mengikuti simulasi. Token berlaku selama 1 jam.</span>
                </div>

                <div class="token-card">
                    <div class="token-display">
                        <div class="token-label">Token Akses Aktif</div>
                        <div class="token-value" id="tokenValue">{{ $currentToken->token }}</div>
                        <div class="token-timer" id="tokenTimer">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            <span>Berlaku: <strong id="timeRemaining">59:45</strong></span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="refreshToken()">
                            <span class="material-symbols-outlined">refresh</span>
                            Generate Token Baru
                        </button>
                        <button class="btn btn-secondary" onclick="copyToken()">
                            <span class="material-symbols-outlined">content_copy</span>
                            Salin Token
                        </button>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-title">
                        <span class="material-symbols-outlined">lightbulb</span>
                        Cara Penggunaan Token
                    </div>
                    <ul class="info-list">
                        <li>Token akan otomatis berubah setiap 1 jam untuk keamanan</li>
                        <li>Siswa memasukkan token ini saat login untuk memverifikasi sesi simulasi</li>
                        <li>Token yang sama dapat digunakan oleh semua siswa dalam sesi yang sama</li>
                        <li>Klik "Generate Token Baru" untuk membuat token baru secara manual</li>
                        <li>Token menggunakan 6 huruf kapital (A-Z) tanpa angka</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Get expiry time in ISO format and convert to local timezone
        const expiryTimeString = '{{ $currentToken->expires_at->toIso8601String() }}';
        let tokenExpiryTime = new Date(expiryTimeString).getTime();
        let timerInterval;

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

        function refreshToken() {
            // Call API to generate new token
            fetch('/simulasi/token/refresh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update token display
                document.getElementById('tokenValue').textContent = data.token;
                
                // Reset timer with new expiry time
                tokenExpiryTime = new Date(data.expires_at).getTime();
                updateTimer();
                
                // Show notification
                alert('Token baru telah di-generate!\n\nToken: ' + data.token + '\n' + data.message);
            })
            .catch(error => {
                alert('Gagal generate token: ' + error);
            });
        }

        function copyToken() {
            const token = document.getElementById('tokenValue').textContent;
            navigator.clipboard.writeText(token).then(() => {
                alert('Token berhasil disalin ke clipboard!\n\nToken: ' + token);
            }).catch(err => {
                alert('Gagal menyalin token: ' + err);
            });
        }

        function updateTimer() {
            const now = Date.now();
            const remaining = tokenExpiryTime - now;
            
            if (remaining <= 0) {
                document.getElementById('timeRemaining').textContent = 'EXPIRED';
                const timerElement = document.getElementById('tokenTimer');
                timerElement.classList.add('timer-expired');
                return;
            }
            
            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            
            const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('timeRemaining').textContent = timeString;
            
            // Change color based on time remaining
            const timerElement = document.getElementById('tokenTimer');
            if (minutes < 5) {
                timerElement.classList.add('timer-warning');
            } else {
                timerElement.classList.remove('timer-warning');
            }
        }

        // Start timer
        timerInterval = setInterval(updateTimer, 1000);
        updateTimer();

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
