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

    {{-- Decoraciones Fruitex — mangos --}}
    @php
        $mangos = [
            ['cls' => 'fruit-mango-1', 'id' => 'mg1'],
            ['cls' => 'fruit-mango-2', 'id' => 'mg2'],
            ['cls' => 'fruit-mango-3', 'id' => 'mg3'],
        ];
    @endphp
    @foreach($mangos as $m)
    <svg class="fruit-deco {{ $m['cls'] }}" viewBox="0 0 200 240" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="{{ $m['id'] }}" x1="15%" y1="10%" x2="90%" y2="95%">
                <stop offset="0%"   stop-color="#5AB14F"/>
                <stop offset="32%"  stop-color="#B6D34A"/>
                <stop offset="58%"  stop-color="#F7C53D"/>
                <stop offset="82%"  stop-color="#F18C2E"/>
                <stop offset="100%" stop-color="#E8552F"/>
            </linearGradient>
        </defs>
        {{-- cuerpo del mango --}}
        <path d="M70 34 C120 18 182 54 186 122 C190 188 140 230 92 226 C44 222 16 176 18 128 C20 76 38 48 70 34 Z"
              fill="url(#{{ $m['id'] }})"/>
        {{-- brillo suave --}}
        <path d="M64 64 C92 50 120 58 132 84 C112 70 86 70 64 64 Z" fill="#ffffff" opacity="0.22"/>
        {{-- hoja --}}
        <path d="M96 34 C92 10 112 0 134 6 C123 20 110 30 96 34 Z" fill="#2E8B40"/>
        {{-- tallo --}}
        <path d="M96 34 C99 24 101 18 104 10" stroke="#7A4B22" stroke-width="4" fill="none" stroke-linecap="round"/>
    </svg>
    @endforeach

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
