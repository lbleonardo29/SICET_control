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

                        {{-- Barra de búsqueda --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1 text-primary"></i>
                                Buscar Empleado
                            </label>
                            <input type="text" 
                                   id="buscarEmpleado" 
                                   class="form-control form-control-lg" 
                                   placeholder="Escribe nombre o número de empleado...">
                        </div>

                        {{-- Select de empleados --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1 text-primary"></i>
                                Empleado
                            </label>
                            <select name="empleado_id" 
                                    id="empleado_id" 
                                    class="form-select form-select-lg" 
                                    size="5"
                                    required>
                                <option value="">-- Seleccione un empleado --</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->id }}"
                                            data-email="{{ $emp->correo }}"
                                            data-numero="{{ $emp->numero_empleado }}">
                                        {{ $emp->nombre_completo }} - {{ $emp->numero_empleado }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Info del empleado --}}
                        <div id="infoEmpleado" class="alert alert-info d-none">
                            <i class="bi bi-envelope me-2"></i> <span id="empEmail"></span><br>
                            <i class="bi bi-person-badge me-2"></i> Número: <span id="empNumero"></span>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('moviles.disponibles') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="button" class="btn btn-primary px-4 py-2" onclick="abrirModalMovil()">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputBuscar = document.getElementById('buscarEmpleado');
        const selectEmpleado = document.getElementById('empleado_id');
        const opciones = selectEmpleado.querySelectorAll('option');
        const infoDiv = document.getElementById('infoEmpleado');
        const empEmail = document.getElementById('empEmail');
        const empNumero = document.getElementById('empNumero');

        function filtrar() {
            const termino = inputBuscar.value.toLowerCase();
            let visibles = 0;
            
            opciones.forEach(opcion => {
                if (opcion.value === '') return;
                const texto = opcion.textContent.toLowerCase();
                if (texto.includes(termino)) {
                    opcion.style.display = '';
                    visibles++;
                } else {
                    opcion.style.display = 'none';
                }
            });
            
            if (visibles === 1) {
                opciones.forEach(opcion => {
                    if (opcion.value !== '' && opcion.style.display !== 'none') {
                        opcion.selected = true;
                        const event = new Event('change');
                        selectEmpleado.dispatchEvent(event);
                    }
                });
            }
        }

        selectEmpleado.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                empEmail.textContent = selected.getAttribute('data-email') || 'No disponible';
                empNumero.textContent = selected.getAttribute('data-numero') || 'No disponible';
                infoDiv.classList.remove('d-none');

                // Guardar para el modal
                const nombre = selected.textContent.split(' - ')[0].trim();
                document.getElementById('dispMovilNombre').value = nombre;
                document.getElementById('dispMovilNumero').value = selected.getAttribute('data-numero') || 'N/A';
            } else {
                infoDiv.classList.add('d-none');
            }
        });

        inputBuscar.addEventListener('keyup', filtrar);
        inputBuscar.addEventListener('input', filtrar);

        if (selectEmpleado.value) {
            selectEmpleado.dispatchEvent(new Event('change'));
        }
    });

    function abrirModalMovil() {
        const empId = document.getElementById('empleado_id').value;
        if (!empId) {
            alert('Debes seleccionar un empleado.');
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