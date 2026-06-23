@extends('layouts.sicet')

@section('page-title', 'Confirmacion de Asignacion')

@section('content')
<div class="container text-center mt-5">
    <div class="card shadow-lg border-0" style="max-width: 500px; margin: 0 auto;">
        <div class="card-body p-5">
            @if($tipo == 'aceptada')
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="text-success mb-3"> ¡Asignación Confirmada!</h2>
                <p class="lead">{{ $mensaje }}</p>
                <p class="text-muted mt-3">La computadora ha sido registrada a tu nombre correctamente.</p>
            @else
                <div class="mb-4">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                </div>
                <h2 class="text-danger mb-3"> Asignación Rechazada</h2>
                <p class="lead">{{ $mensaje }}</p>
                <p class="text-muted mt-3">La computadora no ha sido asignada.</p>
            @endif

            <hr class="my-4">

            <div class="mt-4">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ir al inicio de sesión
                </a>
            </div>

            <p class="text-muted small mt-4">
                <i class="bi bi-info-circle me-1"></i>
                Esta es una página de confirmación automática. Si tienes dudas, contacta al departamento de TI.
            </p>
        </div>
    </div>
</div>
@endsection