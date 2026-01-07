<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Simulasi TKA</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        :root {
            --md-sys-color-primary: #C85A5A;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #FFE5E5;
            --md-sys-color-on-primary-container: #5C2020;
            --md-sys-color-secondary: #D88080;
            --md-sys-color-on-secondary: #FFFFFF;
            --md-sys-color-surface: #FFFBFF;
            --md-sys-color-on-surface: #1C1B1F;
            --md-sys-color-surface-variant: #F5E8E8;
            --md-sys-color-on-surface-variant: #49454F;
            --md-sys-color-outline: #9E9E9E;
            --md-sys-color-error: #D68585;
            --md-sys-color-on-error: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #C85A5A 0%, #D88080 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--md-sys-color-surface);
            border-radius: 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 400px;
            padding: 40px 32px;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 72px;
            height: 72px;
            background: var(--md-sys-color-primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(200, 90, 90, 0.25);
        }

        .logo .material-symbols-outlined {
            font-size: 40px;
            color: var(--md-sys-color-on-primary);
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
        }

        h1 {
            font-size: 28px;
            font-weight: 500;
            color: var(--md-sys-color-on-surface);
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--md-sys-color-on-surface-variant);
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
        }

        .input-field {
            width: 100%;
            padding: 16px;
            border: 1px solid var(--md-sys-color-outline);
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            color: var(--md-sys-color-on-surface);
            background: var(--md-sys-color-surface);
            transition: all 0.2s ease;
            outline: none;
        }

        .input-field:focus {
            border-color: var(--md-sys-color-primary);
            border-width: 2px;
            padding: 15px;
        }

        .input-field:focus + .input-label,
        .input-field:not(:placeholder-shown) + .input-label {
            top: -8px;
            left: 12px;
            font-size: 12px;
            background: var(--md-sys-color-surface);
            padding: 0 4px;
            color: var(--md-sys-color-primary);
        }

        .input-label {
            position: absolute;
            top: 16px;
            left: 16px;
            font-size: 16px;
            color: var(--md-sys-color-on-surface-variant);
            pointer-events: none;
            transition: all 0.2s ease;
        }

        .error-message {
            color: var(--md-sys-color-error);
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .error-message .material-symbols-outlined {
            font-size: 16px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            cursor: pointer;
            accent-color: var(--md-sys-color-primary);
        }

        .checkbox-label {
            font-size: 14px;
            color: var(--md-sys-color-on-surface-variant);
            cursor: pointer;
            user-select: none;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            border: none;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Roboto', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(200, 90, 90, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #B54949;
            box-shadow: 0 4px 16px rgba(200, 90, 90, 0.35);
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(200, 90, 90, 0.25);
        }

        .btn-primary .material-symbols-outlined {
            font-size: 20px;
        }

        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: var(--md-sys-color-outline);
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .divider-text {
            font-size: 14px;
            color: var(--md-sys-color-on-surface-variant);
            padding: 0 16px;
            background: var(--md-sys-color-surface);
            position: relative;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--md-sys-color-on-surface-variant);
        }

        .footer-text a {
            color: var(--md-sys-color-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <h1>Selamat Datang</h1>
            <p class="subtitle">Silakan masuk ke akun Anda</p>
        </div>

        @if ($errors->any())
            <div class="error-message" style="margin-bottom: 16px;">
                <span class="material-symbols-outlined">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        name="email" 
                        id="email" 
                        class="input-field" 
                        placeholder=" "
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                    >
                    <label for="email" class="input-label">Username</label>
                </div>
                @error('email')
                    <div class="error-message">
                        <span class="material-symbols-outlined">error</span>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="input-field" 
                        placeholder=" "
                        required
                        autocomplete="current-password"
                    >
                    <label for="password" class="input-label">Password</label>
                </div>
                @error('password')
                    <div class="error-message">
                        <span class="material-symbols-outlined">error</span>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="checkbox-wrapper">
                <input 
                    type="checkbox" 
                    name="remember" 
                    id="remember" 
                    class="checkbox"
                >
                <label for="remember" class="checkbox-label">Ingat saya</label>
            </div>

            <button type="submit" class="btn-primary">
                <span>Masuk</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <div class="divider">
            <span class="divider-text">atau</span>
        </div>

        <div class="footer-text">
            Belum punya akun? <a href="#">Daftar sekarang</a>
        </div>
    </div>
</body>
</html>
