@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-phone me-2 text-primary"></i>
                Inventario de Dispositivos Móviles
            </h2>
            <div class="d-flex gap-4 text-muted">
                <span>
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Disponibles: <strong>{{ $moviles->where('estado', 'Disponible')->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-person-check-fill text-primary me-1"></i>
                    Asignados: <strong>{{ $moviles->where('estado', 'Asignado')->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-tools text-warning me-1"></i>
                    En reparación: <strong>{{ $moviles->where('estado', 'En reparación')->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-archive text-secondary me-1"></i>
                    Total: <strong>{{ $moviles->total() ?? $moviles->count() }}</strong>
                </span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            @if(auth()->user()->role === 'admin')
                <div class="btn-group" role="group">
                    <a href="{{ route('moviles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Registrar Dispositivo
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="exportToExcel()">
                        <i class="bi bi-file-excel me-1"></i>
                        Exportar
                    </button>
                </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros y búsqueda --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0"
                               placeholder="Buscar por marca, modelo, IMEI o SIM..."
                               value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="Asignado" {{ request('estado') == 'Asignado' ? 'selected' : '' }}>Asignado</option>
                        <option value="En reparación" {{ request('estado') == 'En reparación' ? 'selected' : '' }}>En reparación</option>
                        <option value="Baja" {{ request('estado') == 'Baja' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="marca" class="form-select">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas ?? [] as $marca)
                            <option value="{{ $marca }}" {{ request('marca') == $marca ? 'selected' : '' }}>
                                {{ $marca }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="orden" class="form-select">
                        <option value="reciente" {{ request('orden') == 'reciente' ? 'selected' : '' }}>Más reciente</option>
                        <option value="antiguo" {{ request('orden') == 'antiguo' ? 'selected' : '' }}>Más antiguo</option>
                        <option value="marca" {{ request('orden') == 'marca' ? 'selected' : '' }}>Por marca</option>
                        <option value="estado" {{ request('orden') == 'estado' ? 'selected' : '' }}>Por estado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                        @if(request()->anyFilled(['q', 'estado', 'marca', 'orden']))
                            <a href="{{ route('moviles.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de dispositivos --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de Dispositivos Móviles</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ $moviles->firstItem() ?? 0 }} - {{ $moviles->lastItem() ?? 0 }} de {{ $moviles->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="movilesTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">#</th>
                            <th>Código</th>
                            <th>Marca / Modelo</th>
                            <th>IMEI</th>
                            <th>Número SIM</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            @if(auth()->user()->role === 'admin')
                                <th class="text-center">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($moviles as $movil)
                            @php
                                $ultimaAsignacion = $movil->ultimaAsignacion ?? null;
                                $estadoAsignacion = $ultimaAsignacion ? $ultimaAsignacion->estado_asignacion : null;
                                $tienePendiente = ($estadoAsignacion == 'pendiente');
                                $tieneAceptada = ($estadoAsignacion == 'aceptada' && !$ultimaAsignacion->fecha_devolucion);
                            @endphp
                            <tr>
                                <td class="px-3 fw-bold">{{ $movil->id }}</td>

                                {{-- Código --}}
                                <td>
                                    <span class="fw-bold">{{ $movil->codigo_interno ?? 'N/A' }}</span>
                                </td>

                                {{-- Marca / Modelo --}}
                                <td>
                                    <div class="fw-bold">{{ $movil->marca }} {{ $movil->modelo }}</div>
                                </td>

                                {{-- IMEI --}}
                                <td><span class="font-monospace">{{ $movil->imei ?? 'N/A' }}</span></td>

                                {{-- Número SIM --}}
                                <td>{{ $movil->numero_sim ?? 'N/A' }}</td>

                                {{-- Estado --}}
                                <td class="text-center">
                                    @if($tienePendiente)
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Pendiente
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            Esperando respuesta de<br>
                                            <strong>{{ $ultimaAsignacion->empleado->nombre_completo ?? 'empleado' }}</strong>
                                        </small>
                                    @elseif($tieneAceptada)
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Asignado
                                        </span>
                                    @elseif($movil->estado == 'Disponible')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Disponible
                                        </span>
                                    @elseif($movil->estado == 'En reparación')
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-tools me-1"></i>
                                            En reparación
                                        </span>
                                    @elseif($movil->estado == 'Baja')
                                        <span class="badge bg-secondary px-3 py-2">
                                            <i class="bi bi-archive me-1"></i>
                                            Baja
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            {{ $movil->estado ?? 'Sin estado' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Fecha registro --}}
                                <td>
                                    @if($movil->created_at)
                                        {{ \Carbon\Carbon::parse($movil->created_at)->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                @if(auth()->user()->role === 'admin')
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('moviles.edit', $movil) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Editar dispositivo"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            {{-- Historial --}}
                                            <a href="{{ route('moviles.historial', $movil) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Ver historial"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-clock-history"></i>
                                            </a>

                                            {{-- Asignar (solo si está disponible y no tiene pendiente) --}}
                                            @if($movil->estado == 'Disponible' && !$tienePendiente)
                                                <a href="{{ route('asignaciones.moviles.create', $movil->id) }}" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="Asignar dispositivo"
                                                   data-bs-toggle="tooltip">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            @endif

                                            {{-- DAR DE BAJA (reemplaza al botón Eliminar) --}}
                                            @if($movil->estado != 'Baja')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-warning btn-baja" 
                                                        title="Dar de baja dispositivo"
                                                        data-bs-toggle="tooltip"
                                                        data-id="{{ $movil->id }}"
                                                        data-codigo="{{ $movil->codigo_interno }}"
                                                        data-marca="{{ $movil->marca }}"
                                                        data-modelo="{{ $movil->modelo }}"
                                                        data-imei="{{ $movil->imei }}">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary" 
                                                        disabled 
                                                        title="Ya está dado de baja">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === 'admin' ? 8 : 7 }}" class="text-center py-5">
                                    <i class="bi bi-phone display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No hay dispositivos registrados</h4>
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('moviles.create') }}" class="btn btn-primary mt-3">
                                            Registrar Dispositivo
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
        @if(method_exists($moviles, 'links'))
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $moviles->firstItem() ?? 0 }} - {{ $moviles->lastItem() ?? 0 }} de {{ $moviles->total() }} registros
                    </div>
                    <div>
                        {{ $moviles->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal Dar de Baja Móvil --}}
<div class="modal fade" id="bajaMovilModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-archive me-2"></i>
                    Dar de Baja Dispositivo Móvil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBajaMovil" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>¿Estás seguro de dar de baja este dispositivo?</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Información del dispositivo:</strong><br>
                        <span id="infoMovil"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_baja" class="form-label">
                            Motivo de baja <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_baja" 
                                  id="motivo_baja" 
                                  class="form-control" 
                                  rows="3" 
                                  required 
                                  placeholder="Ej: Dispositivo dañado, robado, obsoleto, etc."></textarea>
                        <div class="form-text">
                            Este motivo quedará registrado en el historial.
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <small>⚠️ El dispositivo quedará marcado como "Baja" y no podrá ser asignado nuevamente.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-archive me-1"></i>
                        Confirmar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .btn-sm {
        padding: 0.4rem 0.7rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-sm:hover {
        transform: translateY(-2px);
    }
    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #000;
    }
    .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: #000;
    }
    .btn-outline-success:hover {
        background-color: #198754;
        color: #fff;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
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
        
        document.querySelector('select[name="marca"]')?.addEventListener('change', function() {
            this.form.submit();
        });
        
        document.querySelector('select[name="orden"]')?.addEventListener('change', function() {
            this.form.submit();
        });

        // Modal para dar de baja móvil
        const bajaModal = new bootstrap.Modal(document.getElementById('bajaMovilModal'));
        const formBaja = document.getElementById('formBajaMovil');
        const infoMovilSpan = document.getElementById('infoMovil');
        
        document.querySelectorAll('.btn-baja').forEach(btn => {
            btn.addEventListener('click', function() {
                const movilId = this.dataset.id;
                const codigo = this.dataset.codigo;
                const marca = this.dataset.marca;
                const modelo = this.dataset.modelo;
                const imei = this.dataset.imei;
                
                // Mostrar información del dispositivo
                infoMovilSpan.innerHTML = `
                    <strong>Código:</strong> ${codigo}<br>
                    <strong>Marca/Modelo:</strong> ${marca} ${modelo}<br>
                    <strong>IMEI:</strong> ${imei}
                `;
                
                // Actualizar action del formulario
                formBaja.action = `/moviles/${movilId}/baja`;
                
                // Limpiar textarea
                document.getElementById('motivo_baja').value = '';
                
                // Mostrar modal
                bajaModal.show();
            });
        });
    });

    function exportToExcel() {
        const table = document.getElementById('movilesTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach(col => {
                // Excluir la columna de acciones
                if (!col.querySelector('button') && !col.querySelector('a')) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""').replace(/\n/g, ' ') + '"');
                }
            });
            if (rowData.length > 0) {
                csv.push(rowData.join(','));
            }
        });

        const csvContent = csv.join('\n');
        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'inventario_moviles.csv';
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            title: 'Exportación completada',
            text: 'El archivo CSV se ha descargado correctamente',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush