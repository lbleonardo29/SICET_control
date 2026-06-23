<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — SICET</title>
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
    <div class="login-form-wrap">

        <h2 class="form-title">Recuperar contraseña</h2>
        <p class="form-subtitle">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        @if(session('success'))
            <div class="alert-sicet alert-sicet-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('password.email') }}" id="recoverForm">
            @csrf

            <div class="field-group">
                <svg class="field-icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input
                    type="email"
                    name="correo"
                    class="field-input {{ $errors->has('correo') ? 'is-invalid' : '' }}"
                    value="{{ old('correo') }}"
                    placeholder="tucorreo@fruitex.com.mx"
                    autocomplete="email"
                    required>
            </div>

            <button type="submit" class="btn-sicet-primary" id="submitBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                <span id="btnText">Enviar enlace de recuperación</span>
            </button>

        </form>

        <a href="{{ route('login') }}" class="back-to-login">
            ← Volver al inicio de sesión
        </a>

    </div>
</main>

<script>
    document.getElementById('recoverForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        document.getElementById('btnText').textContent = 'Enviando...';
    });
</script>

</body>
</html>
