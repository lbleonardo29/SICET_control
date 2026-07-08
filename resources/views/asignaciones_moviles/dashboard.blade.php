@extends('layouts.sicet')

@section('page-title', 'Asignaciones Moviles')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="bi bi-phone me-2 text-primary"></i>
                Asignaciones de Dispositivos Móviles
            </h2>
            <p class="text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i>
                Total: <strong>{{ $asignaciones->total() }}</strong> registros
                | Activos: <strong class="text-success">{{ $asignaciones->whereNull('fecha_devolucion')->count() }}</strong>
                | Devueltos: <strong class="text-secondary">{{ $asignaciones->whereNotNull('fecha_devolucion')->count() }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                @role('admin')
                <a href="{{ route('moviles.disponibles') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Nueva Asignación
                </a>
                @endrole
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>
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

    {{-- Filtros y búsqueda avanzada --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" class="row g-3 align-items-center">
                {{-- Búsqueda por texto --}}
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0"
                               placeholder="Buscar por empleado, dispositivo, IMEI..."
                               value="{{ request('q') }}">
                    </div>
                </div>

                {{-- Filtro por estado --}}
                <div class="col-md-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                        <option value="devuelto" {{ request('estado') == 'devuelto' ? 'selected' : '' }}>Devueltos</option>
                    </select>
                </div>

                {{-- Filtro por empleado --}}
                <div class="col-md-3">
                    <select name="empleado_id" class="form-select">
                        <option value="">Todos los empleados</option>
                        @foreach($empleadosFiltro ?? [] as $emp)
                            <option value="{{ $emp->id_emp }}" {{ request('empleado_id') == $emp->id_emp ? 'selected' : '' }}>
                                {{ $emp->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por dispositivo --}}
                <div class="col-md-2">
                    <select name="dispositivo_id" class="form-select">
                        <option value="">Todos los dispositivos</option>
                        @foreach($dispositivosFiltro ?? [] as $disp)
                            <option value="{{ $disp->id }}" {{ request('dispositivo_id') == $disp->id ? 'selected' : '' }}>
                                {{ $disp->codigo_interno }} - {{ $disp->marca }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones de acción --}}
                <div class="col-md-1">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                        </button>
                        @if(request()->anyFilled(['q', 'estado', 'empleado_id', 'dispositivo_id']))
                            <a href="{{ route('asignaciones.moviles.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Filtros activos --}}
            @if(request()->anyFilled(['q', 'estado', 'empleado_id', 'dispositivo_id']))
                <div class="mt-3 d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted me-2">Filtros activos:</span>
                    @if(request('q'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-search me-1"></i> {{ request('q') }}
                        </span>
                    @endif
                    @if(request('estado'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-flag me-1"></i> {{ request('estado') == 'activo' ? 'Activos' : 'Devueltos' }}
                        </span>
                    @endif
                    @if(request('empleado_id'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-person me-1"></i> Empleado ID: {{ request('empleado_id') }}
                        </span>
                    @endif
                    @if(request('dispositivo_id'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-phone me-1"></i> Dispositivo ID: {{ request('dispositivo_id') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de Asignaciones</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ $asignaciones->firstItem() ?? 0 }} - {{ $asignaciones->lastItem() ?? 0 }} de {{ $asignaciones->total() }}
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">#</th>
                            <th>Empleado</th>
                            <th>Dispositivo</th>
                            <th>Asignación</th>
                            <th>Estado</th>
                            @role('admin')<th class="text-center">Acciones</th>@endrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            <tr>
                                <td class="px-3 fw-bold">#{{ $asignacion->id }}</td>

                                {{-- Empleado --}}
                                <td>
                                    @if($asignacion->empleado)
                                        <div class="fw-bold">{{ $asignacion->empleado->nombre_completo }}</div>
                                        @if($asignacion->empleado->numero_empleado)
                                            <small class="text-muted d-block">
                                                <i class="bi bi-badge-id me-1"></i>
                                                {{ $asignacion->empleado->numero_empleado }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Empleado eliminado
                                        </span>
                                    @endif
                                </td>

                                {{-- Dispositivo --}}
                                <td>
                                    @if($asignacion->dispositivo)
                                        <div class="fw-bold">{{ $asignacion->dispositivo->marca }} {{ $asignacion->dispositivo->modelo }}</div>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-upc-scan me-1"></i>
                                            IMEI: {{ $asignacion->dispositivo->imei }}
                                        </small>
                                    @else
                                        <span class="text-danger">Dispositivo eliminado</span>
                                    @endif
                                </td>

                                {{-- Fechas (SIN HORA) --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-plus text-primary me-2"></i>
                                        <div>
                                            <div class="fw-bold">
                                                {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($asignacion->fecha_devolucion)
                                        <hr class="my-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-check text-success me-2"></i>
                                            <div>
                                                <div class="fw-bold">
                                                    {{ \Carbon\Carbon::parse($asignacion->fecha_devolucion)->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @if($asignacion->fecha_devolucion)
                                        <span class="badge bg-secondary px-3 py-2">Devuelto</span>
                                    @elseif($asignacion->estado_asignacion === 'pendiente')
                                        <span class="badge bg-warning text-dark px-3 py-2">Pendiente firma</span>
                                    @else
                                        <span class="badge bg-success px-3 py-2">Activo</span>
                                    @endif
                                    
                                    {{-- Indicador PDF --}}
                                    <div class="mt-2">
                                        @if($asignacion->estado_asignacion == 'aceptada')
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                                <i class="bi bi-file-pdf me-1"></i>
                                                PDF
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">
                                                <i class="bi bi-file-pdf me-1"></i>
                                                Sin PDF
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Acciones (solo admin) --}}
                                @role('admin')
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        {{-- Botones PDF --}}
                                        @if($asignacion->estado_asignacion == 'aceptada')
                                            <button type="button"
                                                    class="btn btn-sm btn-info"
                                                    onclick="verPDF('{{ route('asignaciones.moviles.responsiva', $asignacion->id) }}', '{{ route('asignaciones.moviles.descargar', $asignacion->id) }}')"
                                                    title="Ver PDF"
                                                    data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('asignaciones.moviles.descargar', $asignacion->id) }}"
                                               class="btn btn-sm btn-success"
                                               title="Descargar PDF"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endif

                                        {{-- Botón Devolver --}}
                                        @if(!$asignacion->fecha_devolucion)
                                            <form action="{{ route('moviles.devolver', $asignacion->id) }}"
                                                  method="POST"
                                                  class="d-inline devolver-form">
                                                @csrf
                                                @method('PUT')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-devolver"
                                                        title="Devolver dispositivo"
                                                        data-bs-toggle="tooltip"
                                                        data-id="{{ $asignacion->id }}">
                                                    <i class="bi bi-arrow-return-left"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Botón Eliminar --}}
                                        @if($asignacion->fecha_devolucion)
                                            <form action="{{ route('asignaciones.moviles.destroy', $asignacion->id) }}"
                                                  method="POST"
                                                  class="d-inline eliminar-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-dark btn-eliminar"
                                                        title="Eliminar asignación"
                                                        data-bs-toggle="tooltip"
                                                        data-id="{{ $asignacion->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                @endrole
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-phone display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No hay asignaciones registradas</h4>
                                    @role('admin')
                                    <p class="text-muted mb-4">Comienza creando una nueva asignación</p>
                                    <a href="{{ route('moviles.disponibles') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Nueva Asignación
                                    </a>
                                    @endrole
                                </div>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            <i class="bi bi-layout-text-window me-1"></i>
            Página {{ $asignaciones->currentPage() }} de {{ $asignaciones->lastPage() }}
        </div>
        <div>
            {{ $asignaciones->appends(request()->query())->links() }}
        </div>
    </div>

</div>

{{-- Modal para vista previa de PDF --}}
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="pdfModalLabel">
                    <i class="bi bi-file-pdf me-2"></i>
                    Vista previa del documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfFrame" src="" style="width: 100%; height: 75vh;" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="downloadPdfLink" href="#" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Descargar
                </a>
            </div>
        </div>
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
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }
    .card-header {
        border-bottom: 2px solid #f0f0f0;
    }
    .pagination {
        margin-bottom: 0;
    }
    .badge.bg-info {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));

        // Auto-submit al cambiar filtros
        document.querySelector('select[name="estado"]')?.addEventListener('change', function() {
            this.form.submit();
        });
        document.querySelector('select[name="empleado_id"]')?.addEventListener('change', function() {
            this.form.submit();
        });
        document.querySelector('select[name="dispositivo_id"]')?.addEventListener('change', function() {
            this.form.submit();
        });

        // Confirmación para devolver
        document.querySelectorAll('.btn-devolver').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: '¿Devolver dispositivo?',
                    text: 'Esta acción registrará la devolución del dispositivo',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, devolver',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Confirmación para eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: '¿Eliminar asignación?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

    function verPDF(pdfUrl, downloadUrl) {
        document.getElementById('pdfFrame').src = pdfUrl;
        document.getElementById('downloadPdfLink').href = downloadUrl;
        new bootstrap.Modal(document.getElementById('pdfModal')).show();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush