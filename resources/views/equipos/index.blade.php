@extends('layouts.sicet')

@section('page-title', 'Equipos')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-pc-display me-2 text-primary"></i>
                Inventario de Computadoras
            </h2>
            <div class="d-flex gap-4 text-muted">
                <span>
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Disponibles: <strong>{{ $conteoEstados['Disponible'] ?? 0 }}</strong>
                </span>
                <span>
                    <i class="bi bi-person-check-fill text-primary me-1"></i>
                    Asignadas: <strong>{{ $conteoEstados['Asignado'] ?? 0 }}</strong>
                </span>
                <span>
                    <i class="bi bi-tools text-warning me-1"></i>
                    En reparación: <strong>{{ $conteoEstados['En reparación'] ?? 0 }}</strong>
                </span>
                <span>
                    <i class="bi bi-archive text-secondary me-1"></i>
                    Total: <strong>{{ $equipos->total() ?? $equipos->count() }}</strong>
                </span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            @if(auth()->user()->role === 'admin')
                <div class="btn-group" role="group">
                    <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Registrar Computadora
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

    {{-- Filtros y búsqueda avanzada --}}
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
                               placeholder="Buscar por nombre, código, marca, modelo o serie..."
                               value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>
                            Disponible
                        </option>
                        <option value="Asignado" {{ request('estado') == 'Asignado' ? 'selected' : '' }}>
                            Asignado
                        </option>
                        <option value="En reparación" {{ request('estado') == 'En reparación' ? 'selected' : '' }}>
                            En reparación
                        </option>
                        <option value="Baja" {{ request('estado') == 'Baja' ? 'selected' : '' }}>
                            Baja
                        </option>
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
                            <i class="bi bi-funnel me-1"></i>
                            Filtrar
                        </button>
                        @if(request()->anyFilled(['q', 'estado', 'marca', 'orden']))
                            <a href="{{ route('equipos.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Filtros activos --}}
            @if(request()->anyFilled(['q', 'estado', 'marca']))
                <div class="mt-3 d-flex align-items-center">
                    <span class="text-muted me-2">Filtros activos:</span>
                    @if(request('q'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-search me-1"></i> {{ request('q') }}
                        </span>
                    @endif
                    @if(request('estado'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-flag me-1"></i> {{ request('estado') }}
                        </span>
                    @endif
                    @if(request('marca'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-tag me-1"></i> {{ request('marca') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla de computadoras --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de Computadoras</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ $equipos->firstItem() ?? 0 }} - {{ $equipos->lastItem() ?? 0 }} de {{ $equipos->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="equiposTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">#</th>
                            <th><i class="bi bi-tag me-1"></i> Nombre</th>
                            <th><i class="bi bi-upc-scan me-1"></i> Código</th>
                            <th><i class="bi bi-tag me-1"></i> Marca / Modelo</th>
                            <th><i class="bi bi-cpu me-1"></i> Especificaciones</th>
                            <th><i class="bi bi-flag me-1"></i> Estado</th>
                            <th><i class="bi bi-calendar me-1"></i> Adquisición</th>
                            @if(auth()->user()->role === 'admin')
                                <th class="text-center">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipos as $index => $computadora)
                            @php
                                $ultimaAsignacion = $computadora->ultimaAsignacion ?? null;
                                $estadoAsignacion = $ultimaAsignacion ? $ultimaAsignacion->estado_asignacion : null;
                                $tienePendiente = ($estadoAsignacion == 'pendiente');
                                $tieneAceptada = ($estadoAsignacion == 'aceptada' && !$ultimaAsignacion->fecha_devolucion);
                            @endphp
                            <tr>
                                <td class="px-3 fw-bold">{{ $equipos->firstItem() + $index }}</td>

                                {{-- Nombre del Equipo (NUEVO) --}}
                                <td>
                                    <span class="fw-bold text-primary">{{ $computadora->nombre_equipo ?? 'N/A' }}</span>
                                </td>

                                {{-- Código --}}
                                <td>
                                    <span class="fw-bold">{{ $computadora->codigo_interno ?? 'N/A' }}</span>
                                    @if($computadora->numero_serie)
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-upc-scan me-1"></i>
                                            Serie: {{ $computadora->numero_serie }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Marca / Modelo --}}
                                <td>
                                    <div class="fw-bold">{{ $computadora->marca }} {{ $computadora->modelo }}</div>
                                    @if($computadora->color)
                                        <small class="text-muted">
                                            <i class="bi bi-palette me-1"></i>
                                            {{ $computadora->color }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Especificaciones --}}
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($computadora->procesador)
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-cpu me-1"></i>
                                                {{ $computadora->procesador }}
                                            </span>
                                        @endif
                                        @if($computadora->ram)
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-memory me-1"></i>
                                                {{ $computadora->ram }}
                                            </span>
                                        @endif
                                        @if($computadora->ssd)
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-device-ssd me-1"></i>
                                                {{ $computadora->ssd }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-plug me-1"></i>
                                        Cargador: {{ $computadora->cargador ? 'Sí' : 'No' }}
                                    </small>
                                </td>

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
                                    @elseif($computadora->estado == 'Disponible')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Disponible
                                        </span>
                                    @elseif($computadora->estado == 'En reparación')
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-tools me-1"></i>
                                            En reparación
                                        </span>
                                    @elseif($computadora->estado == 'Baja')
                                        <span class="badge bg-secondary px-3 py-2">
                                            <i class="bi bi-archive me-1"></i>
                                            Baja
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            {{ $computadora->estado ?? 'Sin estado' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Fecha adquisición --}}
                                <td>
                                    @if($computadora->fecha_adquisicion)
                                        @php
                                            setlocale(LC_TIME, 'es_ES.utf8', 'spanish');
                                            \Carbon\Carbon::setLocale('es');
                                        @endphp
                                        <div>{{ \Carbon\Carbon::parse($computadora->fecha_adquisicion)->translatedFormat('d \d\e F \d\e Y') }}</div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($computadora->fecha_adquisicion)->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Acciones admin --}}
                                @if(auth()->user()->role === 'admin')
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('equipos.edit', $computadora) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="Editar computadora"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="{{ route('equipos.historial', $computadora) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="Ver historial"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-clock-history"></i>
                                            </a>

                                            @if($computadora->estado != 'Baja')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-warning btn-baja"
                                                        title="Dar de baja computadora"
                                                        data-bs-toggle="tooltip"
                                                        data-id="{{ $computadora->id }}"
                                                        data-codigo="{{ $computadora->codigo_interno }}"
                                                        data-marca="{{ $computadora->marca }}"
                                                        data-modelo="{{ $computadora->modelo }}">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="Ya está dada de baja">
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
                                    <i class="bi bi-pc-display display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No hay computadoras registradas</h4>
                                    <p class="text-muted mb-4">
                                        @if(request('q'))
                                            No se encontraron resultados para "{{ request('q') }}"
                                        @else
                                            Comienza registrando la primera computadora
                                        @endif
                                    </p>
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Registrar Computadora
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
        @if(method_exists($equipos, 'links'))
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $equipos->firstItem() ?? 0 }} - {{ $equipos->lastItem() ?? 0 }} de {{ $equipos->total() }} registros
                    </div>
                    <div>
                        {{ $equipos->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal Dar de Baja --}}
