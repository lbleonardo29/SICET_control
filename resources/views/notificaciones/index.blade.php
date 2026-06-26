@extends('layouts.sicet')

@section('page-title', 'Notificaciones')
@section('page-subtitle', 'Historial de notificaciones del sistema')

@section('content')
<div class="container-fluid">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-bell me-2 text-primary"></i> Todas las notificaciones</span>
            @if(Auth::user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notificaciones.leerTodas') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check2-all me-1"></i> Marcar todas como leídas
                    </button>
                </form>
            @endif
        </div>

        <div class="list-group list-group-flush">
            @forelse($notificaciones as $n)
                <a href="{{ route('notificaciones.leer', $n->id) }}"
                   class="list-group-item list-group-item-action d-flex gap-3 py-3 {{ $n->read_at ? '' : 'bg-light' }}">
                    <span class="s-notif-ico s-notif-{{ $n->data['tipo'] ?? 'info' }}">
                        <i class="bi bi-{{ $n->data['icono'] ?? 'bell' }}"></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong style="font-size:14px;">{{ $n->data['titulo'] ?? 'Notificación' }}</strong>
                            @if(!$n->read_at)
                                <span class="badge bg-primary">Nueva</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:13px;">{{ $n->data['mensaje'] ?? '' }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </a>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bell-slash display-4 d-block mb-3"></i>
                    No tienes notificaciones.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        {{ $notificaciones->links() }}
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
