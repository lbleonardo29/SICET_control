@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-phone me-2 text-primary"></i>
                Dispositivos Móviles Disponibles
            </h2>
            <div class="d-flex gap-4 text-muted">
                <span>
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Disponibles: <strong class="text-success">{{ $moviles->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-phone me-1"></i>
                    Total en sistema: <strong>{{ $totalMoviles ?? $moviles->count() }}</strong>
                </span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <a href="{{ route('moviles.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i>
                    Ver Todos
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros y búsqueda avanzada --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0"
                               placeholder="Buscar por marca, modelo, IMEI o número SIM..."
                               value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="marca" class="form-select">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas ?? [] as $marca)
                            <option value="{{ $marca }}" {{ request('marca') == $marca ? 'selected' : '' }}>
                                {{ $marca }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="orden" class="form-select">
                        <option value="reciente" {{ request('orden') == 'reciente' ? 'selected' : '' }}>Más reciente</option>
                        <option value="antiguo" {{ request('orden') == 'antiguo' ? 'selected' : '' }}>Más antiguo</option>
                        <option value="marca" {{ request('orden') == 'marca' ? 'selected' : '' }}>Por marca</option>
                        <option value="imei" {{ request('orden') == 'imei' ? 'selected' : '' }}>Por IMEI</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i>
                            Filtrar
                        </button>
                        @if(request()->anyFilled(['q', 'marca', 'orden']))
                            <a href="{{ route('moviles.disponibles') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Filtros activos --}}
            @if(request()->anyFilled(['q', 'marca']))
                <div class="mt-3 d-flex align-items-center">
                    <span class="text-muted me-2">Filtros activos:</span>
                    @if(request('q'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-search me-1"></i> {{ request('q') }}
                        </span>
                    @endif
                    @if(request('marca'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-tag me-1"></i> {{ request('marca') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Grid de dispositivos disponibles --}}
    @if($moviles->count() > 0)
        <div class="row g-4">
            @foreach($moviles as $movil)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 shadow-lg border-0 hover-card">
                        {{-- Badge de estado --}}
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle me-1"></i>
                                Disponible
                            </span>
                        </div>

                        <div class="card-body text-center p-4">
                            {{-- Icono --}}
                            <div class="mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                    <i class="bi bi-phone fs-1 text-primary"></i>
                                </div>
                            </div>

                            {{-- Marca y modelo --}}
                            <h5 class="fw-bold mb-1">{{ $movil->marca }} {{ $movil->modelo }}</h5>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-upc-scan me-1"></i>
                                Código: {{ $movil->codigo_interno }}
                            </p>

                            {{-- Detalles --}}
                            <div class="text-start bg-light p-3 rounded-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <small class="text-muted d-block">
                                            <i class="bi bi-upc-scan me-1"></i>
                                            IMEI
                                        </small>
                                        <span class="fw-semibold small font-monospace">{{ $movil->imei ?? 'N/A' }}</span>
                                    </div>
                                    @if($movil->numero_sim)
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block">
                                            <i class="bi bi-sim me-1"></i>
                                            Número SIM
                                        </small>
                                        <span class="fw-semibold small">{{ $movil->numero_sim }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Fecha de registro --}}
                            @if($movil->created_at)
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-calendar me-1"></i>
                                    Registrado: {{ \Carbon\Carbon::parse($movil->created_at)->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción --}}
                        <div class="card-footer bg-white border-0 pb-4">
                            @if(auth()->user()->role === 'admin')
                                <div class="d-flex gap-2">
                                    <a href="{{ route('asignaciones.moviles.create', $movil->id) }}"
                                       class="btn btn-primary flex-grow-1"
                                       data-bs-toggle="tooltip"
                                       title="Asignar dispositivo a empleado">
                                        <i class="bi bi-person-plus me-1"></i>
                                        Asignar
                                    </a>
                                </div>
                            @else
                                <span class="text-muted small d-block text-center">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Contacta al administrador para asignación
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Información de resultados --}}
        <div class="mt-4 text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Mostrando {{ $moviles->count() }} {{ Str::plural('dispositivo', $moviles->count()) }} disponible{{ $moviles->count() != 1 ? 's' : '' }}
        </div>

    @else
        {{-- Estado vacío --}}
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-4">
                <i class="bi bi-phone"></i>
            </div>
            <h3 class="text-muted mb-3">No hay dispositivos disponibles</h3>
            <p class="text-muted mb-4">
                @if(request('q'))
                    No se encontraron resultados para "{{ request('q') }}"
                @else
                    Todos los dispositivos están asignados actualmente
                @endif
            </p>
            @if(auth()->user()->role === 'admin')
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('moviles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Registrar Nuevo Dispositivo
                    </a>
                    @if(request('q') || request('marca'))
                        <a href="{{ route('moviles.disponibles') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Limpiar filtros
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .hover-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .font-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    .badge.bg-info {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }
    .rounded-circle {
        border-radius: 50% !important;
    }
    .card {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));

        // Auto-submit al cambiar filtros
        const marcaSelect = document.querySelector('select[name="marca"]');
        const ordenSelect = document.querySelector('select[name="orden"]');
        
        if (marcaSelect) {
            marcaSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
        
        if (ordenSelect) {
            ordenSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
</script>
@endpush