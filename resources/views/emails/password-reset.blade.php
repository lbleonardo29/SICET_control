<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña - SICET</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SICET</h2>
            <p>Sistema de Control de Equipos</p>
        </div>

        <div class="content">
            <h3>Hola,</h3>
            
            <p>Se ha solicitado restablecer la contraseña para el siguiente empleado:</p>
            
            <div class="info-box">
                <strong>👤 Empleado:</strong> {{ $empleado->nombre_completo }}<br>
                <strong>📧 Correo del empleado:</strong> {{ $empleado->correo }}<br>
                <strong>🔢 Número de empleado:</strong> {{ $empleado->numero_empleado }}
            </div>
            
            <p>Para restablecer la contraseña, haz clic en el siguiente botón:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button" style="color: white;">
                    Restablecer Contraseña
                </a>
            </div>
            
            <p>O copia y pega este enlace:</p>
            <p style="word-break: break-all; font-size: 12px; color: #666;">
                {{ $resetUrl }}
            </p>
            
            <div class="warning">
                <strong>⚠️ Nota de prueba:</strong>
                <p style="margin: 5px 0 0;">
                    si no reconoce este correo ignorelo
                </p>
            </div>
        </div>

        <div class="footer">
            <p>SICET - Sistema de Control de Equipos Tecnológicos</p>
            <p>Este es un mensaje automático, por favor no responder a este correo.</p>
        </div>
    </div>
</body>
</html>