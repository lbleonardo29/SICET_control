<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus credenciales - SICET</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f1f2f4;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333333;
        }
        .email-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .email-header {
            line-height: 0;
        }
        .email-header img {
            width: 100%;
            display: block;
        }
        .email-body {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 12px;
            color: #1f2937;
        }
        .greeting strong {
            color: #155029;
        }
        .message {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .password-box {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin: 16px 0;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .password-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
        }
        .password-value {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 22px;
            color: #155029;
            letter-spacing: 2px;
            background: #eef6ee;
            padding: 12px 16px;
            border-radius: 8px;
            display: inline-block;
            word-break: break-all;
        }
        .password-hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 10px;
        }
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            margin: 18px 0;
            border-radius: 8px;
            font-size: 13px;
            color: #92400e;
        }
        .btn-container {
            text-align: center;
            margin: 26px 0 8px;
        }
        .btn-link {
            display: inline-block;
            background-color: #155029;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
        }
        a {
            color: #2563eb;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
        @media (max-width: 480px) {
            .email-body {
                padding: 22px;
            }
            .password-value {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ asset('images/sicet_banner.jpg') }}" alt="SICET · Sistema de Control de Equipos">
        </div>

        <div class="email-body">
            <div class="greeting">
                Hola, <strong>{{ $empleado->nombre_completo }}</strong>
            </div>

            <div class="message">
                Bienvenido al sistema <strong>SICET</strong>. Ingresa con tu número de empleado
                y la siguiente contraseña temporal:
            </div>

            <div class="password-box">
                <span class="password-label">Contraseña temporal</span>
                <div class="password-value">{{ $password }}</div>
                <div class="password-hint">Tócala y mantén presionado para copiarla, o selecciónala con doble clic.</div>
            </div>

            <div class="warning-box">
                Esta es una contraseña <strong>temporal</strong>. Deberás cambiarla la primera vez que inicies sesión.
            </div>

            <div class="btn-container">
                <a href="{{ route('login') }}" class="btn-link" style="display:inline-block;background-color:#155029;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-weight:600;">
                    Iniciar sesión en SICET →
                </a>
            </div>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} SICET · Sistema de Control de Equipos</p>
        </div>
    </div>
</body>
</html>
