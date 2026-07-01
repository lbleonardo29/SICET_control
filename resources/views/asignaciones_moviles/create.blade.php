@extends('layouts.sicet')

@section('page-title', 'Nueva Asignacion Movil')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-plus-circle me-2 text-primary"></i>
            Asignar Dispositivo Móvil
        </h2>
        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-phone me-1"></i>
            Nueva Asignación
        </span>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2 fs-5"></i>
                        <h5 class="mb-0">Detalles de la Asignación</h5>
                    </div>
                </div>
                
                <div class="card-body p-4">

                    {{-- datos del dispositivo para el modal --}}
                    <span id="movilInfo" hidden
                        data-equipo="{{ $movil->marca }} {{ $movil->modelo }}"
                        data-codigo="Código: {{ $movil->codigo_interno }} · IMEI: {{ $movil->imei }}"></span>

                    <form action="{{ route('asignaciones.moviles.store') }}" method="POST" id="asignacionForm">
                        @csrf
                        <input type="hidden" name="dispositivo_movil_id" value="{{ $movil->id }}">
                        <input type="hidden" id="dispMovilNombre" value="">
                        <input type="hidden" id="dispMovilNumero" value="">

                        {{-- Dispositivo --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-phone me-1 text-primary"></i>
                                Dispositivo a Asignar
                            </label>
                            <div class="bg-light p-3 rounded-3 border">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Código</small>
                                        <span class="fw-bold">{{ $movil->codigo_interno }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Marca / Modelo</small>
                                        <span class="fw-bold">{{ $movil->marca }} {{ $movil->modelo }}</span>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">IMEI</small>
                                        <span class="fw-bold">{{ $movil->imei }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha de asignación --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1 text-primary"></i>
                                Fecha de Asignación
                            </label>
                            <input type="date"
                                   name="fecha_asignacion"
                                   class="form-control form-control-lg"
                                   value="{{ old('fecha_asignacion', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                        </div>

                        {{-- BUSCADOR DE EMPLEADOS --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1 text-primary"></i>
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
                                <i class="bi bi-person-badge me-1 text-primary"></i>
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

                        {{-- Botones --}}
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('moviles.disponibles') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="button" class="btn btn-primary px-4 py-2" id="submitBtn" disabled onclick="abrirModalMovil()">
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
<div class="modal fade" id="modalConfMovil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Confirmar Asignación de Móvil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                {{-- Dispositivo --}}
                <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                    <i class="bi bi-phone fs-2 text-primary me-3 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted small mb-1">Dispositivo a asignar</div>
                        <div class="fw-bold" id="mcm-movil">—</div>
                        <div class="text-muted small" id="mcm-codigo">—</div>
                    </div>
                </div>

                {{-- Empleado --}}
                <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                    <i class="bi bi-person-circle fs-2 text-success me-3 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted small mb-1">Asignado a</div>
                        <div class="fw-bold" id="mcm-empleado">—</div>
                        <div class="text-muted small" id="mcm-empleado-num">—</div>
                    </div>
                </div>

                {{-- Fecha --}}
                <div class="p-3 rounded-3 bg-light mb-3">
                    <div class="text-muted small mb-1">
                        <i class="bi bi-calendar2 me-1"></i>Fecha de asignación
                    </div>
                    <div class="fw-bold" id="mcm-fecha">—</div>
                </div>

                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0">
                    <i class="bi bi-send fs-5 flex-shrink-0"></i>
                    <span class="small">Al confirmar se enviará un correo y notificación al empleado.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </button>
                <button type="button" class="btn btn-primary px-4" id="btnConfirmarMovil">
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
        background-color: #cfe2ff;
        border-left-color: #0d6efd;
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
        border-top-color: #0d6efd;
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

            resultadosDiv.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-busqueda mx-auto mb-3" style="width: 2rem; height: 2rem;"></div>
                    <p class="text-muted">Buscando empleados...</p>
                </div>
            `;

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

                    document.querySelectorAll('.empleado-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const nombre = this.querySelector('.fw-bold').innerText;

                            document.querySelectorAll('.empleado-card').forEach(c => {
                                c.classList.remove('selected');
                                c.style.borderLeftColor = 'transparent';
                            });

                            this.classList.add('selected');
                            this.style.borderLeftColor = '#0d6efd';

                            empleadoIdInput.value = id;
                            document.getElementById('dispMovilNombre').value = this.dataset.nombre || nombre;
                            document.getElementById('dispMovilNumero').value = this.dataset.numero || '';

                            empleadoError.classList.add('d-none');
                            submitBtn.disabled = false;

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

        buscador.addEventListener('input', function() {
            const termino = this.value.trim();

            if (empleadoIdInput.value) {
                empleadoIdInput.value = '';
                submitBtn.disabled = true;
            }

            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => buscarEmpleados(termino), 300);
        });

        document.getElementById('asignacionForm').addEventListener('submit', function(e) {
            if (!empleadoIdInput.value) {
                e.preventDefault();
                empleadoError.classList.remove('d-none');
                empleadoError.textContent = 'Debes seleccionar un empleado de la lista';
            }
        });
    });

    function abrirModalMovil() {
        const empId = document.getElementById('empleado_id').value;
        if (!empId) {
            document.getElementById('empleadoError').classList.remove('d-none');
            document.getElementById('empleadoError').textContent = 'Debes seleccionar un empleado de la lista';
            return;
        }

        const mv    = document.getElementById('movilInfo');
        const fecha = document.querySelector('[name="fecha_asignacion"]').value;

        document.getElementById('mcm-movil').textContent      = mv.dataset.equipo;
        document.getElementById('mcm-codigo').textContent     = mv.dataset.codigo;
        document.getElementById('mcm-empleado').textContent   = document.getElementById('dispMovilNombre').value;
        document.getElementById('mcm-empleado-num').textContent = 'Núm. empleado: ' + document.getElementById('dispMovilNumero').value;
        document.getElementById('mcm-fecha').textContent      = fecha
            ? new Date(fecha + 'T12:00:00').toLocaleDateString('es-MX', {day:'2-digit', month:'long', year:'numeric'})
            : '—';

        new bootstrap.Modal(document.getElementById('modalConfMovil')).show();
    }

    document.getElementById('btnConfirmarMovil').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Asignando...';
        document.getElementById('asignacionForm').submit();
    });
</script>
@endpush