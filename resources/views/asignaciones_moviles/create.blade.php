@extends('layouts.app')

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
                    <form action="{{ route('asignaciones.moviles.store') }}" method="POST" id="asignacionForm">
                        @csrf
                        <input type="hidden" name="dispositivo_movil_id" value="{{ $movil->id }}">

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
                                    <option value="{{ $emp->id_emp }}"  
                                            data-email="{{ $emp->email }}"
                                            data-numero="{{ $emp->id_emp }}">
                                        {{ $emp->nombre_completo }} - {{ $emp->id_emp }}
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
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-check-circle me-2"></i>
                                Asignar dispositivo
                            </button>
                        </div>
                    </form>
                </div>
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
</script>
@endpush