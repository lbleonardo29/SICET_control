<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva asignación pendiente - SICET</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        /* HEADER */
        .email-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 35px 20px;
            text-align: center;
            color: white;
        }
        .email-header h1 {
            font-size: 32px;
            margin: 0 0 5px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .email-header p {
            font-size: 14px;
            opacity: 0.85;
            margin: 0;
        }
        .logo-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        /* CONTENIDO */
        .email-body {
            padding: 35px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .greeting strong {
            color: #2a5298;
        }
        .message {
            color: #4a5568;
            margin-bottom: 25px;
        }
        /* INFO BOX */
        .info-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }
        .info-card h3 {
            color: #2a5298;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table tr {
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table tr:last-child {
            border-bottom: none;
        }
        .info-table td {
            padding: 10px 0;
            vertical-align: top;
        }
        .info-label {
            width: 35%;
            font-weight: 600;
            color: #4a5568;
        }
        .info-value {
            width: 65%;
            color: #1a202c;
            font-weight: 500;
        }
        .badge-pending {
            display: inline-block;
            background-color: #fef3c7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #d97706;
        }
        /* BOTÓN ÚNICO */
        .btn-group {
            text-align: center;
            margin: 30px 0 25px;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            margin: 0 6px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            text-align: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(30,60,114,0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(30,60,114,0.4);
        }
        /* INFO BOX */
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 12px;
            font-size: 13px;
            color: #1e40af;
        }
        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
        /* FOOTER */
        .email-footer {
            background-color: #f8fafc;
            padding: 25px 35px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .company-info {
            margin-top: 10px;
            font-size: 11px;
            color: #94a3b8;
        }
        @media (max-width: 480px) {
            .email-body {
                padding: 25px;
            }
            .btn {
                display: block;
                margin: 10px auto;
                width: 80%;
            }
            .info-table td {
                display: block;
                width: 100%;
            }
            .info-label {
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- HEADER --}}
        <div class="email-header">
            <div class="logo-icon"></div>
            <h1>SICET</h1>
            <p>Sistema de Control de Computadoras</p>
        </div>

        {{-- BODY --}}
        <div class="email-body">
            <div class="greeting">
                Hola, <strong>{{ $asignacion->empleado->nombre_completo ?? $asignacion->empleado->nombre ?? 'Empleado' }}</strong>
            </div>

            <div class="message">
                Se ha generado una solicitud de asignación de <strong>{{ $tipo == 'equipo' ? 'computadora' : 'dispositivo móvil' }}</strong> a tu nombre. Por favor, revisa los detalles a continuación:
            </div>

            {{-- INFO CARD --}}
            <div class="info-card">
                <h3>
                     Detalles del dispositivo
                </h3>
                <table class="info-table">
                    @if($tipo == 'equipo')
                        <tr>
                            <td class="info-label">Computadora:</td>
                            <td class="info-value"><strong>{{ $asignacion->equipo->marca ?? 'N/A' }} {{ $asignacion->equipo->modelo ?? '' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Código interno:</td>
                            <td class="info-value">{{ $asignacion->equipo->codigo_interno ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">No. de serie:</td>
                            <td class="info-value">{{ $asignacion->equipo->numero_serie ?? 'N/A' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="info-label">Dispositivo:</td>
                            <td class="info-value"><strong>{{ $asignacion->dispositivo->marca ?? 'N/A' }} {{ $asignacion->dispositivo->modelo ?? '' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Código interno:</td>
                            <td class="info-value">{{ $asignacion->dispositivo->codigo_interno ?? 'N/A' }}</td>
                        </tr>
                        @if($asignacion->dispositivo->imei)
                        <tr>
                            <td class="info-label">IMEI:</td>
                            <td class="info-value">{{ $asignacion->dispositivo->imei }}</td>
                        </tr>
                        @endif
                    @endif
                    <tr>
                        <td class="info-label">Fecha de asignación:</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Estado:</td>
                        <td class="info-value"><span class="badge-pending"> Pendiente de confirmación</span></td>
                    </tr>
                </table>
            </div>

            {{-- BOTÓN ÚNICO --}}
            <div class="btn-group">
                <a href="{{ route('login') }}" class="btn btn-login">
                     Iniciar sesión en SICET
                </a>
            </div>

            {{-- IMPORTANTE --}}
            <div class="info-box">
                <strong> ¿Qué debes hacer?</strong>
                Inicia sesión en el sistema SICET para aceptar o rechazar esta asignación.
                La solicitud estará pendiente hasta que ingreses al sistema y la respondas.
            </div>

            <div class="info-box" style="background-color: #fffbeb; border-left-color: #f59e0b; color: #92400e;">
                <strong> Información importante</strong>
                • Al aceptar, quedas registrado como responsable del equipo.<br>
                • Si rechazas, la asignación será cancelada.<br>
                • Si no reconoces esta solicitud, ignora este mensaje.
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="email-footer">
            <p> Este es un mensaje automático generado por <strong>SICET</strong></p>
            <p>Por favor, no responder a este correo.</p>
            <div class="company-info">
                <p>Fruitex de México, S.A.P.I. de C.V.</p>
                <p>Calle del Jardín 143, Parque Industrial Lerma, Lerma, Estado de México</p>
                <p>Tel: 728 285 8029</p>
                <p>&copy; {{ date('Y') }} Fruitex de México - Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>