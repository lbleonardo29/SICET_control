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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
        }
        .email-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 35px 20px;
            text-align: center;
            color: white;
        }
        .email-header h1 {
            font-size: 32px;
            margin: 0;
        }
        .logo-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 35px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .greeting strong {
            color: #2a5298;
        }
        .message {
            color: #4a5568;
            margin-bottom: 25px;
        }
        .password-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .password-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
        }
        .password-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 22px;
            color: #10b981;
            letter-spacing: 1px;
            background: #ecfdf5;
            padding: 12px 20px;
            border-radius: 10px;
            display: inline-block;
        }
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 12px;
            font-size: 13px;
            color: #92400e;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 25px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        @media (max-width: 480px) {
            .email-body {
                padding: 25px;
            }
            .password-value {
                font-size: 18px;
                word-break: break-all;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-icon">🖥️</div>
            <h1>SICET</h1>
        </div>

        <div class="email-body">
            <div class="greeting">
                Hola, <strong>{{ $empleado->nombre_completo }}</strong>
            </div>

            <div class="message">
                Bienvenido al sistema <strong>SICET</strong>. Ingresa al sistema con tu número de empleado y la siguiente contraseña temporal:
            </div>

            <div class="password-box">
                <span class="password-label">🔐 CONTRASEÑA TEMPORAL</span>
                <div class="password-value">{{ $password }}</div>
            </div>

            <div class="warning-box">
                ⚠️ Esta es una contraseña <strong>temporal</strong>. Deberás cambiarla la primera vez que inicies sesión.
            </div>

            <div class="btn-container">
                <a href="{{ route('login') }}" class="btn">
                    🔓 Iniciar sesión en SICET
                </a>
            </div>
        </div>

        <div class="email-footer">
            <p>© {{ date('Y') }} SICET - Sistema de Control de Computadoras</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>