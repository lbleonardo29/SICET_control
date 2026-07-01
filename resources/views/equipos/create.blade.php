@extends('layouts.sicet')

@section('page-title', 'Registrar Equipo')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-plus-circle me-2 text-primary"></i>
            Registrar Nueva Computadora
        </h2>
        <span class="badge bg-primary px-3 py-2">
            <i class="bi bi-pc-display me-1"></i>
            Registro de Computadora
        </span>
    </div>

    {{-- Solo Admin puede ver el formulario --}}
    @if(auth()->user()->role !== 'admin')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Acceso denegado:</strong> No tienes permisos para registrar computadoras.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @else

    {{-- Errores de validación --}}
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
        <div class="col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Datos de la Computadora</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('equipos.store') }}" method="POST" id="computadoraForm">
                        @csrf

                        {{-- Código automático --}}
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Código Interno:</strong> Se generará automáticamente al guardar
                                    <br>
                                    <small class="text-muted">Formato: SICET-0001, SICET-0002, etc.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">

                            {{-- NOMBRE DEL EQUIPO --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag me-1 text-primary"></i>
                                    Nombre del Equipo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre_equipo"
                                       class="form-control form-control-lg @error('nombre_equipo') is-invalid @enderror"
                                       value="{{ old('nombre_equipo') }}"
                                       placeholder="Ej: PC-GERENCIA-01, LAPTOP-VENTAS-01"
                                       required>
                                <small class="text-muted">Nombre descriptivo para identificar el equipo fácilmente</small>
                                @error('nombre_equipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Marca --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag me-1 text-primary"></i>
                                    Marca <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="marca"
                                       class="form-control form-control-lg @error('marca') is-invalid @enderror"
                                       value="{{ old('marca') }}"
                                       placeholder="Ej: Dell, HP, Lenovo"
                                       required>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Modelo --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1 text-primary"></i>
                                    Modelo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="modelo"
                                       class="form-control form-control-lg @error('modelo') is-invalid @enderror"
                                       value="{{ old('modelo') }}"
                                       placeholder="Ej: Latitude 5490, ThinkPad X1"
                                       required>
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Número de serie --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1 text-primary"></i>
                                    Número de Serie <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_serie"
                                       class="form-control form-control-lg @error('numero_serie') is-invalid @enderror"
                                       value="{{ old('numero_serie') }}"
                                       placeholder="Ej: ABC123XYZ"
                                       required>
                                @error('numero_serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- DIRECCIÓN MAC (NUEVO CAMPO) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-wifi me-1 text-primary"></i>
                                    Dirección MAC
                                </label>
                                <input type="text" 
                                       name="direccion_mac"
                                       class="form-control form-control-lg @error('direccion_mac') is-invalid @enderror"
                                       value="{{ old('direccion_mac') }}"
                                       placeholder="Ej: 00:1A:2B:3C:4D:5E"
                                       oninput="this.value = this.value.toUpperCase()">
                                <small class="text-muted">Formato: XX:XX:XX:XX:XX:XX</small>
                                @error('direccion_mac')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Color --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-palette me-1 text-primary"></i>
                                    Color
                                </label>
                                <input type="text" 
                                       name="color"
                                       class="form-control form-control-lg @error('color') is-invalid @enderror"
                                       value="{{ old('color') }}"
                                       placeholder="Ej: Negro, Plateado">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Procesador --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-cpu me-1 text-primary"></i>
                                    Procesador <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="procesador"
                                       class="form-control form-control-lg @error('procesador') is-invalid @enderror"
                                       value="{{ old('procesador') }}"
                                       placeholder="Ej: Intel i5, AMD Ryzen 5"
                                       required>
                                @error('procesador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- RAM --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-memory me-1 text-primary"></i>
                                    RAM <span class="text-danger">*</span>
                                </label>
                                <select name="ram" class="form-select form-select-lg @error('ram') is-invalid @enderror" required>
                                    <option value="">Seleccione</option>
                                    <option value="4GB" {{ old('ram') == '4GB' ? 'selected' : '' }}>4GB</option>
                                    <option value="8GB" {{ old('ram') == '8GB' ? 'selected' : '' }}>8GB</option>
                                    <option value="16GB" {{ old('ram') == '16GB' ? 'selected' : '' }}>16GB</option>
                                    <option value="32GB" {{ old('ram') == '32GB' ? 'selected' : '' }}>32GB</option>
                                    <option value="64GB" {{ old('ram') == '64GB' ? 'selected' : '' }}>64GB</option>
                                </select>
                                @error('ram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- TIPO DE ALMACENAMIENTO --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-device-ssd me-1 text-primary"></i>
                                    Tipo Almacenamiento <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_almacenamiento" class="form-select form-select-lg @error('tipo_almacenamiento') is-invalid @enderror" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="SSD" {{ old('tipo_almacenamiento') == 'SSD' ? 'selected' : '' }}>SSD (Estado Sólido)</option>
                                    <option value="HDD" {{ old('tipo_almacenamiento') == 'HDD' ? 'selected' : '' }}>HDD (Disco Mecánico)</option>
                                    <option value="NVMe" {{ old('tipo_almacenamiento') == 'NVMe' ? 'selected' : '' }}>NVMe (M.2 SSD)</option>
                                    <option value="SSHD" {{ old('tipo_almacenamiento') == 'SSHD' ? 'selected' : '' }}>SSHD (Híbrido)</option>
                                </select>
                                @error('tipo_almacenamiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- CAPACIDAD DE ALMACENAMIENTO --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-hdd-stack me-1 text-primary"></i>
                                    Capacidad <span class="text-danger">*</span>
                                </label>
                                <select name="capacidad_almacenamiento" class="form-select form-select-lg @error('capacidad_almacenamiento') is-invalid @enderror" required>
                                    <option value="">Seleccione capacidad</option>
                                    <option value="128GB" {{ old('capacidad_almacenamiento') == '128GB' ? 'selected' : '' }}>128GB</option>
                                    <option value="256GB" {{ old('capacidad_almacenamiento') == '256GB' ? 'selected' : '' }}>256GB</option>
                                    <option value="512GB" {{ old('capacidad_almacenamiento') == '512GB' ? 'selected' : '' }}>512GB</option>
                                    <option value="1TB" {{ old('capacidad_almacenamiento') == '1TB' ? 'selected' : '' }}>1TB</option>
                                    <option value="2TB" {{ old('capacidad_almacenamiento') == '2TB' ? 'selected' : '' }}>2TB</option>
                                </select>
                                @error('capacidad_almacenamiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cargador --}}
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-plug me-1 text-primary"></i>
                                    ¿Cargador? <span class="text-danger">*</span>
                                </label>
                                <select name="cargador" class="form-select form-select-lg @error('cargador') is-invalid @enderror" required>
                                    <option value="">Seleccione</option>
                                    <option value="1" {{ old('cargador') == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('cargador') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('cargador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Fecha de adquisición --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar me-1 text-primary"></i>
                                    Fecha de Adquisición <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha_adquisicion"
                                       class="form-control form-control-lg @error('fecha_adquisicion') is-invalid @enderror"
                                       value="{{ old('fecha_adquisicion') }}"
                                       max="{{ date('Y-m-d') }}"
                                       required>
                                @error('fecha_adquisicion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Planta --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1 text-primary"></i>
                                    Planta <span class="text-danger">*</span>
                                </label>
                                <select name="planta_id" class="form-select form-select-lg @error('planta_id') is-invalid @enderror" required>
                                    <option value="">Seleccione una planta</option>
                                    @foreach($plantas ?? [] as $planta)
                                        <option value="{{ $planta->id }}" {{ old('planta_id') == $planta->id ? 'selected' : '' }}>
                                            {{ $planta->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('planta_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Observaciones --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-1 text-primary"></i>
                                    Observaciones <span class="text-danger">*</span>
                                </label>
                                <textarea name="observaciones"
                                          class="form-control @error('observaciones') is-invalid @enderror"
                                          rows="4"
                                          placeholder="Detalles adicionales de la computadora (estado, accesorios, observaciones importantes...)"
                                          required>{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Máximo 500 caracteres
                                </div>
                            </div>

                        </div>

                        {{-- Botones --}}
                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-3">
                            <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4 py-2">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="button" class="btn btn-success px-4 py-2" id="resumenBtn" onclick="abrirResumenEquipo()">
                                <i class="bi bi-clipboard-check me-2"></i>
                                Resumen y guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL RESUMEN DE REGISTRO ===== --}}
    <div class="modal fade" id="modalResumenEquipo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Confirmar registro de computadora
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="max-height:65vh;overflow-y:auto">
                    <p class="text-muted mb-3">Revisa los datos antes de guardar. El código interno se generará automáticamente.</p>
                    <div class="row g-2">
                        <div class="col-md-6"><small class="text-muted d-block">Nombre del equipo</small><span class="fw-bold" id="res-nombre">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Marca</small><span class="fw-bold" id="res-marca">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Modelo</small><span class="fw-bold" id="res-modelo">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Número de serie</small><span class="fw-bold" id="res-serie">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Dirección MAC</small><span class="fw-bold" id="res-mac">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Color</small><span class="fw-bold" id="res-color">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Procesador</small><span class="fw-bold" id="res-procesador">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">RAM</small><span class="fw-bold" id="res-ram">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Almacenamiento</small><span class="fw-bold" id="res-almacen">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Cargador</small><span class="fw-bold" id="res-cargador">—</span></div>
                        <div class="col-md-3"><small class="text-muted d-block">Fecha adquisición</small><span class="fw-bold" id="res-fecha">—</span></div>
                        <div class="col-md-6"><small class="text-muted d-block">Planta</small><span class="fw-bold" id="res-planta">—</span></div>
                        <div class="col-12"><small class="text-muted d-block">Observaciones</small><span class="fw-bold" id="res-obs">—</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i> Regresar y editar
                    </button>
                    <button type="button" class="btn btn-success px-4" id="btnConfirmarEquipo">
                        <i class="bi bi-save me-2"></i> Confirmar y guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .form-control-lg, .form-select-lg {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
        font-size: 1rem;
    }
    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    .alert-info {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border: none;
        color: #0369a1;
    }
    textarea {
        resize: vertical;
        min-height: 100px;
    }
    .text-muted {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Abre el modal de resumen tras validar los campos obligatorios del formulario.
    function abrirResumenEquipo() {
        const form = document.getElementById('computadoraForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const val = n => {
            const el = form.querySelector('[name="' + n + '"]');
            return el && el.value ? el.value : '—';
        };
        const txtSel = n => {
            const el = form.querySelector('[name="' + n + '"]');
            return (el && el.selectedIndex >= 0) ? el.options[el.selectedIndex].text : '—';
        };
        const set = (id, v) => { document.getElementById(id).textContent = v; };

        set('res-nombre', val('nombre_equipo'));
        set('res-marca', val('marca'));
        set('res-modelo', val('modelo'));
        set('res-serie', val('numero_serie'));
        set('res-mac', val('direccion_mac'));
        set('res-color', val('color'));
        set('res-procesador', val('procesador'));
        set('res-ram', val('ram'));
        set('res-almacen', (val('tipo_almacenamiento') + ' ' + val('capacidad_almacenamiento')).trim());
        const carg = form.querySelector('[name="cargador"]').value;
        set('res-cargador', carg === '1' ? 'Sí' : (carg === '0' ? 'No' : '—'));
        set('res-fecha', val('fecha_adquisicion'));
        set('res-planta', txtSel('planta_id'));
        set('res-obs', val('observaciones'));

        new bootstrap.Modal(document.getElementById('modalResumenEquipo')).show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // =====================================================
        // CONVERTIR A MAYÚSCULAS MIENTRAS EL USUARIO ESCRIBE
        // =====================================================
        const camposTexto = document.querySelectorAll('input[type="text"], textarea');
        
        camposTexto.forEach(campo => {
            campo.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            campo.addEventListener('paste', function(e) {
                setTimeout(() => {
                    this.value = this.value.toUpperCase();
                }, 10);
            });
            
            campo.addEventListener('blur', function() {
                this.value = this.value.toUpperCase();
            });
        });

        // =====================================================
        // SPINNER AL ENVIAR
        // =====================================================
        document.getElementById('computadoraForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnConfirmarEquipo');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            }
        });

        // =====================================================
        // CONFIRMAR DESDE EL MODAL DE RESUMEN
        // =====================================================
        const btnConfirmar = document.getElementById('btnConfirmarEquipo');
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function () {
                document.getElementById('computadoraForm').submit();
            });
        }

        // =====================================================
        // VALIDAR QUE LA FECHA NO SEA FUTURA
        // =====================================================
        const fechaInput = document.querySelector('input[name="fecha_adquisicion"]');
        if (fechaInput) {
            fechaInput.addEventListener('change', function() {
                const fecha = new Date(this.value);
                const hoy = new Date();
                if (fecha > hoy) {
                    alert('La fecha de adquisición no puede ser futura');
                    this.value = '';
                }
            });
        }

        // =====================================================
        // FORMATEAR DIRECCIÓN MAC (XX:XX:XX:XX:XX:XX)
        // =====================================================
        const macInput = document.querySelector('input[name="direccion_mac"]');
        if (macInput) {
            macInput.addEventListener('input', function(e) {
                let value = this.value.replace(/[^A-Fa-f0-9]/g, '');
                let formatted = '';
                for (let i = 0; i < value.length && i < 12; i++) {
                    if (i > 0 && i % 2 === 0) {
                        formatted += ':';
                    }
                    formatted += value[i];
                }
                this.value = formatted.toUpperCase();
            });
        }
    });
</script>
@endpush