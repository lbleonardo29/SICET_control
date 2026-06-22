@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-pc-display me-2"></i>
            Asignaciones de Computadoras
        </h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#asignarModal">
            <i class="bi bi-plus-circle me-1"></i>
            Nueva Asignación
        </button>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Modal de Asignación --}}
    <div class="modal fade" id="asignarModal" tabindex="-1" aria-labelledby="asignarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="asignarModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Asignación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('asignaciones.store') }}">
                    @csrf
                    <div class="modal-body">
                        
                        {{-- Selección de empleado --}}
                        <div class="mb-3">
                            <label for="empleado_id" class="form-label fw-bold">
                                <i class="bi bi-person me-1"></i> Empleado
                            </label>
                            <select name="empleado_id" id="empleado_id" class="form-select" required>
                                <option value="">Seleccione un empleado</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}">
                                        {{ $empleado->nombre_completo }} ({{ $empleado->numero_empleado }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Selección de computadora --}}
                        <div class="mb-3">
                            <label for="equipo_id" class="form-label fw-bold">
                                <i class="bi bi-pc-display me-1"></i> Computadora
                            </label>
                            <select name="equipo_id" id="equipo_id" class="form-select" required>
                                <option value="">Seleccione una computadora</option>
                                @foreach($equiposDisponibles as $computadora)
                                    <option value="{{ $computadora->id }}">
                                        {{ $computadora->codigo_interno }} - {{ $computadora->marca }} {{ $computadora->modelo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha de asignación --}}
                        <div class="mb-3">
                            <label for="fecha_asignacion" class="form-label fw-bold">
                                <i class="bi bi-calendar me-1"></i> Fecha de Asignación
                            </label>
                            <input type="date" 
                                   name="fecha_asignacion" 
                                   id="fecha_asignacion" 
                                   class="form-control" 
                                   value="{{ old('fecha_asignacion', date('Y-m-d')) }}" 
                                   required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Asignar Computadora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabla de asignaciones activas --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-table me-2 text-primary"></i>
                <h5 class="mb-0">Asignaciones Activas</h5>
                <span class="badge bg-primary ms-3">{{ $asignaciones->count() }} registros</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Computadora</th>
                            <th>Empleado</th>
                            <th>No. Empleado</th>
                            <th>Planta</th>
                            <th>Fecha Asignación</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $asignacion->equipo->codigo_interno }}</span>
                                </td>
                                <td>
                                    <div>{{ $asignacion->equipo->marca }} {{ $asignacion->equipo->modelo }}</div>
                                    <small class="text-muted">S/N: {{ $asignacion->equipo->numero_serie ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $asignacion->empleado->nombre_completo }}</div>
                                    <small class="text-muted">{{ $asignacion->empleado->correo }}</small>
                                </td>
                                <td>{{ $asignacion->empleado->numero_empleado }}</td>
                                <td>{{ $asignacion->empleado->planta->nombre ?? 'N/A' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('asignaciones.devolver', $asignacion->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('¿Estás seguro de devolver esta computadora?')"
                                                title="Devolver computadora">
                                            <i class="bi bi-arrow-return-left me-1"></i>
                                            Devolver
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">No hay asignaciones activas</h5>
                                    <p class="text-muted">Las asignaciones aparecerán aquí cuando se registren.</p>
                                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#asignarModal">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Crear primera asignación
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginación --}}
    @if(method_exists($asignaciones, 'links'))
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Mostrando {{ $asignaciones->firstItem() ?? 0 }} - {{ $asignaciones->lastItem() ?? 0 }} de {{ $asignaciones->total() }} registros
            </div>
            <div>
                {{ $asignaciones->links() }}
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@push('scripts')
<script>
    // Validación de fechas
    document.getElementById('fecha_asignacion').max = new Date().toISOString().split('T')[0];
</script>
@endpush