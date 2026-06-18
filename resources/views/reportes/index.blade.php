@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header con estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-shield-lock me-2 text-primary"></i>
                Reportes de Seguridad
            </h2>
            <div class="d-flex gap-4 text-muted">
                <span>
                    <i class="bi bi-calendar-check me-1"></i>
                    Total: <strong>{{ $reportes->total() }}</strong> registros
                </span>
                <span>
                    <i class="bi bi-box-arrow-in-down text-success me-1"></i>
                    Entradas: <strong class="text-success">{{ $reportes->where('tipo', 'entrada')->count() }}</strong>
                </span>
                <span>
                    <i class="bi bi-box-arrow-up text-warning me-1"></i>
                    Salidas: <strong class="text-warning">{{ $reportes->where('tipo', 'salida')->count() }}</strong>
                </span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            @if(auth()->user()->role === 'seguridad')
                <a href="{{ route('reportes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nuevo Reporte
                </a>
            @endif
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

    {{-- Filtros --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="buscar"
                               class="form-control border-start-0"
                               placeholder="Buscar por matrícula de computadora o usuario..."
                               value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="area" class="form-select">
                        <option value="">Todas las áreas</option>
                        <option value="partidas" {{ request('area') == 'partidas' ? 'selected' : '' }}>Partidas</option>
                        <option value="sauces" {{ request('area') == 'sauces' ? 'selected' : '' }}>Sauces</option>
                        <option value="jardin" {{ request('area') == 'jardin' ? 'selected' : '' }}>Jardín</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}" placeholder="Filtrar por fecha">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i>
                            Filtrar
                        </button>
                        @if(request()->anyFilled(['buscar', 'area', 'tipo', 'fecha']))
                            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Filtros activos --}}
            @if(request()->anyFilled(['buscar', 'area', 'tipo', 'fecha']))
                <div class="mt-3 d-flex align-items-center">
                    <span class="text-muted me-2">Filtros activos:</span>
                    @if(request('buscar'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-search me-1"></i> {{ request('buscar') }}
                        </span>
                    @endif
                    @if(request('area'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-building me-1"></i> {{ ucfirst(request('area')) }}
                        </span>
                    @endif
                    @if(request('tipo'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-arrow-left-right me-1"></i> {{ ucfirst(request('tipo')) }}
                        </span>
                    @endif
                    @if(request('fecha'))
                        <span class="badge bg-info text-dark me-2">
                            <i class="bi bi-calendar me-1"></i> {{ \Carbon\Carbon::parse(request('fecha'))->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de Reportes</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ $reportes->firstItem() ?? 0 }} - {{ $reportes->lastItem() ?? 0 }} de {{ $reportes->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3" style="width: 5%">#</th>
                            <th style="width: 12%"><i class="bi bi-calendar me-1"></i> Fecha</th>
                            <th style="width: 12%"><i class="bi bi-person me-1"></i> Usuario</th>
                            <th style="width: 20%"><i class="bi bi-person-badge me-1"></i> Empleado</th>
                            <th style="width: 10%"><i class="bi bi-upc-scan me-1"></i> Matrícula</th>
                            <th style="width: 10%"><i class="bi bi-building me-1"></i> Área</th>
                            <th style="width: 10%"><i class="bi bi-arrow-left-right me-1"></i> Tipo</th>
                            <th style="width: 21%"><i class="bi bi-exclamation-triangle me-1"></i> Inconsistencias</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportes as $index => $r)
                            <tr>
                                <td class="px-3 fw-bold text-center">{{ $reportes->firstItem() + $index }}</td>
                                <td class="text-nowrap">
                                    <div class="fw-bold">{{ $r->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $r->created_at->format('H:i') }} hrs</small>
                                </td>
                                <td class="text-nowrap">
                                    <div class="fw-bold">{{ $r->user->name }}</div>
                                    <small class="text-muted text-capitalize">{{ $r->user->role }}</small>
                                </td>
                                <td>
                                    @if($r->empleado)
                                        <div class="fw-bold">{{ $r->empleado->nombre }} {{ $r->empleado->apellidos }}</div>
                                        <small class="text-muted">ID: {{ $r->numero_empleado }}</small>
                                    @else
                                        <span class="text-muted">{{ $r->numero_empleado ?? '—' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $r->matricula }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2">
                                        {{ ucfirst($r->area) }}
                                    </span>
                                </td>
                                <td>
                                    @if($r->tipo == 'entrada')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-box-arrow-in-down me-1"></i> Entrada
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-box-arrow-up me-1"></i> Salida
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->inconsistencias)
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            {{ Str::limit($r->inconsistencias, 50) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-shield-slash display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No hay reportes registrados</h4>
                                    <p class="text-muted mb-4">Los reportes de seguridad aparecerán aquí</p>
                                    @if(auth()->user()->role === 'seguridad')
                                        <a href="{{ route('reportes.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Registrar primer reporte
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Página {{ $reportes->currentPage() }} de {{ $reportes->lastPage() }}
                </div>
                <div>
                    {{ $reportes->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    .text-nowrap {
        white-space: nowrap;
    }
    .btn-group .btn {
        border-radius: 8px;
        margin: 0 2px;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .badge.bg-info {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }
    .card-header {
        border-bottom: 2px solid #f0f0f0;
    }
    .pagination {
        margin-bottom: 0;
    }
</style>
@endpush