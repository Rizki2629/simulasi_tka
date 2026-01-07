<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Data Peserta - Simulasi TKA</title>
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
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            min-height: 100vh;
            padding: 0;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 16px 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .navbar-brand img {
            width: 40px;
            height: 40px;
        }

        .navbar-title {
            display: flex;
            flex-direction: column;
        }

        .navbar-title-main {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .navbar-title-sub {
            font-size: 12px;
            font-weight: 400;
            color: #666;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #333;
        }

        .container-wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 70px;
        }

        .left-side {
            width: 280px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            padding-top: 40px;
        }

        .token-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .token-display {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .token-label {
            font-size: 16px;
            color: #666;
        }

        .token-value {
            font-size: 32px;
            font-weight: 700;
            color: #0284c7;
            letter-spacing: 5px;
            text-align: center;
        }

        .refresh-btn {
            width: 100%;
            padding: 10px 16px;
            background: #0284c7;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: #0369a1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .center-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 40px 50px 40px 20px;
        }

        .form-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        .right-side {
            width: 0;
        }

        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
        }
        
        /* Removed duplicate .form-title rule */

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            background: #F9FAFB;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #0284c7;
            background: white;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }

        .name-input-wrapper {
            position: relative;
        }

        .name-validation-badge {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: none;
        }

        .name-validation-badge.valid {
            background: #DEF7EC;
            color: #03543F;
            display: block;
        }

        .name-validation-badge.invalid {
            background: #FDE8E8;
            color: #9B1C1C;
            display: block;
        }

        .form-input:disabled {
            background: #F3F4F6;
            color: #6B7280;
            cursor: not-allowed;
        }

        .form-input::placeholder {
            color: #9CA3AF;
        }

        .date-group {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #0284c7;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: #0369a1;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .container-wrapper {
                flex-direction: column;
            }

            .left-side {
                width: 100%;
                padding-top: 20px;
            }

            .right-side {
                width: 100%;
            }

            .token-value {
                font-size: 20px;
            }

            .form-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="24" fill="#0284c7"/>
                <path d="M24 12L28 20H32L26 28L28 36L24 32L20 36L22 28L16 20H20L24 12Z" fill="white"/>
            </svg>
            <div class="navbar-title">
                <div class="navbar-title-main">SDN GROGOL UTARA 09</div>
                <div class="navbar-title-sub">SIMULASI TKA</div>
            </div>
        </div>
        <div class="navbar-user">
            <span>{{ $student->nisn }} - {{ strtoupper($student->name) }}</span>
            <a href="{{ route('simulasi.riwayat.nilai') }}" style="color: #0284c7; text-decoration: none; display: flex; align-items: center; margin-right: 16px;" title="Riwayat Nilai">
                <span class="material-symbols-outlined" style="font-size: 20px;">assessment</span>
            </a>
            <a href="/simulasi/student-logout" style="color: #0284c7; text-decoration: none; display: flex; align-items: center;">
                <span class="material-symbols-outlined" style="font-size: 20px;">logout</span>
            </a>
        </div>
    </nav>

    <div class="container-wrapper">
        <div class="left-side">
            <div class="token-section">
                <div class="token-display">
                    <div class="token-label">Token :</div>
                    <div class="token-value" id="tokenValue">{{ $currentToken->token }}</div>
                    <button class="refresh-btn" type="button" onclick="refreshToken()">Refresh</button>
                </div>
            </div>
        </div>

        <div class="center-content">
            <div class="form-section">
                <h2 class="form-title">Konfirmasi data Peserta</h2>
                
                @if(session('error'))
                <div style="background: #FDE8E8; color: #9B1C1C; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    {{ session('error') }}
                </div>
                @endif

                @if(session('success'))
                <div style="background: #DEF7EC; color: #03543F; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    {{ session('success') }}
                </div>
                @endif
                
                <form action="/simulasi/confirm-data" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nomor Peserta</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            value="{{ $student->nisn }}"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Peserta</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            value="{{ strtoupper($student->name) }}"
                            disabled
                        >
                        <input type="hidden" id="originalName" value="{{ strtoupper($student->name) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-Laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mata Ujian</label>
                        <select name="mata_ujian" class="form-select" required>
                            <option value="">Pilih Mata Ujian</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                            <option value="IPA">IPA</option>
                            <option value="IPS">IPS</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Peserta</label>
                        <div class="name-input-wrapper">
                            <input 
                                type="text" 
                                name="nama_peserta"
                                id="namaPeserta"
                                class="form-input" 
                                placeholder="Ketikkan Nama Peserta"
                                required
                                oninput="validateName()"
                                style="padding-right: 120px;"
                            >
                            <span class="name-validation-badge" id="nameBadge"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <div class="date-group">
                            <select name="hari" class="form-select" required>
                                <option value="">Hari</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ $student->tanggal_lahir && \Carbon\Carbon::parse($student->tanggal_lahir)->day == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <select name="bulan" class="form-select" required>
                                <option value="">Bulan</option>
                                @php
                                    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                @endphp
                                @foreach($bulan as $index => $nama_bulan)
                                    <option value="{{ $index + 1 }}" {{ $student->tanggal_lahir && \Carbon\Carbon::parse($student->tanggal_lahir)->month == ($index + 1) ? 'selected' : '' }}>
                                        {{ $nama_bulan }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="tahun" class="form-select" required>
                                <option value="">Tahun</option>
                                @for($i = date('Y'); $i >= 1990; $i--)
                                    <option value="{{ $i }}" {{ $student->tanggal_lahir && \Carbon\Carbon::parse($student->tanggal_lahir)->year == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Token</label>
                        <input 
                            type="text" 
                            name="token"
                            class="form-input" 
                            placeholder="Ketikkan token di sini"
                            required
                            maxlength="6"
                            style="text-transform: uppercase;"
                        >
                    </div>

                    <button type="submit" class="btn-submit">Submit</button>
                </form>
            </div>
        </div>

        <div class="right-side"></div>
    </div>

    <script>
        let currentTokenValue = "{{ $currentToken->token }}";

        function refreshToken() {
            // Check if token has changed in database
            fetch('/simulasi/update-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newToken = data.token;
                    
                    if (newToken !== currentTokenValue) {
                        // Token has changed, update display
                        document.getElementById('tokenValue').textContent = newToken;
                        currentTokenValue = newToken;
                        
                        // Show notification that token has changed
                        alert('Token telah berganti: ' + newToken);
                    } else {
                        // Token hasn't changed
                        alert('Token masih sama: ' + newToken);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal refresh token');
            });
        }

        function validateName() {
            const inputName = document.getElementById('namaPeserta').value.toUpperCase().trim();
            const originalName = document.getElementById('originalName').value.trim();
            const badge = document.getElementById('nameBadge');
            
            if (inputName === '') {
                badge.style.display = 'none';
                badge.className = 'name-validation-badge';
                return;
            }
            
            if (inputName === originalName) {
                badge.className = 'name-validation-badge valid';
                badge.textContent = 'Nama sesuai';
            } else {
                badge.className = 'name-validation-badge invalid';
                badge.textContent = 'Nama tidak sesuai';
            }
        }

        // Auto uppercase token input
        document.querySelector('input[name="token"]').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
        });

        // Auto uppercase name input
        document.getElementById('namaPeserta').addEventListener('input', function(e) {
            const cursorPos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(cursorPos, cursorPos);
        });
    </script>
</body>
</html>
