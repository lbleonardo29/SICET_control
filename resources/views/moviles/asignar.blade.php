@extends('layouts.app')

@section('content')

<h2 class="mb-4">Asignar Dispositivo Móvil</h2>

<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="mb-3">
            {{ $movil->marca }} {{ $movil->modelo }}
        </h5>

        <p><strong>IMEI:</strong> {{ $movil->imei }}</p>

        <form action="{{ route('moviles.asignar', $movil->id) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Seleccionar empleado</label>

                <select name="empleado_id" class="form-control" required>
                    <option value="">-- Selecciona un empleado --</option>

                    @foreach($empleados as $empleado)
                        <option value="{{ $empleado->id }}">
                            {{ $empleado->nombre_completo }}
                        </option>
                    @endforeach

                </select>
            </div>

            <button type="submit" class="btn btn-success">
                Asignar
            </button>

            <a href="{{ route('moviles.index') }}" class="btn btn-secondary">
                Cancelar
            </a>

        </form>

    </div>
</div>

@endsection