<div class="modal fade" id="bajaEquipoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-archive me-2"></i>
                    Dar de Baja Computadora
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBajaEquipo" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>¿Estás seguro de dar de baja este equipo?</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Información del equipo:</strong><br>
                        <span id="infoEquipo"></span>
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
                                  placeholder="Ej: Equipo obsoleto, dañado, robado, etc."></textarea>
                        <div class="form-text">
                            Este motivo quedará registrado en el historial.
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <small> El equipo quedará marcado como "Baja" y no podrá ser asignado nuevamente.</small>
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
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .badge.bg-light {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
    }
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    .card-header {
        border-bottom: 2px solid #f0f0f0;
    }
    .pagination {
        margin-bottom: 0;
    }
    .bg-info.text-dark {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));

        const estadoSelect = document.querySelector('select[name="estado"]');
        const marcaSelect = document.querySelector('select[name="marca"]');
        const ordenSelect = document.querySelector('select[name="orden"]');
        
        if (estadoSelect) estadoSelect.addEventListener('change', function() { this.form.submit(); });
        if (marcaSelect) marcaSelect.addEventListener('change', function() { this.form.submit(); });
        if (ordenSelect) ordenSelect.addEventListener('change', function() { this.form.submit(); });

        const bajaModal = new bootstrap.Modal(document.getElementById('bajaEquipoModal'));
        const formBaja = document.getElementById('formBajaEquipo');
        const infoEquipoSpan = document.getElementById('infoEquipo');
        
        document.querySelectorAll('.btn-baja').forEach(btn => {
            btn.addEventListener('click', function() {
                const equipoId = this.dataset.id;
                const codigo = this.dataset.codigo;
                const marca = this.dataset.marca;
                const modelo = this.dataset.modelo;
                
                infoEquipoSpan.innerHTML = `
                    <strong>Código:</strong> ${codigo}<br>
                    <strong>Marca/Modelo:</strong> ${marca} ${modelo}
                `;
                
                formBaja.action = `/equipos/${equipoId}/baja`;
                document.getElementById('motivo_baja').value = '';
                bajaModal.show();
            });
        });
    });

    function exportToExcel() {
        const table = document.getElementById('equiposTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach(col => {
                if (!col.querySelector('button') && !col.querySelector('a')) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""').replace(/\n/g, ' ') + '"');
                }
            });
            if (rowData.length > 0) csv.push(rowData.join(','));
        });

        const csvContent = csv.join('\n');
        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'inventario_computadoras.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush