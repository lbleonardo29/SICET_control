@extends('layouts.sicet')

@section('page-title', 'Detalle de Dispositivo')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-phone me-2 text-primary"></i>
            {{ $movil->marca }} {{ $movil->modelo }}
        </h2>
        <div class="d-flex gap-2">
            @role('admin')
            <a href="{{ route('moviles.edit', $movil->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <a href="{{ route('moviles.historial', $movil->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i> Historial
            </a>
            <a href="{{ route('moviles.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
            @endrole
        </div>
    </div>

    <div class="row g-4">

        {{-- Datos del dispositivo --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-phone-fill me-2"></i>
                        Información del Dispositivo
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width:40%">Código interno</th>
                            <td class="fw-bold">{{ $movil->codigo_interno }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Marca</th>
                            <td>{{ $movil->marca }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Modelo</th>
                            <td>{{ $movil->modelo ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">IMEI</th>
                            <td><code>{{ $movil->imei }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">No. SIM</th>
                            <td>{{ $movil->numero_sim ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">No. Teléfono</th>
                            <td>{{ $movil->numero_telefono ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Dirección MAC</th>
                            <td><code>{{ $movil->direccion_mac ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Estado</th>
                            <td>
                                @php
                                    $estadoColor = match($movil->estado) {
                                        'Disponible' => 'success',
                                        'Asignado'   => 'primary',
                                        'Pendiente'  => 'warning',
                                        'Baja'       => 'danger',
                                        default      => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $estadoColor }} px-3 py-2">
                                    {{ $movil->estado }}
                                </span>
                            </td>
                        </tr>
                        @if($movil->observaciones)
                        <tr>
                            <th class="text-muted">Observaciones</th>
                            <td class="text-muted small">{{ $movil->observaciones }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Asignación actual --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3
                    {{ $asignacionActual ? 'bg-success text-white' : 'bg-light' }}">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge{{ $asignacionActual ? '-fill' : '' }} me-2"></i>
                        Asignación actual
                    </h5>
                </div>
                <div class="card-body">
                    @if($asignacionActual)
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">Empleado</th>
                                <td class="fw-bold">{{ $asignacionActual->empleado->nombre_completo ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Núm. empleado</th>
                                <td>{{ $asignacionActual->empleado->numero_empleado ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fecha asignación</th>
                                <td>{{ \Carbon\Carbon::parse($asignacionActual->fecha_asignacion)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Estado</th>
                                <td>
                                    @php
                                        $eColor = match($asignacionActual->estado_asignacion) {
                                            'aceptada'  => 'success',
                                            'pendiente' => 'warning',
                                            'rechazada' => 'danger',
                                            default     => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $eColor }} px-3 py-2">
                                        {{ ucfirst($asignacionActual->estado_asignacion) }}
                                    </span>
                                </td>
                            </tr>
                            @if($asignacionActual->fecha_firma)
                            <tr>
                                <th class="text-muted">Firmado el</th>
                                <td>{{ \Carbon\Carbon::parse($asignacionActual->fecha_firma)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>

                        @role('admin')
                        <div class="d-flex gap-2 mt-3">
                            @if($asignacionActual->estado_asignacion == 'aceptada')
                                <a href="{{ route('asignaciones.moviles.descargar', $asignacionActual->id) }}"
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-download me-1"></i> Descargar carta
                                </a>
                            @endif
                            <form action="{{ route('moviles.devolver', $asignacionActual->id) }}"
                                  method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Registrar devolución del dispositivo?')">
                                    <i class="bi bi-arrow-return-left me-1"></i> Registrar devolución
                                </button>
                            </form>
                        </div>
                        @endrole
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-phone display-4 d-block mb-3 text-muted opacity-50"></i>
                            <p class="mb-3">Este dispositivo no está asignado actualmente.</p>
                            @role('admin')
                            @if($movil->estado === 'Disponible')
                                <a href="{{ route('asignaciones.moviles.create', $movil->id) }}"
                                   class="btn btn-primary">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Asignar dispositivo
                                </a>
                            @endif
                            @endrole
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
