@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- TARJETA USUARIO MEJORADA --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-gradient-primary">
                <div class="card-body d-flex flex-wrap align-items-center gap-4">
                    
                    {{-- Foto de perfil --}}
                    <div class="position-relative">
                        <img src="{{ $user->profile_photo
                                ? asset('storage/' . $user->profile_photo)
                                : asset('img/default-user.png') }}"
                             class="rounded-circle border border-3 border-white shadow-sm"
                             width="100"
                             height="100"
                             style="object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 border border-2 border-white"></span>
                    </div>

                    {{-- Información usuario --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h4 class="mb-0 fw-bold">{{ $user->name }}</h4>
                            <span class="badge 
                                {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} 
                                px-3 py-2">
                                <i class="bi bi-shield-{{ $user->role === 'admin' ? 'lock' : 'person' }} me-1"></i>
                                {{ strtoupper($user->role) }}
                            </span>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <div class="text-muted">
                                <i class="bi bi-envelope me-1"></i> {{ $user->email }}
                            </div>
                            <div class="text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> Sesión activa
                            </div>
                        </div>
                    </div>

                    {{-- Fecha actual --}}
                    <div class="text-end d-none d-lg-block">
                        <div class="small text-muted">Hoy</div>
                        <div class="fw-bold">{{ now()->format('d/m/Y') }}</div>
                        <div class="small">{{ now()->format('H:i') }} hrs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ASIGNACIONES PENDIENTES (SOLO PARA USUARIOS NORMALES) ================= --}}
    @if(in_array($user->role, ['user', 'usuario', 'seguridad']))
        @php
            $asignacionesPendientes = \App\Models\Asignacion::where('empleado_id', $user->empleado_id)
                ->where('estado_asignacion', 'pendiente')
                ->with('equipo')
                ->get();
                
            $movilesPendientes = \App\Models\AsignacionMovil::where('empleado_id', $user->empleado_id)
                ->where('estado_asignacion', 'pendiente')
                ->with('dispositivo')
                ->get();
        @endphp

        @if($asignacionesPendientes->count() > 0 || $movilesPendientes->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 border-warning">
                        <div class="card-header bg-warning text-dark py-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history me-2 fs-5"></i>
                                <h5 class="mb-0">Asignaciones Pendientes de Aprobación</h5>
                                <span class="badge bg-dark ms-3">{{ $asignacionesPendientes->count() + $movilesPendientes->count() }} pendientes</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Tienes asignaciones pendientes. Debes aceptarlas o rechazarlas para continuar.
                            </div>

                            {{-- Computadoras pendientes --}}
                            @foreach($asignacionesPendientes as $pendiente)
                                <div class="card mb-3 border-warning">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center gap-3">
                                                    <i class="bi bi-pc-display text-primary fs-1"></i>
                                                    <div>
                                                        <h5 class="mb-1">{{ $pendiente->equipo->marca }} {{ $pendiente->equipo->modelo }}</h5>
                                                        <p class="text-muted mb-0">
                                                            <i class="bi bi-upc-scan me-1"></i>
                                                            Código: {{ $pendiente->equipo->codigo_interno }}
                                                        </p>
                                                        <p class="text-muted mb-0">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            Asignación: {{ \Carbon\Carbon::parse($pendiente->fecha_asignacion)->format('d/m/Y') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                                <div class="d-flex justify-content-end gap-3">
                                                    <form action="{{ route('asignaciones.aceptar', $pendiente->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-lg px-4" onclick="return confirm('¿Aceptar esta computadora?')">
                                                            <i class="bi bi-check-circle me-2"></i>
                                                            Aceptar
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('asignaciones.rechazar', $pendiente->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger btn-lg px-4" onclick="return confirm('¿Rechazar esta computadora?')">
                                                            <i class="bi bi-x-circle me-2"></i>
                                                            Rechazar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Móviles pendientes --}}
                            @foreach($movilesPendientes as $pendiente)
                                <div class="card mb-3 border-warning">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center gap-3">
                                                    <i class="bi bi-phone text-success fs-1"></i>
                                                    <div>
                                                        <h5 class="mb-1">{{ $pendiente->dispositivo->marca }} {{ $pendiente->dispositivo->modelo }}</h5>
                                                        <p class="text-muted mb-0">
                                                            <i class="bi bi-upc-scan me-1"></i>
                                                            Código: {{ $pendiente->dispositivo->codigo_interno }}
                                                        </p>
                                                        <p class="text-muted mb-0">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            Asignación: {{ \Carbon\Carbon::parse($pendiente->fecha_asignacion)->format('d/m/Y') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                                <div class="d-flex justify-content-end gap-3">
                                                    <form action="{{ route('asignaciones.moviles.aceptar', $pendiente->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-lg px-4" onclick="return confirm('¿Aceptar este dispositivo móvil?')">
                                                            <i class="bi bi-check-circle me-2"></i>
                                                            Aceptar
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('asignaciones.moviles.rechazar', $pendiente->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger btn-lg px-4" onclick="return confirm('¿Rechazar este dispositivo móvil?')">
                                                            <i class="bi bi-x-circle me-2"></i>
                                                            Rechazar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ================= ADMIN ESTADISTICAS MEJORADAS ================= --}}
    @if($user->role === 'admin')
    
    {{-- Estadísticas Computadoras --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-pc-display" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Total Computadoras</h6>
                    <h3 class="fw-bold mb-0">{{ $totalEquipos }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Computadoras Disponibles</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $equiposDisponibles }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-person-check" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Computadoras Asignadas</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ $equiposAsignados }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Total Empleados</h6>
                    <h3 class="fw-bold text-info mb-0">{{ $totalEmpleados }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas Móviles --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-phone" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Total Móviles</h6>
                    <h3 class="fw-bold mb-0">{{ $totalMoviles }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Móviles Disponibles</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $movilesDisponibles }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-person-check" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Móviles Asignados</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ $movilesAsignados }}</h3>
                </div>
            </div>
        </div>
    </div>

    @endif

    {{-- ================= EQUIPOS ASIGNADOS (ADMIN Y USER) MEJORADOS ================= --}}
    <div class="row g-4 mb-4">

        {{-- COMPUTADORAS --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-pc-display text-primary fs-5"></i>
                    Mis Computadoras
                </div>
                <div class="card-body">
                    @if($maquinas && $maquinas->count() > 0)
                        @foreach($maquinas as $index => $maquina)
                            <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Computadora {{ $loop->iteration }}:</span>
                                    <span class="fw-bold">{{ $maquina->equipo->marca ?? 'N/A' }} {{ $maquina->equipo->modelo ?? '' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Código:</span>
                                    <span class="fw-bold">{{ $maquina->equipo->codigo_interno ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Serie:</span>
                                    <span class="fw-bold">{{ $maquina->equipo->numero_serie ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Asignación:</span>
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($maquina->fecha_asignacion)->format('d/m/Y H:i') }}</span>
                                </div>
                                @if(!$maquina->fecha_devolucion)
                                    <span class="badge bg-success mt-2">Activo</span>
                                @else
                                    <span class="badge bg-secondary mt-2">Devuelto</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-pc-display text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No tienes computadoras asignadas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MOVIL --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-phone text-success fs-5"></i>
                    Mi Dispositivo Móvil
                </div>
                <div class="card-body">
                    @if($movil)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Código:</span>
                                <span class="fw-bold">{{ $movil->dispositivo->codigo_interno ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Marca/Modelo:</span>
                                <span class="fw-bold">{{ $movil->dispositivo->marca ?? 'N/A' }} {{ $movil->dispositivo->modelo ?? '' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">IMEI:</span>
                                <span class="fw-bold">{{ $movil->dispositivo->imei ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Asignación:</span>
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($movil->fecha_asignacion)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        @if(!$movil->fecha_devolucion)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Devuelto</span>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-phone text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No tienes dispositivo móvil asignado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('styles')
{{-- Bootstrap Icons --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.bg-gradient-primary .text-muted {
    color: rgba(255,255,255,0.8) !important;
}
</style>
@endpush