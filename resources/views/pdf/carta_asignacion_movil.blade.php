<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

@php
    $logoIzqPath = public_path('img/logo_izq.png');
    $logoDerPath = public_path('img/logo_der.png');

    $logoIzq = file_exists($logoIzqPath) 
        ? base64_encode(file_get_contents($logoIzqPath)) 
        : null;

    $logoDer = file_exists($logoDerPath) 
        ? base64_encode(file_get_contents($logoDerPath)) 
        : null;
@endphp

<style>
    @page {
        size: letter;
        margin: 1.2cm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #000000;
        line-height: 1.3;
    }

    /* ===== ENCABEZADO ===== */
    .encabezado {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .encabezado td {
        border: 1px solid #000000;
        padding: 5px;
        vertical-align: middle;
    }

    .logo {
        width: 70px;
        text-align: center;
    }

    .titulo-formato {
        text-align: center;
        font-weight: bold;
        font-size: 12pt;
    }

    .codigo {
        font-size: 9pt;
        text-align: center;
    }

    .info-doc {
        font-size: 11pt;
        text-align: center;
    }

    /* ===== TÍTULO PRINCIPAL ===== */
    .titulo-principal {
        text-align: center;
        font-weight: bold;
        font-size: 13pt;
        margin: 15px 0 8px 0;
    }

    /* ===== FECHA ===== */
    .fecha {
        text-align: right;
        margin-bottom: 10px;
        font-size: 11pt;
    }

    /* ===== TEXTO ===== */
    .texto {
        text-align: justify;
        margin-bottom: 6px;
        font-size: 10pt;
        line-height: 1.2;
    }

    /* ===== TABLA DE DATOS ===== */
    .tabla-datos {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 10px 0;
    }

    .tabla-datos td {
        border: 1px solid #000000;
        padding: 5px;
        vertical-align: top;
        font-size: 10pt;
    }

    .tabla-datos .etiqueta {
        width: 30%;
        font-weight: bold;
        background-color: #f5f5f5;
    }

    /* ===== TABLA DE FIRMAS ===== */
    .tabla-firmas {
        width: 100%;
        margin-top: 12px;
        border-collapse: collapse;
    }

    .tabla-firmas td {
        width: 45%;
        vertical-align: top;
        padding: 5px;
    }

    .firma-linea {
        border-top: 1px solid #000000;
        margin-top: 15px;
        padding-top: 3px;
        width: 90%;
    }

    .nombre-firma {
        margin-top: 5px;
        font-weight: normal;
        font-size: 10pt;
    }

    .firma-label {
        font-weight: bold;
        margin-bottom: 3px;
        font-size: 10pt;
    }

    /* ===== NOTA ===== */
    .nota {
        margin-top: 12px;
        font-size: 9pt;
        font-style: italic;
        text-align: center;
    }

    .text-center {
        text-align: center;
    }

    /* ===== FORZAR UNA SOLA HOJA ===== */
    html, body {
        height: auto;
    }
    
    .contenido {
        max-height: 100%;
    }
</style>
</head>

<body>

<div class="contenido">

{{-- ENCABEZADO CON LOGOS --}}
<table class="encabezado">
    <tr>
        <td class="logo" rowspan="3">
            @if($logoIzq)
                <img src="data:image/png;base64,{{ $logoIzq }}" width="60">
            @endif
        </td>
        <td class="titulo-formato">
            FORMATO DE TECNOLOGÍAS DE LA INFORMACIÓN
        </td>
        <td class="logo" rowspan="3">
            @if($logoDer)
                <img src="data:image/png;base64,{{ $logoDer }}" width="60">
            @endif
        </td>
    </tr>
    <tr>
        <td class="codigo">
            CÓDIGO: <strong>PRO-SIS-01-FOR03</strong>
        </td>
    </tr>
    <tr>
        <td class="info-doc">
            <strong>CARTA RESPONSIVA DISPOSITIVO MÓVIL</strong><br>
            REVISIÓN: 01
        </td>
    </tr>
</table>

{{-- TÍTULO PRINCIPAL --}}
<div class="titulo-principal">
    CARTA RESPONSIVA DISPOSITIVO MÓVIL
</div>

{{-- FECHA --}}
<div class="fecha">
    Fecha: {{ now()->format('d/m/Y') }}
</div>

{{-- TEXTO DE RECEPCIÓN --}}
<div class="texto">
    Recibí de <strong>Fruitex de México, S. A. P. I. de C. V.</strong> el dispositivo móvil que a continuación se describe:
</div>

{{-- TABLA DE DATOS DEL DISPOSITIVO --}}
<table class="tabla-datos">
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
    @if($movil->numero_sim)
    <tr>
        <td class="etiqueta">Número SIM</td>
        <td>{{ $movil->numero_sim }}</td>
    </tr>
    @endif
    <tr>
        <td class="etiqueta">Características</td>
        <td>{{ $movil->caracteristicas ?? '_________________________' }}</td>
    </tr>
</table>

{{-- TEXTO LEGAL --}}
<div class="texto">
    El cual me comprometo a cuidar, mantener en buen estado y utilizar única y exclusivamente para asuntos relacionados con mi actividad laboral.
</div>

<div class="texto">
    Adicionalmente, se le comunica que al firmar la presente responsiva acepta y reconoce que en caso de extravío, daño o uso inadecuado del dispositivo descrito o sus accesorios, se responsabiliza a pagar el costo de reparación o la reposición del mismo.
</div>

<div class="texto">
    En adición se hace de su conocimiento que tiene estrictamente prohibido modificar la configuración del dispositivo o instalar software sin previa autorización de la Gerencia de Tecnologías de la Información.
</div>

{{-- TABLA DE FIRMAS --}}
<table class="tabla-firmas">
    <tr>
        <td class="text-center">
            <div class="firma-linea"></div>
            <div class="nombre-firma">
                <div class="firma-label">FIRMA DE ACEPTACIÓN</div>
                {{ $empleado->nombre_completo ?? '_________________________' }}<br>
                <small>{{ now()->format('d/m/Y') }}</small>
            </div>
        </td>
        <td style="width:10%"></td>
        <td class="text-center">
            <div class="firma-linea"></div>
            <div class="nombre-firma">
                <div class="firma-label">FIRMA DE QUIEN ENTREGA</div>
                {{ auth()->user()->name ?? '_________________________' }}<br>
                <small>{{ now()->format('d/m/Y') }}</small>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-center">
            <strong>NOMBRE:</strong><br>
            {{ $empleado->nombre_completo ?? '_________________________' }}
        </td>
        <td></td>
        <td class="text-center">
            <strong>NOMBRE:</strong><br>
            {{ auth()->user()->name ?? '_________________________' }}
        </td>
    </tr>
    <tr>
        <td class="text-center">
            <strong>NÚMERO DE EMPLEADO:</strong><br>
            {{ $empleado->numero_empleado ?? $empleado->id_emp ?? '_________________________' }}
        </td>
        <td></td>
        <td class="text-center">
            &nbsp;
        </td>
    </tr>
</table>

{{-- NOTA --}}
<div class="nota">
    * Este documento es de carácter oficial y forma parte del control de activos de la empresa.
</div>

</div>

</body>
</html>