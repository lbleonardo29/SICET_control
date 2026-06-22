@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-pencil-square me-2 text-warning"></i>
            Editar Empleado
        </h2>
        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-person-badge me-1"></i>
            ID: {{ $empleado->numero_empleado }}
        </span>
    </div>

    {{-- Alertas --}}
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

    {{-- Tarjeta de información --}}
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Datos del Empleado</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('empleados.update', $empleado->id) }}"
                          method="POST"
                          id="empleadoForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">

                            {{-- Número de empleado (solo lectura) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-badge-id me-1 text-warning"></i>
                                    Número de Empleado
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg bg-light"
                                       value="{{ $empleado->numero_empleado }}"
                                       disabled>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    El número de empleado no se puede modificar
                                </div>
                            </div>

                            {{-- Estado del empleado --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-toggle-on me-1 text-warning"></i>
                                    Estado del Empleado
                                </label>
                                <select name="activo" class="form-select form-select-lg" required>
                                    <option value="1" {{ old('activo', $empleado->activo) == 1 ? 'selected' : '' }}>
                                        <i class="bi bi-check-circle"></i> Activo
                                    </option>
                                    <option value="0" {{ old('activo', $empleado->activo) == 0 ? 'selected' : '' }}>
                                        <i class="bi bi-x-circle"></i> Inactivo
                                    </option>
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Un empleado inactivo no podrá acceder al sistema
                                </div>
                            </div>

                            {{-- Nombre completo --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person me-1 text-warning"></i>
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="nombre"
                                       class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $empleado->nombre_completo) }}"
                                       placeholder="Ej: Juan Pérez García"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Correo electrónico --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1 text-warning"></i>
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       value="{{ old('email', $empleado->correo) }}"
                                       placeholder="Ej: juan.perez@ejemplo.com"
                                       required>
                                <div class="form-text" id="emailHelp"></div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Planta (opcional) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1 text-warning"></i>
                                    Planta
                                </label>
                                <select name="planta_id" class="form-select form-select-lg" required>
                                    <option value="">Seleccione una planta</option>
                                    @foreach($plantas ?? [] as $planta)
                                        <option value="{{ $planta->id }}"
                                            {{ old('planta_id', $empleado->planta_id) == $planta->id ? 'selected' : '' }}>
                                            {{ $planta->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Acceso al sistema --}}
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-shield-lock me-2 text-warning"></i>
                                            Acceso al Sistema
                                        </h6>

                                        @php
                                            $rolActual = old('role', optional($empleado->user)->role);
                                        @endphp

                                        <div class="row">
                                            <div class="col-md-8">
                                                <select name="role" class="form-select form-select-lg" id="roleSelect">
                                                    <option value="" {{ $rolActual == null ? 'selected' : '' }}>
                                                        -- Sin acceso --
                                                    </option>
                                                    <option value="user" {{ $rolActual === 'user' ? 'selected' : '' }}>
                                                        👤 Usuario
                                                    </option>
                                                    <option value="admin" {{ $rolActual === 'admin' ? 'selected' : '' }}>
                                                        ⚡ Administrador
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                @if($empleado->user)
                                                    <span class="badge bg-success p-3 w-100">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Usuario existente
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary p-3 w-100">
                                                        <i class="bi bi-exclamation-circle me-1"></i>
                                                        Sin usuario
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Opción de cambiar contraseña (solo si tiene usuario) --}}
                                        @if($empleado->user)
                                        <div class="mt-3">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="cambiar_password" 
                                                       id="cambiarPassword"
                                                       value="1">
                                                <label class="form-check-label" for="cambiarPassword">
                                                    Cambiar contraseña
                                                </label>
                                            </div>
                                            
                                            <div id="passwordFields" style="display: none;" class="mt-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-key me-1"></i>
                                                    Nueva Contraseña
                                                </label>
                                                <div class="input-group">
                                                    <input type="password" 
                                                           name="password"
                                                           id="password"
                                                           class="form-control form-control-lg"
                                                           placeholder="Mínimo 8 caracteres"
                                                           minlength="8">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Mínimo 8 caracteres. Si no se especifica, mantiene la actual.
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <small>
                                                <strong>Usuario:</strong> {{ $empleado->nombre_completo }} ({{ $empleado->correo }})
                                            </small>
                                        </div>
                                    </div>
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
                                Guardar Cambios
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
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    .card-header.bg-warning {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .badge.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        font-size: 0.9rem;
    }
    .badge.bg-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        font-size: 0.9rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación de email
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

        // Toggle cambio de contraseña
        const cambiarPassword = document.getElementById('cambiarPassword');
        const passwordFields = document.getElementById('passwordFields');
        
        if (cambiarPassword) {
            cambiarPassword.addEventListener('change', function() {
                passwordFields.style.display = this.checked ? 'block' : 'none';
                if (this.checked) {
                    document.getElementById('password').required = true;
                } else {
                    document.getElementById('password').required = false;
                }
            });
        }

        // Toggle mostrar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }

        // Spinner al enviar
        document.getElementById('empleadoForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        });

        // Confirmación si se cambia el rol
        const roleSelect = document.getElementById('roleSelect');
        const rolOriginal = roleSelect.value;
        
        roleSelect.addEventListener('change', function() {
            if (this.value !== rolOriginal) {
                if (!confirm('¿Estás seguro de cambiar el rol del usuario? Esto afectará sus permisos.')) {
                    this.value = rolOriginal;
                }
            }
        });
    });
</script>
@endpush