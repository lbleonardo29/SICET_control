<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña — SICET</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sicet.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/sicet-login.css') }}">
</head>
<body>

{{-- LEFT PANEL --}}
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
            Enlace válido desde tu correo
        </div>
        <div class="login-feature">
            <span class="feature-check">✓</span>
            Usa mínimo 8 caracteres
        </div>
        <div class="login-feature">
            <span class="feature-check">✓</span>
            Acceso seguro con nueva contraseña
        </div>
    </div>

    <p class="login-copyright">
        © {{ date('Y') }} Fruitex de México · Acceso restringido a personal autorizado
    </p>
</aside>

{{-- RIGHT PANEL --}}
<main class="login-right">
    <div class="login-form-wrap">

        <h2 class="form-title">Nueva contraseña</h2>
        <p class="form-subtitle">
            Ingresa y confirma tu nueva contraseña para recuperar el acceso a tu cuenta.
        </p>

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

        <form method="POST" action="{{ route('password.update') }}" id="resetForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            {{-- Nueva contraseña --}}
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
                    placeholder="Nueva contraseña (mín. 8 caracteres)"
                    autocomplete="new-password"
                    required>
                <button type="button" class="eye-btn" id="eyeBtn1" aria-label="Mostrar contraseña">
                    <svg id="eye1Show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg id="eye1Hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>

            {{-- Confirmar contraseña --}}
            <div class="field-group" style="margin-bottom:24px">
                <svg class="field-icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"/>
                    <path d="M21 12c0 4.97-4.03 9-9 9S3 16.97 3 12 7.03 3 12 3s9 4.03 9 9z"/>
                </svg>
                <input
                    type="password"
                    name="password_confirmation"
                    id="passwordConfirm"
                    class="field-input"
                    placeholder="Confirmar nueva contraseña"
                    autocomplete="new-password"
                    required>
                <button type="button" class="eye-btn" id="eyeBtn2" aria-label="Mostrar confirmación">
                    <svg id="eye2Show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg id="eye2Hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn-sicet-primary" id="submitBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span id="btnText">Cambiar contraseña</span>
            </button>

        </form>

        <a href="{{ route('login') }}" class="back-to-login">
            ← Volver al inicio de sesión
        </a>

    </div>
</main>

<script>
    function toggleEye(inputId, showId, hideId) {
        var inp = document.getElementById(inputId);
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        document.getElementById(showId).style.display = isPass ? 'none' : '';
        document.getElementById(hideId).style.display = isPass ? '' : 'none';
    }

    document.getElementById('eyeBtn1').addEventListener('click', function () {
        toggleEye('passwordInput', 'eye1Show', 'eye1Hide');
    });

    document.getElementById('eyeBtn2').addEventListener('click', function () {
        toggleEye('passwordConfirm', 'eye2Show', 'eye2Hide');
    });

    document.getElementById('resetForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        document.getElementById('btnText').textContent = 'Cambiando...';
    });
</script>

</body>
</html>
