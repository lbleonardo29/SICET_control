@extends('layouts.sicet')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general del sistema')

@section('content')

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
                    <form method="POST" action="{{ route('asignaciones.aceptar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Aceptar</button>
                    </form>
                    <form method="POST" action="{{ route('asignaciones.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
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
                    </div>
                    <div style="font-size:12px;color:rgb(130,136,124);margin-top:2px">
                        Fecha asignación: {{ \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <form method="POST" action="{{ route('asignaciones.moviles.aceptar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:rgb(21,64,31);color:#BFE06A;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Aceptar</button>
                    </form>
                    <form method="POST" action="{{ route('asignaciones.moviles.rechazar', $asig->id) }}">
                        @csrf @method('PUT')
                        <button type="submit" style="padding:7px 16px;background:transparent;color:rgb(194,65,12);border:1.5px solid rgba(234,88,12,0.4);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Rechazar</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif {{-- end admin check --}}

@endsection
