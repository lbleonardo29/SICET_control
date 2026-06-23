@extends('layouts.sicet')

@section('page-title', 'Nuevo Reporte')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-clipboard-data me-2 text-primary"></i>
                    Registro de Entrada/Salida
                </h2>
                <span class="badge bg-primary px-3 py-2">
                    <i class="bi bi-shield-lock me-1"></i>
                    {{ auth()->user()->name }}
                </span>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Nuevo Reporte de Movimiento</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('reportes.store') }}" id="reporteForm">
                        @csrf

                        <div class="row g-4">
                            {{-- Número de empleado --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-1 text-primary"></i>
                                    Número de empleado <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_empleado" 
                                       id="numero_empleado"
                                       class="form-control form-control-lg @error('numero_empleado') is-invalid @enderror"
                                       value="{{ old('numero_empleado') }}"
                                       placeholder="Ej: 25384"
                                       list="empleadosList"
                                       autocomplete="off"
                                       required>
                                <datalist id="empleadosList">
                                    @foreach($empleados ?? [] as $empleado)
                                        <option value="{{ $empleado->numero_empleado }}">
                                            {{ $empleado->numero_empleado }} - {{ $empleado->nombre_completo }}
                                        </option>
                                    @endforeach
                                </datalist>
                                @error('numero_empleado') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Número de empleado que entrega o recibe la computadora
                                </div>
                            </div>

                            {{-- Matrícula (solo computadoras asignadas al empleado) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1 text-primary"></i>
                                    Matrícula de la computadora <span class="text-danger">*</span>
                                </label>
                                <select name="matricula" id="matricula" class="form-select form-select-lg @error('matricula') is-invalid @enderror" required disabled>
                                    <option value="">Primero seleccione un empleado</option>
                                </select>
                                @error('matricula') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="infoComputadoras">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Seleccione un empleado para ver sus computadoras asignadas
                                </div>
                            </div>

                            {{-- Tipo de movimiento --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-arrow-left-right me-1 text-primary"></i>
                                    Tipo de movimiento <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="tipo" 
                                               id="entrada" 
                                               value="entrada" 
                                               {{ old('tipo', 'entrada') == 'entrada' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-success" for="entrada">
                                            <i class="bi bi-box-arrow-in-down"></i> Entrada
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="tipo" 
                                               id="salida" 
                                               value="salida"
                                               {{ old('tipo') == 'salida' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-warning" for="salida">
                                            <i class="bi bi-box-arrow-up"></i> Salida
                                        </label>
                                    </div>
                                </div>
                                @error('tipo') 
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Inconsistencias --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-exclamation-triangle me-1 text-primary"></i>
                                    Inconsistencias (opcional)
                                </label>
                                <textarea name="inconsistencias" 
                                          class="form-control @error('inconsistencias') is-invalid @enderror" 
                                          rows="3"
                                          placeholder="Ej: Golpe en la tapa, no prende, teclas dañadas, etc.">{{ old('inconsistencias') }}</textarea>
                                @error('inconsistencias') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Describe cualquier anomalía observada (máximo 500 caracteres)
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2" id="submitBtn">
                                <i class="bi bi-save me-2"></i>
                                Guardar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .form-control-lg, .form-select-lg {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }
    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('reporteForm');
        const numeroEmpleadoInput = document.getElementById('numero_empleado');
        const matriculaSelect = document.getElementById('matricula');
        const infoDiv = document.getElementById('infoComputadoras');

        // Función para cargar computadoras asignadas al empleado
        function cargarComputadorasAsignadas() {
            const empleadoId = numeroEmpleadoInput.value.trim();
            
            // Limpiar select
            matriculaSelect.innerHTML = '<option value="">Seleccione una computadora</option>';
            
            if (!empleadoId) {
                matriculaSelect.disabled = true;
                infoDiv.innerHTML = '<i class="bi bi-info-circle me-1"></i> Seleccione un empleado para ver sus computadoras asignadas';
                return;
            }

            // Mostrar loading
            infoDiv.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Cargando computadoras asignadas...';
            
            // Llamada AJAX para obtener las computadoras del empleado
            fetch(`/api/empleado/${empleadoId}/computadoras`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        matriculaSelect.disabled = true;
                        infoDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-warning"></i> Este empleado no tiene computadoras asignadas';
                        return;
                    }
                    
                    // Agregar opciones
                    data.forEach(equipo => {
                        const option = document.createElement('option');
                        option.value = equipo.codigo_interno;
                        option.textContent = `${equipo.codigo_interno} - ${equipo.marca} ${equipo.modelo}`;
                        matriculaSelect.appendChild(option);
                    });
                    
                    matriculaSelect.disabled = false;
                    infoDiv.innerHTML = `<i class="bi bi-check-circle me-1 text-success"></i> ${data.length} computadora(s) asignada(s) a este empleado`;
                    
                    // Si hay una sola computadora, seleccionarla automáticamente
                    if (data.length === 1) {
                        matriculaSelect.value = data[0].codigo_interno;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    matriculaSelect.disabled = true;
                    infoDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-danger"></i> Error al cargar las computadoras';
                });
        }

        // Eventos
        numeroEmpleadoInput.addEventListener('change', cargarComputadorasAsignadas);
        numeroEmpleadoInput.addEventListener('blur', cargarComputadorasAsignadas);
        
        // Si hay un valor inicial (por old), ejecutar
        if (numeroEmpleadoInput.value) {
            cargarComputadorasAsignadas();
        }

        // Spinner al enviar
        if (form) {
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            });
        }
    });
</script>
@endpush