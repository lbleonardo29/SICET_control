@extends('layouts.sicet')

@section('page-title', 'Mi Perfil')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-circle me-2 text-primary"></i>
                    Mi Perfil
                </h2>
                <span class="badge bg-primary px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i>
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>

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

            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="mb-0">Información Personal</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Datos del usuario --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Nombre completo</label>
                                <div class="fw-bold fs-5">{{ auth()->user()->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Correo electrónico</label>
                                <div class="fw-bold">{{ auth()->user()->email }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Rol</label>
                                <div>
                                    <span class="badge {{ auth()->user()->role === 'admin' ? 'bg-danger' : 'bg-primary' }} px-3 py-2">
                                        <i class="bi bi-shield-{{ auth()->user()->role === 'admin' ? 'lock' : 'person' }} me-1"></i>
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            {{-- Foto de perfil --}}
                            <div class="position-relative d-inline-block">
                                <img src="{{ auth()->user()->profile_photo
                                    ? asset('storage/'.auth()->user()->profile_photo)
                                    : asset('img/default-user.png') }}"
                                    class="rounded-circle border border-3 border-primary shadow-sm"
                                    id="fotoPerfil"
                                    width="150"
                                    height="150"
                                    style="object-fit: cover;">
                                <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 border border-2 border-white">
                                    <i class="bi bi-camera-fill text-white small"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-secondary">ID: {{ auth()->user()->id }}</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Formulario para cambiar foto --}}
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-image me-2 text-primary"></i>
                        Cambiar foto de perfil
                    </h6>

                    <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" id="perfilForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="file" 
                                           name="profile_photo" 
                                           id="profile_photo" 
                                           class="form-control @error('profile_photo') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/jpg"
                                           onchange="previewImage(event)">
                                    <label class="input-group-text" for="profile_photo">
                                        <i class="bi bi-upload"></i>
                                    </label>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Formatos permitidos: JPG, JPEG, PNG. Máximo 2MB.
                                </div>
                                @error('profile_photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" id="submitBtn">
                                        <i class="bi bi-save me-2"></i>
                                        Guardar
                                    </button>
                                    @if(auth()->user()->profile_photo)
                                        <button type="button" class="btn btn-outline-danger" id="btnEliminarFoto" onclick="confirmarEliminarFoto()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Vista previa de la nueva foto --}}
                    <div id="previewContainer" class="mt-4" style="display: none;">
                        <hr>
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-eye me-2 text-primary"></i>
                            Vista previa
                        </h6>
                        <div class="text-center">
                            <img id="previewImage" 
                                 class="rounded-circle border border-2 border-primary shadow-sm"
                                 width="120"
                                 height="120"
                                 style="object-fit: cover;">
                            <div class="mt-2">
                                <span class="badge bg-info text-dark">Nueva foto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información adicional --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="text-primary mb-2">
                                <i class="bi bi-calendar-check fs-3"></i>
                            </div>
                            <div class="fw-bold">Miembro desde</div>
                            <small class="text-muted">{{ auth()->user()->created_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="col-md-4">
                            <div class="text-success mb-2">
                                <i class="bi bi-pc-display fs-3"></i>
                            </div>
                            <div class="fw-bold">Equipos asignados</div>
                            <small class="text-muted">{{ auth()->user()->asignaciones->count() ?? 0 }}</small>
                        </div>
                        <div class="col-md-4">
                            <div class="text-warning mb-2">
                                <i class="bi bi-phone fs-3"></i>
                            </div>
                            <div class="fw-bold">Móviles asignados</div>
                            <small class="text-muted">{{ auth()->user()->asignacionesMoviles->count() ?? 0 }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Formulario oculto para eliminar foto --}}
<form id="eliminarFotoForm" action="{{ route('perfil.eliminar.foto') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .rounded-circle {
        border-radius: 50% !important;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
    #previewImage {
        transition: all 0.3s ease;
    }
    .card {
        transition: transform 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    }

    function confirmarEliminarFoto() {
        Swal.fire({
            title: '¿Eliminar foto de perfil?',
            text: 'Esta acción eliminará tu foto actual.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('eliminarFotoForm').submit();
            }
        });
    }

    // Spinner al enviar
    document.getElementById('perfilForm')?.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush