@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-pc-display me-2 text-info"></i>
            Detalles de la Computadora
        </h2>
        <div>
            <a href="{{ route('equipos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al listado
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i>
                    Editar
                </a>
            @endif
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Información General --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Información General
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%"><i class="bi bi-tag me-1 text-primary"></i> Nombre del Equipo:</th>
                            <td><strong class="text-primary">{{ $equipo->nombre_equipo ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-upc-scan me-1 text-primary"></i> Código Interno:</th>
                            <td><span class="badge bg-dark">{{ $equipo->codigo_interno }}</span></td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-tag me-1 text-primary"></i> Marca:</th>
                            <td>{{ $equipo->marca }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-diagram-3 me-1 text-primary"></i> Modelo:</th>
                            <td>{{ $equipo->modelo }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-upc-scan me-1 text-primary"></i> Número de Serie:</th>
                            <td>{{ $equipo->numero_serie ?? 'N/A' }}</td>
                        </tr>
                        {{-- DIRECCIÓN MAC (NUEVO CAMPO) --}}
                        <tr>
                            <th><i class="bi bi-wifi me-1 text-primary"></i> Dirección MAC:</th>
                            <td><code>{{ $equipo->direccion_mac ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-palette me-1 text-primary"></i> Color:</th>
                            <td>{{ $equipo->color ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-building me-1 text-primary"></i> Planta/Ubicación:</th>
                            <td>{{ $equipo->planta->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-flag me-1 text-primary"></i> Estado:</th>
                            <td>
                                @if($equipo->estado == 'Disponible')
                                    <span class="badge bg-success">Disponible</span>
                                @elseif($equipo->estado == 'Asignado')
                                    <span class="badge bg-primary">Asignado</span>
                                @elseif($equipo->estado == 'En reparación')
                                    <span class="badge bg-warning text-dark">En reparación</span>
                                @elseif($equipo->estado == 'Baja')
                                    <span class="badge bg-secondary">Baja</span>
                                @else
                                    <span class="badge bg-secondary">{{ $equipo->estado }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-calendar me-1 text-primary"></i> Fecha Adquisición:</th>
                            <td>
                                @if($equipo->fecha_adquisicion)
                                    {{ \Carbon\Carbon::parse($equipo->fecha_adquisicion)->format('d/m/Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-clock-history me-1 text-primary"></i> Registrado:</th>
                            <td>{{ $equipo->created_at ? \Carbon\Carbon::parse($equipo->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @if($equipo->fecha_baja)
                        <tr>
                            <th><i class="bi bi-archive me-1 text-danger"></i> Fecha de Baja:</th>
                            <td>{{ \Carbon\Carbon::parse($equipo->fecha_baja)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-chat-text me-1 text-danger"></i> Motivo de Baja:</th>
                            <td>{{ $equipo->motivo_baja ?? 'N/A' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Especificaciones Técnicas --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-cpu me-2"></i>
                        Especificaciones Técnicas
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%"><i class="bi bi-cpu me-1 text-secondary"></i> Procesador:</th>
                            <td>{{ $equipo->procesador ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-memory me-1 text-secondary"></i> RAM:</th>
                            <td>{{ $equipo->ram ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-device-ssd me-1 text-secondary"></i> Tipo Almacenamiento:</th>
                            <td>
                                @if($equipo->tipo_almacenamiento)
                                    @if($equipo->tipo_almacenamiento == 'SSD')
                                        <span class="badge bg-info text-dark">SSD (Estado Sólido)</span>
                                    @elseif($equipo->tipo_almacenamiento == 'HDD')
                                        <span class="badge bg-secondary">HDD (Disco Mecánico)</span>
                                    @elseif($equipo->tipo_almacenamiento == 'NVMe')
                                        <span class="badge bg-success">NVMe (M.2 SSD)</span>
                                    @else
                                        <span class="badge bg-dark">{{ $equipo->tipo_almacenamiento }}</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-hdd-stack me-1 text-secondary"></i> Capacidad:</th>
                            <td>{{ $equipo->capacidad_almacenamiento ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-device-ssd me-1 text-secondary"></i> Almacenamiento (Completo):</th>
                            <td>{{ $equipo->ssd ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th><i class="bi bi-plug me-1 text-secondary"></i> Cargador:</th>
                            <td>
                                @if($equipo->cargador)
                                    <span class="badge bg-success">Sí</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Asignación Actual --}}
        @if($asignacionActual)
        <div class="col-12 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check-fill me-2"></i>
                        Asignación Actual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Empleado:</strong>
                            <p>{{ $asignacionActual->empleado->nombre_completo ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha de Asignación:</strong>
                            <p>{{ \Carbon\Carbon::parse($asignacionActual->fecha_asignacion)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Estado de Asignación:</strong>
                            <p>
                                @if($asignacionActual->estado_asignacion == 'aceptada')
                                    <span class="badge bg-success">Aceptada</span>
                                @elseif($asignacionActual->estado_asignacion == 'pendiente')
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @else
                                    <span class="badge bg-secondary">{{ $asignacionActual->estado_asignacion }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('asignaciones.historial.empleado', $asignacionActual->empleado_id) }}" 
                               class="btn btn-sm btn-outline-info mt-2">
                                <i class="bi bi-clock-history me-1"></i>
                                Ver historial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($equipo->estado != 'Disponible' && $equipo->estado != 'Baja')
        <div class="col-12 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Sin Asignación Activa
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Esta computadora no tiene una asignación activa en este momento.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Observaciones --}}
        @if($equipo->observaciones)
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-text me-2"></i>
                        Observaciones
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $equipo->observaciones }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .table-borderless tr {
        border-bottom: 1px solid #f0f0f0;
    }
    .table-borderless tr:last-child {
        border-bottom: none;
    }
    .table-borderless th {
        font-weight: 600;
        color: #4b5563;
    }
</style>
@endpush