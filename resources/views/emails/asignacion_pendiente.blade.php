<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva asignación pendiente - SICET</title>
    @php
        // El dominio de SICET solo es alcanzable dentro de la red interna, y
        // Gmail no muestra imágenes incrustadas en base64 — por eso el banner
        // se hospeda en un repo público aparte (github.com/lbleonardo29/sicet-assets)
        // y se referencia por su URL pública, alcanzable desde cualquier cliente.
        $bannerUrl = 'https://raw.githubusercontent.com/lbleonardo29/sicet-assets/main/sicet_banner.jpg';
    @endphp
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
        .info-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin: 16px 0;
            border: 1px solid #e5e7eb;
        }
        .info-card h3 {
            color: #155029;
            font-size: 14px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .info-table tr:last-child {
            border-bottom: none;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
            font-size: 13px;
        }
        .info-label {
            width: 40%;
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            width: 60%;
            color: #1f2937;
            font-weight: 500;
        }
        .badge-pending {
            display: inline-block;
            background-color: #fffbeb;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
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
        .info-box {
            background-color: #eef6ee;
            border-left: 4px solid #155029;
            padding: 14px 16px;
            margin: 18px 0;
            border-radius: 8px;
            font-size: 13px;
            color: #14532d;
        }
        .info-box strong {
            display: block;
            margin-bottom: 6px;
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
        .warning-box strong {
            display: block;
            margin-bottom: 6px;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 4px 0;
        }
        @media (max-width: 480px) {
            .email-body {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ $bannerUrl }}" alt="SICET · Sistema de Control de Equipos">
        </div>

        <div class="email-body">
            <div class="greeting">
                Hola, <strong>{{ $asignacion->empleado->nombre_completo ?? $asignacion->empleado->nombre ?? 'Empleado' }}</strong>
            </div>

            <div class="message">
                Se ha generado una solicitud de asignación de <strong>{{ $tipo == 'equipo' ? 'computadora' : 'dispositivo móvil' }}</strong> a tu nombre. Revisa los detalles a continuación:
            </div>

            <div class="info-card">
                <h3>Detalles del dispositivo</h3>
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
                        <td class="info-value"><span class="badge-pending">Pendiente de confirmación</span></td>
                    </tr>
                </table>
            </div>

            <div class="btn-container">
                <a href="{{ route('login') }}" class="btn-link" style="display:inline-block;background-color:#155029;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-weight:600;">
                    Iniciar sesión en SICET →
                </a>
            </div>

            <div class="info-box">
                <strong>¿Qué debes hacer?</strong>
                Inicia sesión en el sistema SICET para aceptar o rechazar esta asignación. La solicitud estará pendiente hasta que ingreses al sistema y la respondas.
            </div>

            <div class="warning-box">
                <strong>Información importante</strong>
                Al aceptar, quedas registrado como responsable del equipo. Si rechazas, la asignación será cancelada.
            </div>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} SICET · Sistema de Control de Equipos</p>
        </div>
    </div>
</body>
</html>
