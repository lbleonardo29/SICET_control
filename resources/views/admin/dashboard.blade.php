@extends('layouts.sicet')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general del sistema')

@section('content')

{{-- ======================== MODAL DE PRIMER INICIO / ALTA ======================== --}}
@if(auth()->user()->necesitaAlta())
@php $sinFirma = empty(auth()->user()->firma); @endphp
<div class="modal fade firma-modal" id="modalAlta" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:480px;margin:1.75rem auto">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background:rgb(21,64,31);color:#BFE06A">
                <h5 class="modal-title">
                    <i class="bi bi-stars me-2"></i>
                    @if($sinFirma) Bienvenido a SICET — Alta en la plataforma
                    @else Establece tu nueva contraseña @endif
                </h5>
            </div>

            <form method="POST" action="{{ route('cambiar.password') }}"
                  class="@if($sinFirma) firma-form @endif"
                  @if($sinFirma) data-canvas="canvasAlta" @endif>
                @csrf
                @if($sinFirma)
                    <input type="hidden" name="firma" class="firma-input">
                @endif

                <div class="modal-body p-4" style="max-height:65vh;overflow-y:auto">

                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        @if($sinFirma)
                            Es tu primer ingreso. Para activar tu cuenta, firma tu alta y establece una contraseña personal. La firma solo se solicita esta vez.
                        @else
                            Por seguridad debes establecer una nueva contraseña personal antes de continuar.
                        @endif
                    </p>

                    @if($sinFirma)
                    {{-- Firma de alta --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-pen me-1 text-success"></i> Firma de alta
                        </label>
                        <p class="text-muted small mb-2">
                            Al firmar, me doy de alta y acepto el uso de la plataforma SICET.
                        </p>
                        <div class="firma-wrap">
                            <canvas class="firma-canvas" id="canvasAlta"></canvas>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick="limpiarFirma('canvasAlta')">
                            <i class="bi bi-eraser me-1"></i> Limpiar
                        </button>
                    </div>
                    @endif

                    {{-- Nueva contraseña --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock me-1 text-success"></i> Nueva contraseña
                        </label>
                        <input type="password" name="password" id="altaPwd"
                               class="form-control form-control-lg"
                               placeholder="Mínimo 8 caracteres" minlength="8" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1 text-success"></i> Confirmar contraseña
                        </label>
                        <input type="password" name="password_confirmation" id="altaPwd2"
                               class="form-control form-control-lg" minlength="8" required>
                    </div>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" id="altaShowPwd"
                               onclick="var t=this.checked?'text':'password';document.getElementById('altaPwd').type=t;document.getElementById('altaPwd2').type=t;">
                        <label class="form-check-label small text-muted" for="altaShowPwd">Mostrar contraseña</label>
                    </div>
                </div>

                <div class="modal-footer" style="flex-direction:column;align-items:stretch;gap:8px;padding:16px 24px 20px">
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-semibold"
                            style="padding:14px;font-size:17px;letter-spacing:.3px">
                        <i class="bi bi-check-circle-fill me-2"></i> Confirmar y continuar
                    </button>
                </div>
            </form>

            <div class="text-center pb-3">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ======================== ADMIN VIEW ======================== --}}
@if($user->role === 'admin')

@php
    $totalAsignaciones = $equiposAsignados + $movilesAsignados;

    // Donut percentages (based on equipos)
    $donutTotal = max($totalEquipos, 1);
    $pctAsig    = round($equiposAsignados / $donutTotal * 100);
    $pctDisp    = round($equiposDisponibles / $donutTotal * 100);
    $pctOtros   = max(0, 100 - $pctAsig - $pctDisp);

    $deg1 = round($pctAsig * 3.6);
    $deg2 = round(($pctAsig + $pctDisp) * 3.6);
    $donutGradient = "conic-gradient(rgb(21,64,31) 0deg {$deg1}deg, rgb(152,192,61) {$deg1}deg {$deg2}deg, rgb(210,214,205) {$deg2}deg 360deg)";

    // Monthly chart (last 6 months)
    $chartMeses = collect(range(5, 0))->map(function ($i) {
        $mes  = now()->subMonths($i);
        $comp = \App\Models\Asignacion::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)->count();
        $mov  = \App\Models\AsignacionMovil::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)->count();
        return ['label' => $mes->locale('es')->translatedFormat('M'), 'total' => $comp + $mov];
    });
    $chartMax = max(1, $chartMeses->max('total'));

    // Recent activity
    $actividadReciente = \App\Models\Asignacion::with(['empleado', 'equipo'])
        ->latest()->take(6)->get();
