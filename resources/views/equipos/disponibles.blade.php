@extends('layouts.sicet')

@section('page-title', 'Equipos Disponibles')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-pc-display me-2 text-success"></i>
                Computadoras Disponibles
            </h2>
            <p class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Total: <strong>{{ $equipos->count() }}</strong> computadoras disponibles
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <a href="{{ route('equipos.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i>
                    Ver Todos
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros y búsqueda --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0"
                               placeholder="Buscar por código, marca, modelo o serie..."
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
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            {{-- Filtros activos --}}
            @if(request('q') || request('marca'))
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
                    <a href="{{ route('equipos.disponibles') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Grid de computadoras disponibles --}}
    @if($equipos->count() > 0)
        <div class="row g-4">
            @foreach($equipos as $computadora)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0 hover-card">
                        <div class="card-header bg-white border-0 pt-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Disponible
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-upc-scan me-1"></i>
                                    {{ $computadora->codigo_interno }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="card-body pt-0">
                            {{-- Icono e información principal --}}
                            <div class="text-center mb-3">
                                <div class="display-4 text-success mb-2">
                                    <i class="bi bi-pc-display"></i>
                                </div>
                                <h5 class="fw-bold mb-1">{{ $computadora->marca }} {{ $computadora->modelo }}</h5>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-upc-scan me-1"></i>
                                    Serie: {{ $computadora->numero_serie ?? 'N/A' }}
                                </p>
                            </div>

                            {{-- Especificaciones --}}
                            <div class="bg-light p-3 rounded-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Procesador</small>
                                        <span class="fw-semibold small">{{ $computadora->procesador ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted d-block">RAM</small>
                                        <span class="fw-semibold small">{{ $computadora->ram ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted d-block">SSD</small>
                                        <span class="fw-semibold small">{{ $computadora->ssd ?? 'N/A' }}</span>
                                    </div>
                                    @if($computadora->color)
                                    <div class="col-6">
                                        <small class="text-muted d-block">Color</small>
                                        <span class="fw-semibold small">{{ $computadora->color }}</span>
                                    </div>
                                    @endif
                                    <div class="col-6">
                                        <small class="text-muted d-block">Cargador</small>
                                        <span class="fw-semibold small">
                                            @if($computadora->cargador)
                                                <i class="bi bi-check-circle text-success"></i> Sí
                                            @else
                                                <i class="bi bi-x-circle text-danger"></i> No
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Fecha de adquisición --}}
                            @if($computadora->fecha_adquisicion)
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-calendar me-1"></i>
                                    Adq: {{ \Carbon\Carbon::parse($computadora->fecha_adquisicion)->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción --}}
                        <div class="card-footer bg-white border-0 pb-3">
                            @if(auth()->user()->role === 'admin')
                                <div class="d-flex gap-2">
                                    <a href="{{ route('asignaciones.create', $computadora->id) }}"
                                       class="btn btn-primary flex-grow-1"
                                       data-bs-toggle="tooltip"
                                       title="Asignar computadora a empleado">
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
            Mostrando {{ $equipos->count() }} {{ Str::plural('computadora', $equipos->count()) }} disponible{{ $equipos->count() != 1 ? 's' : '' }}
        </div>

    @else
        {{-- Estado vacío --}}
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-4">
                <i class="bi bi-pc-display"></i>
            </div>
            <h3 class="text-muted mb-3">No hay computadoras disponibles</h3>
            <p class="text-muted mb-4">
                @if(request('q'))
                    No se encontraron resultados para "{{ request('q') }}"
                @else
                    Todas las computadoras están asignadas actualmente
                @endif
            </p>
            @if(auth()->user()->role === 'admin')
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Registrar Nueva Computadora
                    </a>
                    @if(request('q') || request('marca'))
                        <a href="{{ route('equipos.disponibles') }}" class="btn btn-outline-secondary">
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
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #28a745;
    }
    .card-header {
        background: transparent;
    }
    .bg-light {
        background-color: #f8f9fc !important;
    }
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    .badge.bg-info {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
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