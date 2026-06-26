@extends('layouts.sicet')

@section('page-title', 'Catálogo de Equipos')
@section('page-subtitle', 'Computadoras y dispositivos móviles unificados')

@section('content')
<div class="container-fluid">

    {{-- Resumen --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-pc-display fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Computadoras</div>
                        <div class="fs-4 fw-bold">{{ $totalComputadoras }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-phone fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Dispositivos móviles</div>
                        <div class="fs-4 fw-bold">{{ $totalMoviles }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-dark bg-opacity-10 p-3 me-3">
                        <i class="bi bi-collection fs-4 text-dark"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total de equipos</div>
                        <div class="fs-4 fw-bold">{{ $totalComputadoras + $totalMoviles }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light py-3">
            <form method="GET" action="{{ route('equipos.catalogo') }}" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="q"
                               class="form-control border-start-0"
                               placeholder="Buscar por código, nombre, marca, modelo, serie o IMEI..."
                               value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="tipo" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="computadora" {{ request('tipo') == 'computadora' ? 'selected' : '' }}>Computadoras</option>
                        <option value="movil"       {{ request('tipo') == 'movil'       ? 'selected' : '' }}>Móviles</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        @foreach(['Disponible','Asignado','Pendiente','Mantenimiento','Baja'] as $est)
                            <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ $est }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                        @if(request()->anyFilled(['q','tipo','estado']))
                            <a href="{{ route('equipos.catalogo') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-2 text-primary"></i>
                <span class="fw-bold">Listado de equipos</span>
            </div>
            <span class="badge bg-primary px-3 py-2">
                {{ $equipos->firstItem() ?? 0 }} - {{ $equipos->lastItem() ?? 0 }} de {{ $equipos->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">Tipo</th>
                            <th>Código</th>
                            <th>Equipo</th>
                            <th>Identificador</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipos as $eq)
                            <tr>
                                {{-- Tipo --}}
                                <td class="px-3">
                                    @if($eq->tipo === 'computadora')
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            <i class="bi bi-pc-display me-1"></i> Computadora
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                            <i class="bi bi-phone me-1"></i> Móvil
                                        </span>
                                    @endif
                                </td>

                                {{-- Código --}}
                                <td class="fw-bold">{{ $eq->codigo_interno }}</td>

                                {{-- Equipo (nombre + marca/modelo) --}}
                                <td>
                                    <div class="fw-bold">{{ $eq->nombre ?: ($eq->marca.' '.$eq->modelo) }}</div>
                                    <small class="text-muted d-block">{{ $eq->marca }} {{ $eq->modelo }}</small>
                                </td>

                                {{-- Identificador (serie / IMEI) --}}
                                <td>
                                    <small class="text-muted d-block">{{ $eq->identificador_label }}</small>
                                    <span>{{ $eq->identificador ?: '—' }}</span>
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @php
                                        $badge = match($eq->estado) {
                                            'Disponible'    => 'bg-success',
                                            'Asignado'      => 'bg-primary',
                                            'Pendiente'     => 'bg-warning text-dark',
                                            'Mantenimiento' => 'bg-info text-dark',
                                            'Baja'          => 'bg-secondary',
                                            default         => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }} px-3 py-2">{{ $eq->estado }}</span>
                                </td>

                                {{-- Acciones --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ $eq->show_url }}" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ $eq->edit_url }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ $eq->historial_url }}" class="btn btn-sm btn-outline-dark" title="Historial">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                        @if($eq->estado === 'Disponible')
                                            @if($eq->tipo === 'computadora')
                                                <a href="{{ route('asignaciones.create', $eq->id) }}" class="btn btn-sm btn-success" title="Asignar">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('asignaciones.moviles.create', $eq->id) }}" class="btn btn-sm btn-success" title="Asignar">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-collection display-1 text-muted d-block mb-3"></i>
                                    <h4 class="text-muted">No se encontraron equipos</h4>
                                    <p class="text-muted">Ajusta los filtros o registra un nuevo equipo.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-4 d-flex justify-content-end">
        {{ $equipos->links() }}
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .table th { font-weight: 600; text-transform: uppercase; font-size: 0.78rem; letter-spacing: 0.5px; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 6px; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .card-header { border-bottom: 2px solid #f0f0f0; }
    .pagination { margin-bottom: 0; }
</style>
@endpush
