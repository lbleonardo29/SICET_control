<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva Móvil SICET</title>

    @php
        $logoIzqPath = public_path('img/logo_izq.png');
        $logoDerPath = public_path('img/logo_der.png');

        $logoIzq = file_exists($logoIzqPath)
            ? base64_encode(file_get_contents($logoIzqPath))
            : null;

        $logoDer = file_exists($logoDerPath)
            ? base64_encode(file_get_contents($logoDerPath))
            : null;

        $fechaCarta = (!empty($asignacion) && !empty($asignacion->fecha_firma))
            ? \Carbon\Carbon::parse($asignacion->fecha_firma)->format('d/m/Y')
            : now()->format('d/m/Y');
    @endphp

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000000;
            margin: 1.2cm 1.8cm;
        }

        .encabezado { width: 100%; margin-bottom: 10px; text-align: center; }
        .logo-izq { float: left; width: 65px; }
        .logo-der { float: right; width: 65px; }
        .clearfix { clear: both; }

        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 15px 0 10px 0;
        }

        .fecha { text-align: right; margin-bottom: 15px; }

        .texto { text-align: justify; margin-bottom: 10px; }

        .tabla-equipo {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .tabla-equipo td {
            border: 1px solid #000000;
            padding: 6px;
            vertical-align: top;
        }
        .tabla-equipo .etiqueta {
            width: 25%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .tabla-firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tabla-firmas td {
            width: 50%;
            vertical-align: top;
            padding: 6px 10px;
        }

        .firma-linea {
            border-top: 1px solid #000000;
            margin-top: 30px;
            padding-top: 5px;
            width: 80%;
        }

        .nombre-firma { margin-top: 8px; font-weight: normal; }

        .nota {
            margin-top: 20px;
            font-size: 8.5pt;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="contenido">

    {{-- ENCABEZADO CON LOGOS --}}
    <div class="encabezado">
        @if($logoIzq)
            <img class="logo-izq" src="data:image/png;base64,{{ $logoIzq }}" alt="Logo izquierdo">
        @endif
        @if($logoDer)
            <img class="logo-der" src="data:image/png;base64,{{ $logoDer }}" alt="Logo derecho">
        @endif
        <div class="clearfix"></div>
    </div>

    {{-- TÍTULO --}}
    <div class="titulo">
        CARTA RESPONSIVA EQUIPO DE COMUNICACIÓN MÓVIL
    </div>

    {{-- FECHA (fecha de firma si ya firmó; si no, fecha actual) --}}
    <div class="fecha">
        Fecha: {{ $fechaCarta }}
    </div>

    {{-- TEXTO DE RECEPCIÓN --}}
    <div class="texto">
        Recibí de <strong>Fruitex de México, S. A. P. I. de C. V.</strong> el equipo que a continuación se describe:
    </div>

    {{-- TABLA DE DATOS DEL DISPOSITIVO --}}
    <table class="tabla-equipo">
        <tr>
            <td class="etiqueta">Marca</td>
            <td>{{ $movil->marca ?? '_________________________' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Modelo</td>
            <td>{{ $movil->modelo ?? '_________________________' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">IMEI</td>
            <td>{{ $movil->imei ?? '_________________________' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Características</td>
            <td>{{ $movil->caracteristicas ?? '_________________________' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">No. de SIM</td>
            <td>{{ $movil->numero_sim ?? '_________________________' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">No. telefónico</td>
            <td>{{ $movil->numero_telefono ?? '_________________________' }}</td>
        </tr>
    </table>

    {{-- TEXTO LEGAL --}}
    <div class="texto">
        El cual me comprometo a cuidar, mantener en buen estado y utilizar única y exclusivamente para asuntos relacionados con mi actividad laboral.
    </div>

    <div class="texto">
        Adicionalmente, se le comunica que al firmar la presente responsiva acepta y reconoce que en caso de extravío, daño o uso inadecuado del equipo descrito o sus accesorios, se responsabiliza a pagar el costo de reparación o la reposición del mismo, a entera satisfacción de Fruitex de México, S. A. P. I. de C. V.
    </div>

    <div class="texto">
        En adición se hace de su conocimiento que tiene estrictamente prohibido modificar la configuración del equipo o instalar software sin previa autorización de la Gerencia de Tecnologías de la Información.
    </div>

    {{-- TABLA DE FIRMAS --}}
    <table class="tabla-firmas">
        <tr>
            <td>
                @if(!empty($asignacion) && !empty($asignacion->firma))
                    <img src="{{ $asignacion->firma }}" alt="Firma de aceptación"
                         style="max-width:200px; max-height:70px; display:block; margin-bottom:-8px;">
                @endif
                <div class="firma-linea"></div>
                <div class="nombre-firma">
                    <strong>FIRMA DE ACEPTACIÓN:</strong>
                    @if(!empty($asignacion) && !empty($asignacion->fecha_firma))
                        <br><span style="font-size:9pt;">Firmado electrónicamente el
                        {{ \Carbon\Carbon::parse($asignacion->fecha_firma)->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </td>
            <td>
                <div class="firma-linea"></div>
                <div class="nombre-firma">
                    <strong>FIRMA DE QUIEN ENTREGA:</strong>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <strong>NOMBRE:</strong><br>
                {{ $empleado->nombre_completo ?? $empleado->nombre ?? '_________________________' }}
            </td>
            <td>
                <strong>NOMBRE:</strong><br>
                Edgar Alcántara
            </td>
        </tr>
        <tr>
            <td>
                <strong>NUMERO DE EMPLEADO:</strong><br>
                {{ $empleado->numero_empleado ?? $empleado->id ?? '_________________________' }}
            </td>
            <td>
                &nbsp;
            </td>
        </tr>
    </table>

    {{-- NOTA --}}
    <div class="nota">
        Este documento es de carácter oficial y forma parte del control de activos de la empresa.
    </div>

</div>

</body>
</html>