@endphp

{{-- KPI CARDS --}}
<div class="s-kpi-grid s-mb-24">
    <div class="s-kpi-card">
        <div class="s-kpi-label">Total de equipos</div>
        <div class="s-kpi-value">{{ $totalEquipos }}</div>
        <span class="s-kpi-tag s-kpi-tag-green">{{ $equiposDisponibles }} disponibles</span>
    </div>
    <div class="s-kpi-card">
        <div class="s-kpi-label">Dispositivos móviles</div>
        <div class="s-kpi-value">{{ $totalMoviles }}</div>
        <span class="s-kpi-tag s-kpi-tag-blue">{{ $movilesDisponibles }} disponibles</span>
    </div>
    <div class="s-kpi-card">
        <div class="s-kpi-label">Empleados</div>
        <div class="s-kpi-value">{{ $totalEmpleados }}</div>
        <span class="s-kpi-tag s-kpi-tag-gray">registrados</span>
    </div>
    <div class="s-kpi-card">
        <div class="s-kpi-label">Asignaciones activas</div>
        <div class="s-kpi-value">{{ $totalAsignaciones }}</div>
        <span class="s-kpi-tag s-kpi-tag-orange">{{ $equiposAsignados }} comp · {{ $movilesAsignados }} móv</span>
    </div>
</div>

