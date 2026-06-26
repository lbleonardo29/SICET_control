@extends('layouts.sicet')

@section('page-title', 'Nueva Asignacion')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-person-plus me-2 text-success"></i>
            Asignar Computadora
        </h2>
        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-pc-display me-1"></i>
            {{ $equipo->codigo_interno }} - {{ $equipo->marca }} {{ $equipo->modelo }}
        </span>
    </div>

    {{-- Alertas --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Seleccionar Empleado</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Información del equipo --}}
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-pc-display me-3 fs-4"></i>
                            <div>
                                <strong>Equipo a asignar:</strong><br>
                                {{ $equipo->marca }} {{ $equipo->modelo }}<br>
                                <small class="text-muted">Código: {{ $equipo->codigo_interno }} | Serie: {{ $equipo->numero_serie ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- datos del equipo para el modal --}}
                    <span id="equipoInfo" hidden
                        data-equipo="{{ $equipo->marca }} {{ $equipo->modelo }}"
                        data-codigo="Código: {{ $equipo->codigo_interno }} · Serie: {{ $equipo->numero_serie ?? 'N/A' }}"></span>

                    <form action="{{ route('asignaciones.store') }}" method="POST" id="asignacionForm">
                        @csrf
                        <input type="hidden" name="equipo_id" value="{{ $equipo->id }}">
                        <input type="hidden" id="dispNombre" value="">
                        <input type="hidden" id="dispNumero" value="">

                        {{-- BUSCADOR DE EMPLEADOS --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1 text-success"></i>
                                Buscar Empleado <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       id="buscadorEmpleado"
                                       class="form-control form-control-lg border-start-0"
                                       placeholder="Buscar por nombre, apellido, correo o número de empleado..."
                                       autocomplete="off">
                            </div>
                            <small class="text-muted">Escribe para buscar empleados (mínimo 2 caracteres)</small>
                        </div>

                        {{-- Lista de resultados / Selección --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge me-1 text-success"></i>
                                Empleado Seleccionado
                            </label>
                            <div id="resultadosEmpleados" class="border rounded-3" style="min-height: 200px; max-height: 300px; overflow-y: auto;">
                                <div class="text-center text-muted py-5" id="mensajeInicial">
                                    <i class="bi bi-search display-6 d-block mb-2"></i>
                                    <p>Escribe en el buscador para encontrar empleados</p>
                                </div>
                            </div>
                            <input type="hidden" id="empleado_id" name="empleado_id" required>
                            <div id="empleadoError" class="invalid-feedback d-none">Debes seleccionar un empleado</div>
                        </div>

                        {{-- Fecha de asignación --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1 text-success"></i>
                                Fecha de Asignación <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="fecha_asignacion"
                                   class="form-control form-control-lg @error('fecha_asignacion') is-invalid @enderror"
                                   value="{{ old('fecha_asignacion', date('Y-m-d')) }}"
                                   required>
                            @error('fecha_asignacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-chat-text me-1 text-success"></i>
                                Observaciones
                            </label>
                            <textarea name="observaciones"
                                      class="form-control @error('observaciones') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Motivo de la asignación, condiciones especiales, etc.">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-3">
                            <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="button" class="btn btn-success px-4 py-2" id="submitBtn" disabled onclick="abrirModalConfirmacion()">
                                <i class="bi bi-clipboard-check me-2"></i>
                                Resumen y confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- ===== MODAL CONFIRMACIÓN ===== --}}
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Confirmar Asignación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                {{-- Equipo --}}
                <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                    <i class="bi bi-pc-display-horizontal fs-2 text-success me-3 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted small mb-1">Equipo a asignar</div>
                        <div class="fw-bold" id="mc-equipo">—</div>
                        <div class="text-muted small" id="mc-codigo">—</div>
                    </div>
                </div>

                {{-- Empleado --}}
                <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                    <i class="bi bi-person-circle fs-2 text-primary me-3 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted small mb-1">Asignado a</div>
                        <div class="fw-bold" id="mc-empleado">—</div>
                        <div class="text-muted small" id="mc-empleado-num">—</div>
                    </div>
                </div>

                {{-- Fecha y observaciones --}}
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-calendar2 me-1"></i>Fecha de asignación
                            </div>
                            <div class="fw-bold" id="mc-fecha">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-chat-text me-1"></i>Observaciones
                            </div>
                            <div class="small text-muted fst-italic" id="mc-obs">Sin observaciones</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 py-2 mb-0">
                    <i class="bi bi-send fs-5 flex-shrink-0"></i>
                    <span class="small">Al confirmar se enviará un correo y notificación al empleado.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmarOk">
                    <i class="bi bi-check-circle me-2"></i>
                    Confirmar y asignar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .empleado-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .empleado-card:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    .empleado-card.selected {
        background-color: #d1e7dd;
        border-left-color: #198754;
    }
    .resultado-item {
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 0;
    }
    .resultado-item:last-child {
        border-bottom: none;
    }
    .spinner-busqueda {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #e9ecef;
        border-top-color: #198754;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorEmpleado');
        const resultadosDiv = document.getElementById('resultadosEmpleados');
        const empleadoIdInput = document.getElementById('empleado_id');
        const empleadoError = document.getElementById('empleadoError');
        const submitBtn = document.getElementById('submitBtn');
        let timeoutId = null;

        // Función para buscar empleados
        function buscarEmpleados(termino) {
            if (termino.length < 2) {
                resultadosDiv.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-search display-6 d-block mb-2"></i>
                        <p>Escribe al menos 2 caracteres para buscar</p>
                    </div>
                `;
                return;
            }

            // Mostrar spinner de carga
            resultadosDiv.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-busqueda mx-auto mb-3" style="width: 2rem; height: 2rem;"></div>
                    <p class="text-muted">Buscando empleados...</p>
                </div>
            `;

            // Realizar petición AJAX
            fetch(`/api/empleados/search?q=${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        resultadosDiv.innerHTML = `
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-person-x display-6 d-block mb-2"></i>
                                <p>No se encontraron empleados con "${termino}"</p>
                            </div>
                        `;
                        return;
                    }

                    // Mostrar resultados
                    resultadosDiv.innerHTML = data.map(empleado => `
                        <div class="resultado-item p-3 empleado-card"
                             data-id="${empleado.id}"
                             data-nombre="${empleado.nombre_completo}"
                             data-numero="${empleado.numero_empleado || 'N/A'}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">
                                        <i class="bi bi-person-circle me-2 text-primary"></i>
                                        ${empleado.nombre_completo}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <span class="me-3">
                                            <i class="bi bi-badge-number me-1"></i>
                                            Núm: ${empleado.numero_empleado || 'N/A'}
                                        </span>
                                        <span class="me-3">
                                            <i class="bi bi-envelope me-1"></i>
                                            ${empleado.correo || 'Sin correo'}
                                        </span>
                                        <span>
                                            <i class="bi bi-building me-1"></i>
                                            ${empleado.area || 'Sin área'}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <i class="bi bi-check-circle-fill text-success" style="display: none;"></i>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    // Agregar eventos de selección a los resultados
                    document.querySelectorAll('.empleado-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const nombre = this.querySelector('.fw-bold').innerText;
                            
                            // Remover selección de todos
                            document.querySelectorAll('.empleado-card').forEach(c => {
                                c.classList.remove('selected');
                                c.style.borderLeftColor = 'transparent';
                            });
                            
                            // Marcar selección
                            this.classList.add('selected');
                            this.style.borderLeftColor = '#198754';
                            
                            // Guardar ID + datos para modal
                            empleadoIdInput.value = id;
                            document.getElementById('dispNombre').value = this.dataset.nombre || nombre;
                            document.getElementById('dispNumero').value = this.dataset.numero || '';

                            // Limpiar error
                            empleadoError.classList.add('d-none');

                            // Habilitar botón de submit
                            submitBtn.disabled = false;
                            
                            // Mostrar mensaje de selección
                            resultadosDiv.insertAdjacentHTML('beforeend', `
                                <div class="alert alert-success m-2 p-2 small">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Seleccionado: ${nombre}
                                </div>
                            `);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultadosDiv.innerHTML = `
                        <div class="text-center text-danger py-5">
                            <i class="bi bi-exclamation-triangle-fill display-6 d-block mb-2"></i>
                            <p>Error al buscar empleados. Intenta de nuevo.</p>
                        </div>
                    `;
                });
        }

        // Evento de búsqueda con debounce
        buscador.addEventListener('input', function() {
            const termino = this.value.trim();
            
            // Resetear selección
            if (empleadoIdInput.value) {
                empleadoIdInput.value = '';
                submitBtn.disabled = true;
            }
            
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => buscarEmpleados(termino), 300);
        });

        // Validar al intentar enviar (por si acaso se hace submit por teclado)
        document.getElementById('asignacionForm').addEventListener('submit', function(e) {
            if (!empleadoIdInput.value) {
                e.preventDefault();
                empleadoError.classList.remove('d-none');
                empleadoError.textContent = 'Debes seleccionar un empleado de la lista';
            }
        });
    });

    // Abrir modal con resumen
    function abrirModalConfirmacion() {
        const empId = document.getElementById('empleado_id').value;
        if (!empId) {
            document.getElementById('empleadoError').classList.remove('d-none');
            document.getElementById('empleadoError').textContent = 'Debes seleccionar un empleado de la lista';
            return;
        }

        const eq   = document.getElementById('equipoInfo');
        const fecha = document.querySelector('[name="fecha_asignacion"]').value;
        const obs   = document.querySelector('[name="observaciones"]').value.trim();

        document.getElementById('mc-equipo').textContent      = eq.dataset.equipo;
        document.getElementById('mc-codigo').textContent      = eq.dataset.codigo;
        document.getElementById('mc-empleado').textContent    = document.getElementById('dispNombre').value;
        document.getElementById('mc-empleado-num').textContent = 'Núm. empleado: ' + document.getElementById('dispNumero').value;
        document.getElementById('mc-fecha').textContent       = fecha
            ? new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {day:'2-digit', month:'long', year:'numeric'})
            : '—';
        document.getElementById('mc-obs').textContent         = obs || 'Sin observaciones';

        new bootstrap.Modal(document.getElementById('modalConfirmacion')).show();
    }

    // Confirmar desde el modal → submit real
    document.getElementById('btnConfirmarOk').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Asignando...';
        document.getElementById('asignacionForm').submit();
    });
</script>
@endpush