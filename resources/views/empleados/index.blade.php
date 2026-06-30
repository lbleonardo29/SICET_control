@extends('layouts.sicet')

@section('page-title', 'Empleados')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-people-fill me-2 text-primary"></i>
                Gestión de Empleados
            </h2>
            <div class="d-flex gap-3 text-muted">
                <span>
                    <i class="bi bi-person-check me-1"></i>
                    Activos: <strong class="text-success">{{ $empleados->where('es_activo', true)->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-person-x me-1"></i>
                    Inactivos: <strong class="text-danger">{{ $empleados->where('es_activo', false)->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-shield me-1"></i>
                    Admin: <strong class="text-warning">{{ $empleados->filter(fn($e) => optional($e->user)->role === 'admin')->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-person me-1"></i>
                    Usuarios: <strong>{{ $empleados->filter(fn($e) => optional($e->user)->role === 'user')->count() }}</strong>
                </span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-info bg-opacity-25 text-info-emphasis px-3 py-2">
                <i class="bi bi-cloud-arrow-down me-1"></i>
                Directorio sincronizado desde el corporativo (solo lectura)
            </span>
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

    {{-- Barra de búsqueda y filtros --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               id="searchInput"
                               placeholder="Buscar por nombre, correo o núm. de empleado...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="estadoFilter">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="rolFilter">
                        <option value="">Todos los roles</option>
                        <option value="admin">Administradores</option>
                        <option value="user">Usuarios</option>
                        <option value="sin">Sin usuario</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de empleados --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de Empleados</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                Total: {{ $empleados->count() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="empleadosTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3" style="white-space:nowrap">Núm. Empleado</th>
                            <th>Empleado</th>
                            <th>Contacto</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empleados as $empleado)
                            <tr>
                                <td class="px-3 fw-bold text-primary" style="font-size:1rem;letter-spacing:.5px">
                                    {{ $empleado->numero_empleado ?? '—' }}
                                </td>

                                {{-- Información del empleado --}}
                                <td>
                                    <div class="fw-bold">{{ $empleado->nombre_completo }}</div>
                                </td>

                                {{-- Contacto --}}
                                <td>
                                    @if($empleado->correo)
                                        <div>
                                            <i class="bi bi-envelope me-1 text-muted"></i>
                                            {{ $empleado->correo }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-50 text-muted">
                                            <i class="bi bi-envelope-slash me-1"></i>Sin correo
                                        </span>
                                    @endif
                                    @if($empleado->planta)
                                        <small class="text-muted d-block">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $empleado->planta->nombre }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Rol --}}
                                <td>
                                    @if($empleado->user)
                                        <span class="badge {{ $empleado->user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} px-3 py-2">
                                            <i class="bi bi-shield-{{ $empleado->user->role === 'admin' ? 'lock' : 'person' }} me-1"></i>
                                            {{ ucfirst($empleado->user->role) }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Sin usuario
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td>
                                    <span class="badge {{ $empleado->es_activo ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                        <i class="bi {{ $empleado->es_activo ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                                        {{ $empleado->es_activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-people display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No se encontraron empleados</h4>
                                    <p class="text-muted mb-0">Ajusta la búsqueda o el filtro de estado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if(method_exists($empleados, 'links'))
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $empleados->firstItem() ?? 0 }} - {{ $empleados->lastItem() ?? 0 }} de {{ $empleados->total() }} registros
                    </div>
                    <div>
                        {{ $empleados->links() }}
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
    .btn-sm {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    .badge {
        font-weight: 500;
        font-size: 0.8rem;
    }
    .input-group-text {
        background-color: white;
        border-right: none;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        const estadoFilter = document.getElementById('estadoFilter');
        const rolFilter = document.getElementById('rolFilter');
        const table = document.getElementById('empleadosTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        function filtrarTabla() {
            const searchTerm = searchInput.value.toLowerCase();
            const estadoValue = estadoFilter.value;
            const rolValue = rolFilter.value;

            Array.from(rows).forEach(row => {
                if (row.cells.length === 1) return; // Fila vacía

                const numEmp  = row.cells[0]?.textContent.toLowerCase() || '';
                const nombre  = row.cells[1]?.textContent.toLowerCase() || '';
                const email   = row.cells[2]?.textContent.toLowerCase() || '';

                // Estado (columna 4)
                const estadoCell = row.cells[4]?.textContent.toLowerCase() || '';
                const esActivo = estadoCell.includes('activo');

                // Rol (columna 3)
                const rolCell = row.cells[3]?.textContent.toLowerCase() || '';
                const esAdmin = rolCell.includes('admin');
                const esUser  = rolCell.includes('user');
                const sinUsuario = rolCell.includes('sin usuario');

                // Filtro de búsqueda
                const matchesSearch = searchTerm === '' ||
                    numEmp.includes(searchTerm) ||
                    nombre.includes(searchTerm) ||
                    email.includes(searchTerm);

                // Filtro de estado
                let matchesEstado = true;
                if (estadoValue !== '') {
                    matchesEstado = (estadoValue === '1' && esActivo) || 
                                   (estadoValue === '0' && !esActivo);
                }

                // Filtro de rol
                let matchesRol = true;
                if (rolValue !== '') {
                    if (rolValue === 'admin') matchesRol = esAdmin;
                    else if (rolValue === 'user') matchesRol = esUser;
                    else if (rolValue === 'sin') matchesRol = sinUsuario;
                }

                row.style.display = matchesSearch && matchesEstado && matchesRol ? '' : 'none';
            });

            // Mostrar contador de resultados
            const visibleRows = Array.from(rows).filter(r => r.style.display !== 'none').length;
            const totalRows = rows.length;
            
            let mensaje = document.getElementById('resultadosBusqueda');
            if (!mensaje) {
                mensaje = document.createElement('div');
                mensaje.id = 'resultadosBusqueda';
                mensaje.className = 'mt-2 text-muted small';
                document.querySelector('.card-body').appendChild(mensaje);
            }
            mensaje.textContent = `Mostrando ${visibleRows} de ${totalRows} registros`;
        }

        searchInput.addEventListener('keyup', filtrarTabla);
        estadoFilter.addEventListener('change', filtrarTabla);
        rolFilter.addEventListener('change', filtrarTabla);

        // Confirmación para activar/desactivar con SweetAlert
        document.querySelectorAll('.btn-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                const activo = this.dataset.activo === '1';
                const action = activo ? 'desactivar' : 'activar';
                
                Swal.fire({
                    title: `¿${action} empleado?`,
                    text: `Esta acción ${activo ? 'inhabilitará' : 'habilitará'} el acceso del empleado`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: activo ? '#dc3545' : '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sí, ${action}`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

    function limpiarFiltros() {
        document.getElementById('searchInput').value = '';
        document.getElementById('estadoFilter').value = '';
        document.getElementById('rolFilter').value = '';
        
        // Disparar evento de búsqueda
        const event = new Event('keyup');
        document.getElementById('searchInput').dispatchEvent(event);
    }
</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush