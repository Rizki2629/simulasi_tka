<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Siswa - Simulasi TKA</title>
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
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            background: #1e3a8a;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header .material-symbols-outlined {
            font-size: 64px;
            font-variation-settings: 'FILL' 1, 'wght' 400;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E0E0E0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Roboto', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input::placeholder {
            color: #999;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #FEE;
            color: #C33;
            border: 1px solid #FCC;
        }

        .alert .material-symbols-outlined {
            font-size: 20px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .back-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .input-icon {
            position: relative;
        }

        .input-icon .material-symbols-outlined {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 20px;
            cursor: pointer;
            user-select: none;
        }

        .input-icon .material-symbols-outlined:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <span class="material-symbols-outlined">school</span>
            <h1>Simulasi TKA</h1>
            <p>Login untuk memulai simulasi</p>
        </div>

        <div class="login-body">
            @if(session('error'))
            <div class="alert alert-danger">
                <span class="material-symbols-outlined">error</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <form action="/simulasi/student-login" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nisn" class="form-label">NISN</label>
                    <input 
                        type="text" 
                        id="nisn" 
                        name="nisn" 
                        class="form-input" 
                        placeholder="Masukkan NISN Anda"
                        required
                        maxlength="10"
                        value="{{ old('nisn') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-icon">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Masukkan password Anda"
                            required
                        >
                        <span class="material-symbols-outlined" onclick="togglePassword()">visibility</span>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Masuk
                </button>
            </form>

            <div class="back-link">
                <a href="/">← Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = event.target;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        // Auto-format NISN input (numbers only)
        document.getElementById('nisn').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Handle form submission with auto-retry on 419 (Page Expired)
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Memproses...';

            const formData = new FormData(form);

            function submitForm(retryCount) {
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/json',
                    },
                    redirect: 'follow',
                    credentials: 'same-origin'
                }).then(function(response) {
                    if (response.status === 419 && retryCount < 3) {
                        // CSRF expired — fetch fresh token and retry
                        return fetch('/simulasi/login', { credentials: 'same-origin' })
                            .then(function(r) { return r.text(); })
                            .then(function(html) {
                                var match = html.match(/name="_token"\s+value="([^"]+)"/);
                                if (match) {
                                    formData.set('_token', match[1]);
                                    var metaToken = document.querySelector('meta[name="csrf-token"]');
                                    if (metaToken) metaToken.content = match[1];
                                    var hiddenToken = form.querySelector('input[name="_token"]');
                                    if (hiddenToken) hiddenToken.value = match[1];
                                }
                                return submitForm(retryCount + 1);
                            });
                    }
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }
                    if (response.ok) {
                        // Some redirects don't report as redirected
                        return response.text().then(function(html) {
                            if (response.url !== window.location.href) {
                                window.location.href = response.url;
                            } else {
                                document.open();
                                document.write(html);
                                document.close();
                            }
                        });
                    }
                    // Other error — reload the page
                    return response.text().then(function(html) {
                        document.open();
                        document.write(html);
                        document.close();
                    });
                }).catch(function(err) {
                    if (retryCount < 2) {
                        setTimeout(function() { submitForm(retryCount + 1); }, 1000);
                    } else {
                        btn.disabled = false;
                        btn.textContent = originalText;
                        alert('Gagal menghubungi server. Silakan coba lagi.');
                    }
                });
            }

            submitForm(0);
        });
    </script>
</body>
</html>
