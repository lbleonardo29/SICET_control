@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header con información de la computadora --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <h2 class="mb-0">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Historial de Asignaciones
                </h2>
                <span class="badge bg-primary px-3 py-2">
                    <i class="bi bi-upc-scan me-1"></i>
                    {{ $equipo->codigo_interno }}
                </span>
            </div>
            <p class="text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i>
                {{ $equipo->marca }} {{ $equipo->modelo }} - Serie: {{ $equipo->numero_serie ?? 'N/A' }}
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <a href="{{ route('equipos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total asignaciones</h6>
                            <h3 class="mb-0">{{ $asignaciones->count() }}</h3>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Asignaciones activas</h6>
                            <h3 class="mb-0">{{ $asignaciones->whereNull('fecha_devolucion')->where('estado_asignacion', 'aceptada')->count() }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-secondary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Completadas</h6>
                            <h3 class="mb-0">{{ $asignaciones->whereNotNull('fecha_devolucion')->where('estado_asignacion', '!=', 'rechazada')->count() }}</h3>
                        </div>
                        <i class="bi bi-arrow-return-left fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Rechazadas</h6>
                            <h3 class="mb-0">{{ $asignaciones->where('estado_asignacion', 'rechazada')->count() }}</h3>
                        </div>
                        <i class="bi bi-x-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de historial --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Registro de asignaciones</span>
            </div>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>
                    Imprimir
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportToExcel()">
                    <i class="bi bi-file-excel me-1"></i>
                    Exportar
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="historialTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th><i class="bi bi-person me-1"></i> Empleado</th>
                            <th><i class="bi bi-calendar-plus me-1"></i> Fecha Asignación</th>
                            <th><i class="bi bi-calendar-check me-1"></i> Fecha Devolución</th>
                            <th><i class="bi bi-hourglass-split me-1"></i> Duración</th>
                            <th><i class="bi bi-flag me-1"></i> Estado</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $index => $a)
                        <tr>
                            <td class="fw-bold">{{ $asignaciones->firstItem() + $index }}</td>
                            
                            {{-- Empleado --}}
                            <td>
                                @if($a->empleado)
                                    <div class="fw-bold">{{ $a->empleado->nombre_completo ?? $a->empleado->nombre }}</div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-badge-id me-1"></i>
                                        {{ $a->empleado->numero_empleado }}
                                    </small>
                                @else
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Empleado eliminado
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha asignación --}}
                            <td>
                                <div class="fw-bold">
                                    {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('H:i') }} hrs</small>
                            </td>

                            {{-- Fecha devolución --}}
                            <td>
                                @if($a->fecha_devolucion)
                                    <div class="fw-bold text-success">
                                        {{ \Carbon\Carbon::parse($a->fecha_devolucion)->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($a->fecha_devolucion)->format('H:i') }} hrs</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Duración --}}
                            <td>
                                @if($a->fecha_devolucion)
                                    @php
                                        $inicio = \Carbon\Carbon::parse($a->fecha_asignacion);
                                        $fin = \Carbon\Carbon::parse($a->fecha_devolucion);
                                        $dias = $inicio->diffInDays($fin);
                                        $horas = $inicio->diffInHours($fin) % 24;
                                    @endphp
                                    <span class="badge bg-light text-dark">
                                        {{ $dias }}d {{ $horas }}h
                                    </span>
                                @elseif($a->estado_asignacion == 'pendiente')
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @else
                                    @php
                                        $inicio = \Carbon\Carbon::parse($a->fecha_asignacion);
                                        $dias = $inicio->diffInDays(now());
                                    @endphp
                                    <span class="badge bg-success text-dark">
                                        {{ $dias }} días activo
                                    </span>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="text-center">
                                @if($a->estado_asignacion == 'rechazada')
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Rechazada
                                    </span>
                                @elseif($a->fecha_devolucion)
                                    <span class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Devuelta
                                    </span>
                                @elseif($a->estado_asignacion == 'pendiente')
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-clock-history me-1"></i>
                                        Pendiente
                                    </span>
                                @else
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-play-circle me-1"></i>
                                        Asignada
                                    </span>
                                @endif
                            </td>

                            {{-- PDF --}}
                            <td class="text-center">
                                @if($a->carta_pdf && $a->estado_asignacion == 'aceptada')
                                    <a href="{{ route('asignaciones.descargar', $a->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank"
                                       title="Ver PDF"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-clock-history display-1 text-muted d-block mb-3"></i>
                                <h4 class="text-muted">No hay historial para esta computadora</h4>
                                <p class="text-muted mb-4">Esta computadora no ha sido asignada a ningún empleado</p>
                                @if(auth()->user()->role === 'admin' && $equipo->estado === 'Disponible')
                                    <a href="{{ route('asignaciones.create', $equipo->id) }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Asignar computadora
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if(method_exists($asignaciones, 'links'))
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $asignaciones->firstItem() ?? 0 }} - {{ $asignaciones->lastItem() ?? 0 }} de {{ $asignaciones->total() }} registros
                    </div>
                    <div>
                        {{ $asignaciones->links() }}
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
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .card.bg-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .card.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .card.bg-secondary { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }
    .card.bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .text-white-50 { color: rgba(255,255,255,0.7); }
    .badge.bg-light {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
    }
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));
    });

    function exportToExcel() {
        const table = document.getElementById('historialTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach(col => {
                if (col.cellIndex < 6) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                }
            });
            if (rowData.length > 0) {
                csv.push(rowData.join(','));
            }
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'historial_computadora_{{ $equipo->codigo_interno }}.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>
@endpush