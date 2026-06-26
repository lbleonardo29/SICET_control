<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — SICET</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sicet.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/sicet-login.css') }}">
</head>
<body>

{{-- ===== LEFT PANEL ===== --}}
<aside class="login-left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-logo-box">
        @if(file_exists(public_path('images/fruitex-logo.png')))
            <img src="{{ asset('images/fruitex-logo.png') }}" alt="Fruitex de México">
        @else
            <span class="login-logo-placeholder">FX</span>
        @endif
    </div>

    <div class="login-brand">
        <h1>SICET</h1>
        <p class="tagline">
            Sistema de Control de Equipos<br>
            <span class="company">Fruitex de México</span>
        </p>
    </div>

    <div class="login-features">
        <div class="login-feature">
            <span class="feature-check">✓</span>
            Gestión de equipos de cómputo
        </div>
        <div class="login-feature">
            <span class="feature-check">✓</span>
            Control de dispositivos móviles
        </div>
        <div class="login-feature">
            <span class="feature-check">✓</span>
            Historial completo de asignaciones
        </div>
    </div>

    <p class="login-copyright">
         {{ date('Y') }} Fruitex de México · Acceso restringido a personal autorizado
    </p>
</aside>

{{-- ===== RIGHT PANEL ===== --}}
<main class="login-right">

    {{-- Decoraciones Fruitex --}}
    <svg class="fruit-deco fruit-mango" viewBox="0 0 210 250" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M105 18 C142 18 172 50 175 102 C178 158 158 220 105 238 C52 220 32 158 35 102 C38 50 68 18 105 18Z"/>
        <path d="M105 18 C109 3 122 -3 129 5 C120 9 112 14 105 18Z" opacity="0.82"/>
        <path d="M114 10 C138 -5 162 6 153 26 C136 16 123 11 114 10Z" opacity="0.72"/>
    </svg>

    <svg class="fruit-deco fruit-orange" viewBox="0 0 155 155" fill="none" stroke="currentColor" stroke-width="5" xmlns="http://www.w3.org/2000/svg">
        <circle cx="77" cy="77" r="70"/>
        <line x1="7" y1="77" x2="147" y2="77"/>
        <line x1="77" y1="7" x2="77" y2="147"/>
        <line x1="28" y1="28" x2="126" y2="126"/>
        <line x1="126" y1="28" x2="28" y2="126"/>
        <circle cx="77" cy="77" r="19" fill="currentColor" stroke="none"/>
    </svg>

    <svg class="fruit-deco fruit-leaf-a" viewBox="0 0 52 68" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M26 4 C44 17 48 48 26 65 C4 48 8 17 26 4Z"/>
        <line x1="26" y1="4" x2="26" y2="65" stroke="white" stroke-width="1.5" fill="none" opacity="0.35"/>
    </svg>

    <svg class="fruit-deco fruit-leaf-b" viewBox="0 0 42 56" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M21 3 C37 13 40 40 21 53 C2 40 5 13 21 3Z"/>
    </svg>

    <svg class="fruit-deco fruit-dots" viewBox="0 0 76 76" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <circle cx="9"  cy="9"  r="5"/><circle cx="28" cy="9"  r="3.5"/><circle cx="9"  cy="28" r="3.5"/>
        <circle cx="47" cy="9"  r="2.5"/><circle cx="28" cy="28" r="5"/><circle cx="9"  cy="47" r="2.5"/>
        <circle cx="47" cy="28" r="3"/><circle cx="66" cy="9"  r="2"/><circle cx="28" cy="47" r="3"/>
        <circle cx="47" cy="47" r="5"/><circle cx="66" cy="28" r="2.5"/><circle cx="66" cy="47" r="3.5"/>
        <circle cx="66" cy="66" r="5"/><circle cx="47" cy="66" r="3"/><circle cx="28" cy="66" r="2.5"/>
        <circle cx="9"  cy="66" r="2"/>
    </svg>

    <div class="login-form-wrap">

        <h2 class="form-title">Bienvenido</h2>
        <p class="form-subtitle">Ingresa tus credenciales para acceder al sistema</p>

        @if($errors->any())
            <div class="alert-sicet alert-sicet-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-sicet alert-sicet-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf

            {{-- Número de empleado o correo --}}
            <div class="field-group">
                <svg class="field-icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <input
                    type="text"
                    name="email"
                    id="loginInput"
                    class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="Número de empleado"
                    autocomplete="username"
                    required>
            </div>

            {{-- Contraseña --}}
            <div class="field-group">
                <svg class="field-icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                    type="password"
                    name="password"
                    id="passwordInput"
                    class="field-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    required>
                <button type="button" class="eye-btn" id="eyeBtn" aria-label="Mostrar contraseña">
                    <svg id="eyeIconShow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg id="eyeIconHide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>

            {{-- Recordarme + Olvidé contraseña --}}
            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Recordarme
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link-right">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" class="btn-sicet-primary" id="submitBtn">
                <span id="btnText">Ingresar al sistema</span>
            </button>

        </form>
    </div>
</main>

<script>
    document.getElementById('eyeBtn').addEventListener('click', function () {
        var inp = document.getElementById('passwordInput');
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        document.getElementById('eyeIconShow').style.display = isPass ? 'none' : '';
        document.getElementById('eyeIconHide').style.display = isPass ? '' : 'none';
    });

    document.getElementById('loginForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        document.getElementById('btnText').textContent = 'Ingresando...';
    });
</script>

</body>
</html>