{{-- CHARTS ROW --}}
<div class="s-charts-row">

    {{-- Bar chart --}}
    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Asignaciones por mes</span>
                <span class="s-card-subtitle">Últimos 6 meses (equipos + dispositivos)</span>
            </div>
        </div>
        <div class="s-card-body">
            <div class="s-bar-chart">
                @foreach($chartMeses as $mes)
                @php $barH = max(4, round(($mes['total'] / $chartMax) * 110)); @endphp
                <div class="s-bar-col">
                    <span class="s-bar-val">{{ $mes['total'] }}</span>
                    <div class="s-bar" style="height:{{ $barH }}px" title="{{ $mes['total'] }} asignaciones en {{ $mes['label'] }}"></div>
                    <span class="s-bar-label">{{ $mes['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Donut chart --}}
    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Estado de equipos</span>
                <span class="s-card-subtitle">Distribución actual (computadoras)</span>
            </div>
        </div>
        <div class="s-card-body">
            <div class="s-donut-wrap">
                <div class="s-donut" style="background:{{ $donutGradient }}">
                    <div style="width:100%;height:100%;border-radius:50%;-webkit-mask:radial-gradient(circle,transparent 44%,black 45%);mask:radial-gradient(circle,transparent 44%,black 45%);background:#fff"></div>
                </div>
                <div class="s-legend">
                    <div class="s-legend-item">
                        <span class="s-legend-dot" style="background:rgb(21,64,31)"></span>
                        Asignados <span class="s-legend-pct">{{ $pctAsig }}%</span>
                    </div>
                    <div class="s-legend-item">
                        <span class="s-legend-dot" style="background:rgb(152,192,61)"></span>
                        Disponibles <span class="s-legend-pct">{{ $pctDisp }}%</span>
                    </div>
                    <div class="s-legend-item">
                        <span class="s-legend-dot" style="background:rgb(210,214,205)"></span>
                        Otros <span class="s-legend-pct">{{ $pctOtros }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- RECENT ACTIVITY --}}
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-header-left">
            <span class="s-card-title">Actividad reciente</span>
            <span class="s-card-subtitle">Últimas asignaciones de equipos</span>
        </div>
        <a href="{{ route('asignaciones.dashboard') }}" class="s-link-action">Ver todas →</a>
    </div>

    @if($actividadReciente->isEmpty())
        <div class="s-empty">
            <div class="s-empty-icon"></div>
            <div class="s-empty-title">Sin actividad reciente</div>
            <div class="s-empty-text">Las asignaciones realizadas aparecerán aquí.</div>
        </div>
    @else
        <table class="s-table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Equipo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($actividadReciente as $asig)
                <tr>
                    <td>
                        <div class="s-row-flex">
                            <div class="s-initials">
                                {{ $asig->empleado ? mb_strtoupper(mb_substr($asig->empleado->nombre_completo, 0, 2)) : '?' }}
                            </div>
                            {{ $asig->empleado->nombre_completo ?? '—' }}
                        </div>
                    </td>
                    <td>
                        {{ $asig->equipo ? $asig->equipo->marca . ' ' . $asig->equipo->modelo : '—' }}
                        @if($asig->equipo)
                            <br><small style="color:rgb(130,136,124);font-size:11px">{{ $asig->equipo->codigo_interno }}</small>
                        @endif
                    </td>
                    <td style="white-space:nowrap">
                        {{ $asig->fecha_asignacion ? \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @switch($asig->estado_asignacion)
                            @case('aceptada')  <span class="s-badge s-badge-green">Asignado</span>  @break
                            @case('pendiente') <span class="s-badge s-badge-yellow">Pendiente</span> @break
                            @case('rechazada') <span class="s-badge s-badge-red">Rechazado</span>   @break
                            @default           <span class="s-badge s-badge-gray">{{ $asig->estado_asignacion }}</span>
                        @endswitch
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ======================== PENDIENTES DEL ADMIN (si tiene equipo pendiente de firma) ======================== --}}
@if($asignacionesPendientes->isNotEmpty() || $movilesPendientes->isNotEmpty())
<div class="s-alert s-alert-warning" style="margin-bottom:24px;margin-top:24px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    Tienes {{ $asignacionesPendientes->count() + $movilesPendientes->count() }} asignación(es) pendiente(s) de confirmar.
</div>
@endif

@if($asignacionesPendientes->isNotEmpty())
<div class="s-card s-mb-24">
    <div class="s-card-header">
        <div class="s-card-header-left">
            <span class="s-card-title">Equipos pendientes de confirmación</span>
        </div>
        <span class="s-badge s-badge-yellow">{{ $asignacionesPendientes->count() }}</span>
    </div>
    <div class="s-card-body" style="display:flex;flex-direction:column;gap:10px">
        @foreach($asignacionesPendientes as $asig)
        <div class="s-pending-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div style="font-weight:600;font-size:14px;color:rgb(27,32,24)">
                        {{ $asig->equipo->marca ?? '' }} {{ $asig->equipo->modelo ?? '' }}
                        <span style="color:rgb(130,136,124);font-weight:400;font-size:12px">— {{ $asig->equipo->codigo_interno ?? '' }}</span>
                    </div>
                    <div style="font-size:12px;color:rgb(130,136,124);margin-top:2px">
                        Fecha asignación: {{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="firma-trigger" data-bs-toggle="modal" data-bs-target="#firmaModalEqAdm{{ $asig->id }}"
                            style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
                        Revisar y firmar
                    </button>
                    <form method="POST" action="{{ route('asignaciones.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade firma-modal" id="firmaModalEqAdm{{ $asig->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Carta responsiva — Confirmación de equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh;overflow-y:auto">
                        <div class="alert alert-warning py-2" style="font-size:12px">
                            <strong>Plantilla provisional.</strong> Revisa los datos y firma en el recuadro para aceptar el equipo.
                        </div>
                        <h6 class="text-center fw-bold mb-3">CARTA RESPONSIVA EQUIPO DE CÓMPUTO</h6>
                        <p style="font-size:13px">Recibí de <strong>Fruitex de México, S.A.P.I. de C.V.</strong> el equipo:</p>
                        <table class="table table-sm table-bordered" style="font-size:13px">
                            <tr><th style="width:35%">Marca</th><td>{{ $asig->equipo->marca }}</td></tr>
                            <tr><th>Modelo</th><td>{{ $asig->equipo->modelo }}</td></tr>
                            <tr><th>No. de serie</th><td>{{ $asig->equipo->numero_serie }}</td></tr>
                            <tr><th>Código interno</th><td>{{ $asig->equipo->codigo_interno }}</td></tr>
                        </table>
                        <p style="font-size:12px;color:#555">
                            Me comprometo a cuidarlo y utilizarlo exclusivamente para fines laborales. En caso de
                            extravío, daño o uso inadecuado, me responsabilizo del costo de reparación o reposición.
                        </p>
                        <div class="alert alert-light border d-flex align-items-center gap-2 mt-2 py-2" style="font-size:12px">
                            <i class="bi bi-patch-check-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Se firmará con la firma que registraste al darte de alta en la plataforma.</span>
                        </div>
                    </div>
                    <div class="modal-footer flex-column align-items-stretch gap-2">
                        <button type="button" class="btn-hold-confirm w-100" data-form-id="firmaFormEqAdm{{ $asig->id }}">
                            <span class="btn-hold-progress"></span>
                            <span class="btn-hold-label"><i class="bi bi-hand-index-thumb me-2"></i>Mantén presionado para aceptar</span>
                        </button>
                        <form method="POST" action="{{ route('asignaciones.firmar', $asig->id) }}" id="firmaFormEqAdm{{ $asig->id }}" class="d-none">
                            @csrf @method('PUT')
                        </form>
                        <form method="POST" action="{{ route('asignaciones.rechazar', $asig->id) }}" class="m-0">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Rechazar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($movilesPendientes->isNotEmpty())
<div class="s-card s-mb-24">
    <div class="s-card-header">
        <div class="s-card-header-left">
            <span class="s-card-title">Dispositivos pendientes de confirmación</span>
        </div>
        <span class="s-badge s-badge-yellow">{{ $movilesPendientes->count() }}</span>
    </div>
    <div class="s-card-body" style="display:flex;flex-direction:column;gap:10px">
        @foreach($movilesPendientes as $asig)
        <div class="s-pending-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div style="font-weight:600;font-size:14px;color:rgb(27,32,24)">
                        {{ $asig->dispositivo->marca ?? '' }} {{ $asig->dispositivo->modelo ?? '' }}
                        <span style="color:rgb(130,136,124);font-weight:400;font-size:12px">— {{ $asig->dispositivo->codigo_interno ?? '' }}</span>
                    </div>
                    <div style="font-size:12px;color:rgb(130,136,124);margin-top:2px">
                        Fecha asignación: {{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="firma-trigger" data-bs-toggle="modal" data-bs-target="#firmaModalMovAdm{{ $asig->id }}"
                            style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
                        Revisar y firmar
                    </button>
                    <form method="POST" action="{{ route('asignaciones.moviles.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade firma-modal" id="firmaModalMovAdm{{ $asig->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Carta responsiva — Confirmación de dispositivo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh;overflow-y:auto">
                        <div class="alert alert-warning py-2" style="font-size:12px">
                            <strong>Plantilla provisional.</strong> Revisa los datos y firma en el recuadro para aceptar el dispositivo.
                        </div>
                        <h6 class="text-center fw-bold mb-3">CARTA RESPONSIVA DISPOSITIVO MÓVIL</h6>
                        <p style="font-size:13px">Recibí de <strong>Fruitex de México, S.A.P.I. de C.V.</strong> el dispositivo:</p>
                        <table class="table table-sm table-bordered" style="font-size:13px">
                            <tr><th style="width:35%">Marca</th><td>{{ $asig->dispositivo->marca }}</td></tr>
                            <tr><th>Modelo</th><td>{{ $asig->dispositivo->modelo }}</td></tr>
                            <tr><th>IMEI</th><td>{{ $asig->dispositivo->imei }}</td></tr>
                            <tr><th>Código interno</th><td>{{ $asig->dispositivo->codigo_interno }}</td></tr>
                        </table>
                        <p style="font-size:12px;color:#555">
                            Me comprometo a cuidarlo y utilizarlo exclusivamente para fines laborales. En caso de
                            extravío, daño o uso inadecuado, me responsabilizo del costo de reparación o reposición.
                        </p>
                        <div class="alert alert-light border d-flex align-items-center gap-2 mt-2 py-2" style="font-size:12px">
                            <i class="bi bi-patch-check-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Se firmará con la firma que registraste al darte de alta en la plataforma.</span>
                        </div>
                    </div>
                    <div class="modal-footer flex-column align-items-stretch gap-2">
                        <button type="button" class="btn-hold-confirm w-100" data-form-id="firmaFormMovAdm{{ $asig->id }}">
                            <span class="btn-hold-progress"></span>
                            <span class="btn-hold-label"><i class="bi bi-hand-index-thumb me-2"></i>Mantén presionado para aceptar</span>
                        </button>
                        <form method="POST" action="{{ route('asignaciones.moviles.firmar', $asig->id) }}" id="firmaFormMovAdm{{ $asig->id }}" class="d-none">
                            @csrf @method('PUT')
                        </form>
                        <form method="POST" action="{{ route('asignaciones.moviles.rechazar', $asig->id) }}" class="m-0">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Rechazar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Mi propio equipo y móvil (el admin también puede tener equipo asignado) --}}
<div class="s-grid-2 s-mb-24">

    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Mis equipos</span>
                <span class="s-card-subtitle">Equipos de cómputo activos</span>
            </div>
        </div>
        @if($maquinas->isEmpty())
            <div class="s-empty">
                <div class="s-empty-icon"></div>
                <div class="s-empty-title">Sin equipos asignados</div>
                <div class="s-empty-text">No tienes computadoras asignadas actualmente.</div>
            </div>
        @else
            <div class="s-card-body" style="display:flex;flex-direction:column;gap:12px">
                @foreach($maquinas as $asig)
                <div class="s-device-card">
                    <div class="s-device-card-header">
                        <div class="s-device-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <div>
                            <div class="s-device-model">{{ $asig->equipo->marca }} {{ $asig->equipo->modelo }}</div>
                            <div class="s-device-code">{{ $asig->equipo->codigo_interno }}</div>
                        </div>
                    </div>
                    <div class="s-device-detail">Serie: <span>{{ $asig->equipo->numero_serie }}</span></div>
                    <div class="s-device-detail">Asignado el: <span>{{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}</span></div>
                    @if($asig->estado_asignacion == 'aceptada')
                    <a href="{{ route('asignaciones.descargar', $asig->id) }}" class="s-pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Descargar carta (PDF)
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Mi dispositivo móvil</span>
                <span class="s-card-subtitle">Dispositivo activo asignado</span>
            </div>
        </div>
        @if(!$movil)
            <div class="s-empty">
                <div class="s-empty-icon"></div>
                <div class="s-empty-title">Sin dispositivo asignado</div>
                <div class="s-empty-text">No tienes un dispositivo móvil actualmente.</div>
            </div>
        @else
            <div class="s-card-body">
                <div class="s-device-card">
                    <div class="s-device-card-header">
                        <div class="s-device-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                <line x1="12" y1="18" x2="12.01" y2="18"/>
                            </svg>
                        </div>
                        <div>
                            <div class="s-device-model">{{ $movil->dispositivo->marca }} {{ $movil->dispositivo->modelo }}</div>
                            <div class="s-device-code">{{ $movil->dispositivo->codigo_interno ?? 'S/C' }}</div>
                        </div>
                    </div>
                    <div class="s-device-detail">IMEI: <span>{{ $movil->dispositivo->imei }}</span></div>
                    <div class="s-device-detail">Asignado el: <span>{{ \Carbon\Carbon::parse($movil->fecha_asignacion)->format('d/m/Y') }}</span></div>
                    @if($movil->estado_asignacion == 'aceptada')
                    <a href="{{ route('asignaciones.moviles.descargar', $movil->id) }}" class="s-pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Descargar carta (PDF)
                    </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>

{{-- ======================== USER / SEGURIDAD VIEW ======================== --}}
@else

@if($asignacionesPendientes->isNotEmpty() || $movilesPendientes->isNotEmpty())
<div class="s-alert s-alert-warning" style="margin-bottom:24px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    Tienes {{ $asignacionesPendientes->count() + $movilesPendientes->count() }} asignación(es) pendiente(s) de confirmar.
</div>
@endif

<div class="s-grid-2 s-mb-24">

    {{-- Computadoras asignadas --}}
    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Mis equipos</span>
                <span class="s-card-subtitle">Equipos de cómputo activos</span>
            </div>
        </div>
        @if($maquinas->isEmpty())
            <div class="s-empty">
                <div class="s-empty-icon"></div>
                <div class="s-empty-title">Sin equipos asignados</div>
                <div class="s-empty-text">No tienes computadoras asignadas actualmente.</div>
            </div>
        @else
            <div class="s-card-body" style="display:flex;flex-direction:column;gap:12px">
                @foreach($maquinas as $asig)
                <div class="s-device-card">
                    <div class="s-device-card-header">
                        <div class="s-device-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <div>
                            <div class="s-device-model">{{ $asig->equipo->marca }} {{ $asig->equipo->modelo }}</div>
                            <div class="s-device-code">{{ $asig->equipo->codigo_interno }}</div>
                        </div>
                    </div>
                    <div class="s-device-detail">Serie: <span>{{ $asig->equipo->numero_serie }}</span></div>
                    <div class="s-device-detail">Asignado el: <span>{{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}</span></div>
                    @if($asig->estado_asignacion == 'aceptada')
                    <a href="{{ route('asignaciones.descargar', $asig->id) }}" class="s-pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Descargar carta (PDF)
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Móvil asignado --}}
    <div class="s-card">
        <div class="s-card-header">
            <div class="s-card-header-left">
                <span class="s-card-title">Mi dispositivo móvil</span>
                <span class="s-card-subtitle">Dispositivo activo asignado</span>
            </div>
        </div>
        @if(!$movil)
            <div class="s-empty">
                <div class="s-empty-icon"></div>
                <div class="s-empty-title">Sin dispositivo asignado</div>
                <div class="s-empty-text">No tienes un dispositivo móvil actualmente.</div>
            </div>
        @else
            <div class="s-card-body">
                <div class="s-device-card">
                    <div class="s-device-card-header">
                        <div class="s-device-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                <line x1="12" y1="18" x2="12.01" y2="18"/>
                            </svg>
                        </div>
                        <div>
                            <div class="s-device-model">{{ $movil->dispositivo->marca }} {{ $movil->dispositivo->modelo }}</div>
                            <div class="s-device-code">{{ $movil->dispositivo->codigo_interno ?? 'S/C' }}</div>
                        </div>
                    </div>
                    <div class="s-device-detail">IMEI: <span>{{ $movil->dispositivo->imei }}</span></div>
                    <div class="s-device-detail">Asignado el: <span>{{ \Carbon\Carbon::parse($movil->fecha_asignacion)->format('d/m/Y') }}</span></div>
                    @if($movil->estado_asignacion == 'aceptada')
                    <a href="{{ route('asignaciones.moviles.descargar', $movil->id) }}" class="s-pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Descargar carta (PDF)
                    </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Pending computers --}}
@if($asignacionesPendientes->isNotEmpty())
<div class="s-card s-mb-24">
    <div class="s-card-header">
        <div class="s-card-header-left">
            <span class="s-card-title">Equipos pendientes de confirmación</span>
        </div>
        <span class="s-badge s-badge-yellow">{{ $asignacionesPendientes->count() }}</span>
    </div>
    <div class="s-card-body" style="display:flex;flex-direction:column;gap:10px">
        @foreach($asignacionesPendientes as $asig)
        <div class="s-pending-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div style="font-weight:600;font-size:14px;color:rgb(27,32,24)">
                        {{ $asig->equipo->marca ?? '' }} {{ $asig->equipo->modelo ?? '' }}
                        <span style="color:rgb(130,136,124);font-weight:400;font-size:12px">— {{ $asig->equipo->codigo_interno ?? '' }}</span>
                    </div>
                    <div style="font-size:12px;color:rgb(130,136,124);margin-top:2px">
                        Fecha asignación: {{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="firma-trigger" data-bs-toggle="modal" data-bs-target="#firmaModalEq{{ $asig->id }}"
                            style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
                        Revisar y firmar
                    </button>
                    <form method="POST" action="{{ route('asignaciones.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: carta provisional + firma electrónica --}}
        <div class="modal fade firma-modal" id="firmaModalEq{{ $asig->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Carta responsiva — Confirmación de equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh;overflow-y:auto">
                        <div class="alert alert-warning py-2" style="font-size:12px">
                            <strong>Plantilla provisional.</strong> Revisa los datos y firma en el recuadro para aceptar el equipo.
                        </div>
                        <h6 class="text-center fw-bold mb-3">CARTA RESPONSIVA EQUIPO DE CÓMPUTO</h6>
                        <p style="font-size:13px">Recibí de <strong>Fruitex de México, S.A.P.I. de C.V.</strong> el equipo:</p>
                        <table class="table table-sm table-bordered" style="font-size:13px">
                            <tr><th style="width:35%">Marca</th><td>{{ $asig->equipo->marca }}</td></tr>
                            <tr><th>Modelo</th><td>{{ $asig->equipo->modelo }}</td></tr>
                            <tr><th>No. de serie</th><td>{{ $asig->equipo->numero_serie }}</td></tr>
                            <tr><th>Código interno</th><td>{{ $asig->equipo->codigo_interno }}</td></tr>
                        </table>
                        <p style="font-size:12px;color:#555">
                            Me comprometo a cuidarlo y utilizarlo exclusivamente para fines laborales. En caso de
                            extravío, daño o uso inadecuado, me responsabilizo del costo de reparación o reposición.
                        </p>
                        <div class="alert alert-light border d-flex align-items-center gap-2 mt-2 py-2" style="font-size:12px">
                            <i class="bi bi-patch-check-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Se firmará con la firma que registraste al darte de alta en la plataforma.</span>
                        </div>
                    </div>
                    <div class="modal-footer flex-column align-items-stretch gap-2">
                        <button type="button" class="btn-hold-confirm w-100" data-form-id="firmaFormEq{{ $asig->id }}">
                            <span class="btn-hold-progress"></span>
                            <span class="btn-hold-label"><i class="bi bi-hand-index-thumb me-2"></i>Mantén presionado para aceptar</span>
                        </button>
                        <form method="POST" action="{{ route('asignaciones.firmar', $asig->id) }}" id="firmaFormEq{{ $asig->id }}" class="d-none">
                            @csrf @method('PUT')
                        </form>
                        <form method="POST" action="{{ route('asignaciones.rechazar', $asig->id) }}" class="m-0">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Rechazar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($movilesPendientes->isNotEmpty())
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-header-left">
            <span class="s-card-title">Dispositivos pendientes de confirmación</span>
        </div>
        <span class="s-badge s-badge-yellow">{{ $movilesPendientes->count() }}</span>
    </div>
    <div class="s-card-body" style="display:flex;flex-direction:column;gap:10px">
        @foreach($movilesPendientes as $asig)
        <div class="s-pending-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div style="font-weight:600;font-size:14px;color:rgb(27,32,24)">
                        {{ $asig->dispositivo->marca ?? '' }} {{ $asig->dispositivo->modelo ?? '' }}
                        <span style="color:rgb(130,136,124);font-weight:400;font-size:12px">— {{ $asig->dispositivo->codigo_interno ?? '' }}</span>
                    </div>
                    <div style="font-size:12px;color:rgb(130,136,124);margin-top:2px">
                        Fecha asignación: {{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="firma-trigger" data-bs-toggle="modal" data-bs-target="#firmaModalMov{{ $asig->id }}"
                            style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
                        Revisar y firmar
                    </button>
                    <form method="POST" action="{{ route('asignaciones.moviles.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: carta provisional + firma electrónica (móvil) --}}
        <div class="modal fade firma-modal" id="firmaModalMov{{ $asig->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Carta responsiva — Confirmación de dispositivo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh;overflow-y:auto">
                        <div class="alert alert-warning py-2" style="font-size:12px">
                            <strong>Plantilla provisional.</strong> Revisa los datos y firma en el recuadro para aceptar el dispositivo.
                        </div>
                        <h6 class="text-center fw-bold mb-3">CARTA RESPONSIVA DISPOSITIVO MÓVIL</h6>
                        <p style="font-size:13px">Recibí de <strong>Fruitex de México, S.A.P.I. de C.V.</strong> el dispositivo:</p>
                        <table class="table table-sm table-bordered" style="font-size:13px">
                            <tr><th style="width:35%">Marca</th><td>{{ $asig->dispositivo->marca }}</td></tr>
                            <tr><th>Modelo</th><td>{{ $asig->dispositivo->modelo }}</td></tr>
                            <tr><th>IMEI</th><td>{{ $asig->dispositivo->imei }}</td></tr>
                            <tr><th>Código interno</th><td>{{ $asig->dispositivo->codigo_interno }}</td></tr>
                        </table>
                        <p style="font-size:12px;color:#555">
                            Me comprometo a cuidarlo y utilizarlo exclusivamente para fines laborales. En caso de
                            extravío, daño o uso inadecuado, me responsabilizo del costo de reparación o reposición.
                        </p>
                        <div class="alert alert-light border d-flex align-items-center gap-2 mt-2 py-2" style="font-size:12px">
                            <i class="bi bi-patch-check-fill text-success fs-5 flex-shrink-0"></i>
                            <span>Se firmará con la firma que registraste al darte de alta en la plataforma.</span>
                        </div>
                    </div>
                    <div class="modal-footer flex-column align-items-stretch gap-2">
                        <button type="button" class="btn-hold-confirm w-100" data-form-id="firmaFormMov{{ $asig->id }}">
                            <span class="btn-hold-progress"></span>
                            <span class="btn-hold-label"><i class="bi bi-hand-index-thumb me-2"></i>Mantén presionado para aceptar</span>
                        </button>
                        <form method="POST" action="{{ route('asignaciones.moviles.firmar', $asig->id) }}" id="firmaFormMov{{ $asig->id }}" class="d-none">
                            @csrf @method('PUT')
                        </form>
                        <form method="POST" action="{{ route('asignaciones.moviles.rechazar', $asig->id) }}" class="m-0">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Rechazar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif {{-- end admin check --}}

@endsection

@push('styles')
<style>
    .firma-wrap {
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        background: #fff;
        margin-top: 6px;
    }
    .firma-canvas {
        width: 100%;
        height: 180px;
        display: block;
        touch-action: none;
        cursor: crosshair;
        border-radius: 10px;
    }
    .btn-hold-confirm {
        position: relative;
        overflow: hidden;
        padding: 14px;
        border: none;
        border-radius: 10px;
        background: rgb(21,64,31);
        color: #BFE06A;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        -webkit-user-select: none;
        user-select: none;
        touch-action: none;
    }
    .btn-hold-confirm .btn-hold-progress {
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 0%;
        background: rgba(191,224,106,0.38);
    }
    .btn-hold-confirm .btn-hold-label {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
    }
    .btn-hold-confirm:disabled {
        opacity: 0.85;
        cursor: default;
    }
    .s-pdf-btn {
        display: inline-flex;
        align-items: center;
        margin-top: 10px;
        padding: 7px 14px;
        background: rgb(21,64,31);
        color: #BFE06A;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }
    .s-pdf-btn:hover { background: rgb(27,80,40); color: #d2f08a; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
    if (typeof SignaturePad === 'undefined') return;

    var pads = {};

    function initPad(canvas) {
        var pad = new SignaturePad(canvas, {
            penColor: 'rgb(21,64,31)',
            backgroundColor: 'rgba(255,255,255,0)'
        });
        pads[canvas.id] = pad;
        return pad;
    }

    function resizePad(canvas) {
        var pad = pads[canvas.id] || initPad(canvas);
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
    }

    document.querySelectorAll('.firma-canvas').forEach(initPad);

    // El canvas solo tiene tamaño real cuando el modal es visible
    document.querySelectorAll('.firma-modal').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            var canvas = modal.querySelector('.firma-canvas');
            if (canvas) resizePad(canvas);
        });
    });

    window.limpiarFirma = function (canvasId) {
        if (pads[canvasId]) pads[canvasId].clear();
    };

    document.querySelectorAll('.firma-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var pad = pads[form.getAttribute('data-canvas')];
            if (!pad || pad.isEmpty()) {
                e.preventDefault();
                alert('Por favor dibuja tu firma antes de aceptar.');
                return false;
            }
            form.querySelector('.firma-input').value = pad.toDataURL('image/png');
        });
    });

    // Auto-abrir: si NO hay modal de alta, abrir la primera asignación pendiente.
    if (!document.getElementById('modalAlta')) {
        var firstModal = document.querySelector('.firma-modal');
        if (firstModal && window.bootstrap) {
            new bootstrap.Modal(firstModal).show();
        }
    }
})();

