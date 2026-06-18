@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-person-plus-fill me-2 text-primary"></i>
            Registrar Nuevo Empleado
        </h2>
        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-person-badge me-1"></i>
            Nuevo Registro
        </span>
    </div>

    {{-- Alertas de error --}}
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
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Datos del Empleado</h5>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('empleados.store') }}" method="POST" id="empleadoForm">
                        @csrf

                        <div class="row g-4">

                            {{-- Número de empleado --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-badge-id me-1 text-primary"></i>
                                    Número de Empleado <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_empleado"
                                       id="numero_empleado"
                                       class="form-control form-control-lg @error('numero_empleado') is-invalid @enderror"
                                       value="{{ old('numero_empleado') }}"
                                       placeholder="Ej: 12345"
                                       maxlength="20"
                                       required>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Identificador único del empleado
                                </div>
                                @error('numero_empleado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nombre completo --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person me-1 text-primary"></i>
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre"
                                       class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}"
                                       placeholder="Ej: Juan Pérez García"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Correo electrónico --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1 text-primary"></i>
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email"
                                       id="email"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="Ej: juan.perez@ejemplo.com"
                                       required>
                                <div class="form-text" id="emailHelp"></div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Contraseña --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-key me-1 text-primary"></i>
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password"
                                           id="password"
                                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           placeholder="Mínimo 8 caracteres"
                                           minlength="8"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-shield-lock me-1"></i>
                                    Mínimo 8 caracteres
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Rol --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-shield me-1 text-primary"></i>
                                    Rol <span class="text-danger">*</span>
                                </label>
                                <select name="role" class="form-select form-select-lg @error('role') is-invalid @enderror" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="usuario" {{ old('role') == 'usuario' ? 'selected' : '' }}>Usuario</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Planta (opcional, según tu estructura) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1 text-primary"></i>
                                    Planta
                                </label>
                                <select name="id_planta" class="form-select form-select-lg">
                                    <option value="">Seleccione una planta</option>
                                    @foreach($plantas ?? [] as $planta)
                                        <option value="{{ $planta->id }}">{{ $planta->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Opcional
                                </div>
                            </div>

                            {{-- Activo (checkbox) --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="activo" 
                                           id="activo" 
                                           value="1"
                                           {{ old('activo', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="activo">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Empleado activo
                                    </label>
                                </div>
                            </div>

                        </div>

                        {{-- Resumen --}}
                        <div class="alert alert-info mt-4 mb-0" id="resumenEmpleado" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Resumen del empleado:</strong>
                                    <p class="mb-0" id="resumenTexto"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('empleados.index') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success px-4 py-2" id="submitBtn">
                                <i class="bi bi-save me-2"></i>
                                Guardar Empleado
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
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
    .progress {
        border-radius: 10px;
        background-color: #e9ecef;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
    .progress-bar.weak { background-color: #dc3545; }
    .progress-bar.medium { background-color: #ffc107; }
    .progress-bar.strong { background-color: #28a745; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle contraseña
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        // Validación de email en tiempo real
        const email = document.getElementById('email');
        const emailHelp = document.getElementById('emailHelp');
        
        email.addEventListener('input', function() {
            const value = this.value;
            if (value && !value.includes('@')) {
                emailHelp.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>El correo debe contener @</span>';
            } else if (value && !value.includes('.')) {
                emailHelp.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>El correo debe tener un dominio válido</span>';
            } else {
                emailHelp.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Correo válido</span>';
            }
        });

        // Medidor de fuerza de contraseña
        password.addEventListener('input', function() {
            const value = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (value.length >= 8) strength += 25;
            if (value.match(/[a-z]+/)) strength += 25;
            if (value.match(/[A-Z]+/)) strength += 25;
            if (value.match(/[0-9]+/) || value.match(/[$@#&!]+/)) strength += 25;
            
            strengthBar.style.width = strength + '%';
            
            strengthBar.classList.remove('weak', 'medium', 'strong');
            if (strength <= 50) {
                strengthBar.classList.add('weak');
            } else if (strength <= 75) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });

        // Resumen del empleado
        const form = document.getElementById('empleadoForm');
        const numeroEmpleado = document.getElementById('numero_empleado');
        const nombreInput = document.querySelector('input[name="nombre"]');
        const emailInput = email;
        const resumenDiv = document.getElementById('resumenEmpleado');
        const resumenTexto = document.getElementById('resumenTexto');
        
        function actualizarResumen() {
            if (numeroEmpleado.value && nombreInput.value && emailInput.value) {
                resumenTexto.innerHTML = `
                    <i class="bi bi-badge-id me-1"></i> ${numeroEmpleado.value}<br>
                    <i class="bi bi-person me-1"></i> ${nombreInput.value}<br>
                    <i class="bi bi-envelope me-1"></i> ${emailInput.value}
                `;
                resumenDiv.style.display = 'block';
            } else {
                resumenDiv.style.display = 'none';
            }
        }
        
        numeroEmpleado.addEventListener('input', actualizarResumen);
        nombreInput.addEventListener('input', actualizarResumen);
        emailInput.addEventListener('input', actualizarResumen);

        // Spinner al enviar
        form.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        });
    });
</script>
@endpush