// Botón "mantener presionado" para aceptar una asignación reutilizando la
// firma de alta (ya no se dibuja una firma nueva por cada asignación).
(function () {
    var DURATION = 1000;

    function initHoldToConfirm(btn) {
        var progress  = btn.querySelector('.btn-hold-progress');
        var label     = btn.querySelector('.btn-hold-label');
        var labelHtml = label.innerHTML;
        var form      = document.getElementById(btn.getAttribute('data-form-id'));
        var holdTimeout = null;
        var completed = false;

        function empezar(e) {
            if (completed) return;
            e.preventDefault();
            progress.style.transition = 'width ' + DURATION + 'ms linear';
            progress.style.width = '100%';
            holdTimeout = setTimeout(function () {
                completed = true;
                btn.disabled = true;
                label.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Firmando...';
                setTimeout(function () { form.submit(); }, 300);
            }, DURATION);
        }

        function cancelar() {
            if (completed) return;
            clearTimeout(holdTimeout);
            progress.style.transition = 'width 0.2s ease';
            progress.style.width = '0%';
            label.innerHTML = labelHtml;
        }

        btn.addEventListener('mousedown', empezar);
        btn.addEventListener('touchstart', empezar, { passive: false });
        btn.addEventListener('mouseup', cancelar);
        btn.addEventListener('mouseleave', cancelar);
        btn.addEventListener('touchend', cancelar);
        btn.addEventListener('touchcancel', cancelar);
    }

    document.querySelectorAll('.btn-hold-confirm').forEach(initHoldToConfirm);
})();

// El modal de alta debe abrirse y bloquear SIEMPRE (aun si falla la librería de firma).
(function () {
    var alta = document.getElementById('modalAlta');
    if (alta && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(alta, { backdrop: 'static', keyboard: false }).show();
    }
})();
</script>
@endpush
